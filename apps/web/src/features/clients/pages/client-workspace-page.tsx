import { type ReactNode, useEffect, useMemo, useState } from 'react'
import { useForm } from 'react-hook-form'
import { z } from 'zod'
import { zodResolver } from '@hookform/resolvers/zod'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { Link, useNavigate, useParams, useSearchParams } from 'react-router-dom'
import { PageHeader, AppBadge, AppButton, AppCard, AppCardBody, AppCardHeader, AppInput, AppSelect, AppTextarea, EmptyState, LoadingSkeleton } from '@/components/ui'
import { ClientStatusBadge } from '@/features/clients/components/client-status-badge'
import { ClientWorkspaceTabs } from '@/features/clients/components/client-workspace-tabs'
import { ClientDispositionPanel } from '@/features/disposition/components/client-disposition-panel'
import { ClientCommunicationsPanel } from '@/features/communications/components/client-communications-panel'
import { ClientEventsPanel } from '@/features/calendar-tasks/components/client-events-panel'
import { ClientApplicationsPanel } from '@/features/applications/components/client-applications-panel'
import { ClientQuickNoteDialog } from '@/features/clients/components/client-quick-note-dialog'
import { ApplicationCreateDialog } from '@/features/applications/components/application-create-dialog'
import { EventCreateDialog } from '@/features/calendar-tasks/components/event-create-dialog'
import { applicationsApi, clientsApi, communicationsApi, calendarApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import { useToast } from '@/components/shell/toast-host'
import { cn } from '@/lib/utils/cn'

const workspaceSchema = z.object({
  displayName: z.string().min(2),
  firstName: z.string().optional().or(z.literal('')),
  lastName: z.string().optional().or(z.literal('')),
  companyName: z.string().optional().or(z.literal('')),
  primaryEmail: z.string().email().optional().or(z.literal('')),
  primaryPhone: z.string().optional().or(z.literal('')),
  preferredContactChannel: z.enum(['email', 'sms', 'phone']).optional(),
  dateOfBirth: z.string().optional().or(z.literal('')),
  status: z.enum(['lead', 'active', 'inactive']),
  addressLine1: z.string().optional().or(z.literal('')),
  addressLine2: z.string().optional().or(z.literal('')),
  city: z.string().optional().or(z.literal('')),
  stateCode: z.string().max(2).optional().or(z.literal('')),
  postalCode: z.string().optional().or(z.literal(''))
})

type WorkspaceValues = z.infer<typeof workspaceSchema>

const validTabs = ['overview', 'communications', 'events', 'applications', 'notes', 'documents', 'audit'] as const

type WorkspaceTab = typeof validTabs[number]

function normalizeWorkspaceFormStatus(
  status: 'active' | 'lead' | 'qualified' | 'applied' | 'inactive'
): 'active' | 'lead' | 'inactive' {
  if (status === 'inactive') {
    return 'inactive'
  }

  if (status === 'lead') {
    return 'lead'
  }

  return 'active'
}

type ClientAddress = {
  addressLine1?: string | null
  addressLine2?: string | null
  city?: string | null
  stateCode?: string | null
  postalCode?: string | null
}

type ApplicationListItem = {
  id: string
  applicationNumber: string
  productType: string
  createdAt?: string | null
  currentStatus: {
    label: string
    tone: string
  }
  ruleSummary: {
    infoCount: number
    warningCount: number
    blockingCount: number
  }
}

function toneToBadgeVariant(tone?: string | null): 'neutral' | 'info' | 'success' | 'warning' | 'danger' {
  if (tone === 'success') return 'success'
  if (tone === 'warning') return 'warning'
  if (tone === 'danger') return 'danger'
  if (tone === 'info') return 'info'
  return 'neutral'
}

function isOlderThanDays(isoValue?: string | null, days = 7) {
  if (!isoValue) return false
  const cutoff = new Date()
  cutoff.setDate(cutoff.getDate() - days)
  return new Date(isoValue).getTime() < cutoff.getTime()
}

export function ClientWorkspacePage() {
  const navigate = useNavigate()
  const queryClient = useQueryClient()
  const [searchParams] = useSearchParams()
  const { clientId, tab } = useParams<{ clientId: string, tab?: WorkspaceTab }>()
  const { notify } = useToast()
  const [noteBody, setNoteBody] = useState('')
  const [documentFile, setDocumentFile] = useState<File | null>(null)
  const [attachmentCategory, setAttachmentCategory] = useState('')
  const [quickNoteOpen, setQuickNoteOpen] = useState(false)
  const [applicationCreateOpen, setApplicationCreateOpen] = useState(false)
  const [eventCreateOpen, setEventCreateOpen] = useState(false)
  const [dispositionOpenRequestKey, setDispositionOpenRequestKey] = useState(0)

  const activeTab = validTabs.includes((tab ?? 'overview') as WorkspaceTab) ? (tab ?? 'overview') as WorkspaceTab : 'overview'
  const initialComposer = searchParams.get('compose')
  const composerMode = initialComposer === 'email' || initialComposer === 'call' ? initialComposer : 'sms'

  const detailQuery = useQuery({
    enabled: Boolean(clientId),
    queryKey: queryKeys.clients.detail(clientId ?? ''),
    queryFn: () => clientsApi.get(clientId ?? '')
  })

  const communicationsPreviewQuery = useQuery({
    enabled: Boolean(clientId) && activeTab === 'overview',
    queryKey: queryKeys.communications.clientTimeline(clientId ?? '', { limit: 1 }),
    queryFn: () => communicationsApi.list(clientId ?? '', { limit: 1 })
  })

  const eventsPreviewQuery = useQuery({
    enabled: Boolean(clientId) && activeTab === 'overview',
    queryKey: queryKeys.calendar.clientEvents(clientId ?? '', { preview: 'overview' }),
    queryFn: () => calendarApi.clientEvents(clientId ?? '')
  })

  const applicationsPreviewQuery = useQuery({
    enabled: Boolean(clientId) && activeTab === 'overview',
    queryKey: queryKeys.applications.list(clientId ?? ''),
    queryFn: () => applicationsApi.list(clientId ?? '')
  })

  const payload = detailQuery.data?.data
  const form = useForm<WorkspaceValues>({
    resolver: zodResolver(workspaceSchema)
  })

  useEffect(() => {
    if (!payload?.client) return

    const address = (payload.client.address ?? {}) as ClientAddress

    form.reset({
      displayName: payload.client.displayName,
      firstName: payload.client.firstName ?? '',
      lastName: payload.client.lastName ?? '',
      companyName: payload.client.companyName ?? '',
      primaryEmail: payload.client.primaryEmail ?? '',
      primaryPhone: payload.client.primaryPhone ?? '',
      preferredContactChannel: (payload.client.preferredContactChannel as 'email' | 'sms' | 'phone' | undefined) ?? 'email',
      dateOfBirth: payload.client.dateOfBirth ?? '',
      status: normalizeWorkspaceFormStatus(payload.client.status),
      addressLine1: address.addressLine1 ?? '',
      addressLine2: address.addressLine2 ?? '',
      city: address.city ?? '',
      stateCode: address.stateCode ?? '',
      postalCode: address.postalCode ?? ''
    })
  }, [form, payload])

  const updateClientMutation = useMutation({
    mutationFn: (values: WorkspaceValues) => clientsApi.update(clientId ?? '', values),
    onSuccess: async () => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.clients.detail(clientId ?? '') }),
        queryClient.invalidateQueries({ queryKey: queryKeys.clients.all }),
        queryClient.invalidateQueries({ queryKey: queryKeys.dashboard.all })
      ])
      notify({ title: 'Client updated', description: 'Profile changes remained auditable and tenant-scoped.', tone: 'success' })
    }
  })

  const createNoteMutation = useMutation({
    mutationFn: (body: string) => clientsApi.createNote(clientId ?? '', { body }),
    onSuccess: async () => {
      setNoteBody('')
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.clients.detail(clientId ?? '') }),
        queryClient.invalidateQueries({ queryKey: queryKeys.dashboard.all })
      ])
      notify({ title: 'Note created', description: 'The client workspace note history updated from the server contract.', tone: 'success' })
    }
  })

  const uploadDocumentMutation = useMutation({
    mutationFn: (formData: FormData) => clientsApi.uploadDocument(clientId ?? '', formData),
    onSuccess: async () => {
      setDocumentFile(null)
      setAttachmentCategory('')
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.clients.detail(clientId ?? '') }),
        queryClient.invalidateQueries({ queryKey: queryKeys.dashboard.all })
      ])
      notify({ title: 'Document uploaded', description: 'Document metadata now appears in the governed client workspace.', tone: 'success' })
    }
  })

  const applicationCreateMutation = useMutation({
    mutationFn: (body: { productType: string, externalReference?: string | null, amountRequested?: number | null, submittedAt?: string | null }) =>
      applicationsApi.create(clientId ?? '', body),
    onSuccess: async () => {
      setApplicationCreateOpen(false)
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.applications.list(clientId ?? '') }),
        queryClient.invalidateQueries({ queryKey: queryKeys.clients.detail(clientId ?? '') })
      ])
      notify({
        title: 'Application created',
        description: 'The new application is now available from the governed client workspace.',
        tone: 'success'
      })
    }
  })

  const summaryCards = useMemo(() => payload ? [
    ['Notes', payload.summary.notesCount],
    ['Documents', payload.summary.documentsCount],
    ['Events', payload.summary.eventsCount],
    ['Applications', payload.summary.applicationsCount]
  ] : [], [payload])

  const latestCommunication = communicationsPreviewQuery.data?.data.items?.[0]
  const nextEvent = useMemo(() => {
    const items = eventsPreviewQuery.data?.data.items ?? []
    return [...items]
      .sort((left, right) => new Date(left.startsAt ?? 0).getTime() - new Date(right.startsAt ?? 0).getTime())[0]
  }, [eventsPreviewQuery.data])

  const leadApplication = useMemo(() => {
    const items = (((applicationsPreviewQuery.data as { data?: { items?: ApplicationListItem[] } })?.data?.items) ?? []) as ApplicationListItem[]
    return [...items].sort((left, right) => {
      if (left.ruleSummary.blockingCount !== right.ruleSummary.blockingCount) {
        return right.ruleSummary.blockingCount - left.ruleSummary.blockingCount
      }
      if (left.ruleSummary.warningCount !== right.ruleSummary.warningCount) {
        return right.ruleSummary.warningCount - left.ruleSummary.warningCount
      }
      return new Date(right.createdAt ?? 0).getTime() - new Date(left.createdAt ?? 0).getTime()
    })[0]
  }, [applicationsPreviewQuery.data])

  const recentActivity = useMemo(() => {
    const noteItems = (payload?.recentNotes ?? []).slice(0, 2).map((note) => ({
      id: `note-${note.id}`,
      kind: 'Note',
      title: note.authorDisplayName,
      body: note.body,
      at: note.createdAt,
      href: `/app/clients/${clientId}/notes`
    }))

    const documentItems = (payload?.recentDocuments ?? []).slice(0, 2).map((document) => ({
      id: `document-${document.id}`,
      kind: 'Document',
      title: document.originalFilename,
      body: `Uploaded by ${document.uploadedByDisplayName}`,
      at: document.uploadedAt,
      href: `/app/clients/${clientId}/documents`
    }))

    const auditItems = (payload?.recentAudit ?? []).slice(0, 2).map((entry) => ({
      id: `audit-${entry.id}`,
      kind: 'Audit',
      title: entry.action,
      body: `${entry.actorDisplayName} • ${entry.subjectType}`,
      at: entry.createdAt,
      href: `/app/clients/${clientId}/audit`
    }))

    return [...noteItems, ...documentItems, ...auditItems]
      .sort((left, right) => new Date(right.at ?? 0).getTime() - new Date(left.at ?? 0).getTime())
      .slice(0, 5)
  }, [clientId, payload])

  const recommendedAction = useMemo(() => {
    if (leadApplication?.ruleSummary.blockingCount) {
      return {
        tone: 'danger' as const,
        title: 'Review blocked application',
        description: 'This client has an application with blocking rule evidence.',
        ctaLabel: 'Open application',
        onClick: () => navigate(`/app/clients/${clientId}/applications`)
      }
    }

    if (latestCommunication?.status?.tone === 'danger') {
      return {
        tone: 'warning' as const,
        title: 'Retry failed outreach',
        description: 'The most recent client communication failed and should be reviewed.',
        ctaLabel: 'Open communications',
        onClick: () => navigate(`/app/clients/${clientId}/communications`)
      }
    }

    if (nextEvent && ((nextEvent.taskSummary?.blocked ?? 0) > 0 || (nextEvent.taskSummary?.open ?? 0) > 0)) {
      return {
        tone: 'info' as const,
        title: 'Prepare for the next scheduled event',
        description: 'There is upcoming client work with open or blocked tasks.',
        ctaLabel: 'Open events',
        onClick: () => navigate(`/app/clients/${clientId}/events`)
      }
    }

    if (isOlderThanDays(payload?.summary.lastActivityAt, 7)) {
      return {
        tone: 'neutral' as const,
        title: 'Follow up with this client',
        description: 'This record has not had recent activity and may need outreach.',
        ctaLabel: 'Start communication',
        onClick: () => navigate(`/app/clients/${clientId}/communications?compose=sms`)
      }
    }

    return {
      tone: 'success' as const,
      title: 'No urgent action',
      description: 'This client does not have a blocked workflow, failed outreach, or upcoming event needing attention.',
      ctaLabel: 'Open full workspace',
      onClick: () => navigate(`/app/clients/${clientId}/overview`)
    }
  }, [clientId, latestCommunication, leadApplication, navigate, nextEvent, payload?.summary.lastActivityAt])

  if (detailQuery.isLoading && !payload) {
    return <LoadingSkeleton lines={8} />
  }

  if (!payload?.client) {
    return <EmptyState title="Client not found" description="The requested workspace could not be resolved for the current tenant and role scope." />
  }

  const renderProfileCard = () => (
    <AppCard>
      <AppCardHeader>
        <div className="heading-md">Client profile</div>
        <div className="body-sm text-text-muted">Update core client details while keeping history, communications, and lifecycle changes governed separately.</div>
      </AppCardHeader>
      <AppCardBody>
        <form className="grid gap-4 lg:grid-cols-2" onSubmit={form.handleSubmit(async (values) => updateClientMutation.mutateAsync(values))}>
          <div className="space-y-2 lg:col-span-2"><label className="label-sm text-text">Display name</label><AppInput {...form.register('displayName')} /></div>
          <div className="space-y-2"><label className="label-sm text-text">First name</label><AppInput {...form.register('firstName')} /></div>
          <div className="space-y-2"><label className="label-sm text-text">Last name</label><AppInput {...form.register('lastName')} /></div>
          <div className="space-y-2 lg:col-span-2"><label className="label-sm text-text">Company name</label><AppInput {...form.register('companyName')} /></div>
          <div className="space-y-2"><label className="label-sm text-text">Email</label><AppInput type="email" {...form.register('primaryEmail')} /></div>
          <div className="space-y-2"><label className="label-sm text-text">Phone</label><AppInput {...form.register('primaryPhone')} /></div>
          <div className="space-y-2"><label className="label-sm text-text">Preferred contact channel</label><AppSelect {...form.register('preferredContactChannel')}><option value="email">Email</option><option value="sms">SMS</option><option value="phone">Phone</option></AppSelect></div>
          <div className="space-y-2"><label className="label-sm text-text">Status</label><AppSelect {...form.register('status')}><option value="lead">Lead</option><option value="active">Active</option><option value="inactive">Inactive</option></AppSelect></div>
          <div className="space-y-2"><label className="label-sm text-text">Date of birth</label><AppInput type="date" {...form.register('dateOfBirth')} /></div>
          <div className="space-y-2 lg:col-span-2"><label className="label-sm text-text">Address line 1</label><AppInput {...form.register('addressLine1')} /></div>
          <div className="space-y-2 lg:col-span-2"><label className="label-sm text-text">Address line 2</label><AppInput {...form.register('addressLine2')} /></div>
          <div className="space-y-2"><label className="label-sm text-text">City</label><AppInput {...form.register('city')} /></div>
          <div className="space-y-2"><label className="label-sm text-text">State</label><AppInput {...form.register('stateCode')} /></div>
          <div className="space-y-2"><label className="label-sm text-text">Postal code</label><AppInput {...form.register('postalCode')} /></div>
          <div className="lg:col-span-2"><AppButton type="submit" disabled={updateClientMutation.isPending}>{updateClientMutation.isPending ? 'Saving…' : 'Save profile changes'}</AppButton></div>
        </form>
      </AppCardBody>
    </AppCard>
  )

  const renderOverviewPanel = () => (
    <div className="space-y-6">
      <AppCard>
        <AppCardBody className="flex flex-wrap gap-3">
          <ActionButton disabled={!payload.client.primaryPhone} helper="No primary phone saved" onClick={() => navigate(`/app/clients/${clientId}/communications?compose=call`)}>
            Call
          </ActionButton>
          <ActionButton disabled={!payload.client.primaryPhone} helper="No primary phone saved" onClick={() => navigate(`/app/clients/${clientId}/communications?compose=sms`)}>
            Text
          </ActionButton>
          <ActionButton disabled={!payload.client.primaryEmail} helper="No primary email saved" onClick={() => navigate(`/app/clients/${clientId}/communications?compose=email`)}>
            Email
          </ActionButton>
          <ActionButton onClick={() => setEventCreateOpen(true)}>Schedule event</ActionButton>
          <ActionButton onClick={() => setApplicationCreateOpen(true)}>Create application</ActionButton>
          <ActionButton onClick={() => setQuickNoteOpen(true)}>Add note</ActionButton>
          <ActionButton onClick={() => setDispositionOpenRequestKey((current) => current + 1)}>Change disposition</ActionButton>
        </AppCardBody>
      </AppCard>

      <AppCard>
        <AppCardHeader>
          <div className="heading-md">Recommended next step</div>
          <div className="body-sm text-text-muted">What should happen next for this client based on current governed activity.</div>
        </AppCardHeader>
        <AppCardBody className="flex flex-wrap items-start justify-between gap-4">
          <div className="space-y-2">
            <AppBadge variant={toneToBadgeVariant(recommendedAction.tone)}>{recommendedAction.title}</AppBadge>
            <div className="body-md text-text">{recommendedAction.description}</div>
          </div>
          <AppButton type="button" onClick={recommendedAction.onClick}>{recommendedAction.ctaLabel}</AppButton>
        </AppCardBody>
      </AppCard>

      <div className="grid gap-4 xl:grid-cols-2">
        <SnapshotCard
          title="Latest communication"
          emptyTitle="No communications yet"
          emptyDescription="Start with a text, email, or call from the action bar."
          ctaHref={`/app/clients/${clientId}/communications`}
          ctaLabel="Open communications"
        >
          {latestCommunication ? (
            <div className="space-y-2">
              <div className="flex flex-wrap items-center gap-2">
                <AppBadge variant="neutral">{latestCommunication.channel.toUpperCase()}</AppBadge>
                <AppBadge variant="neutral">{latestCommunication.direction}</AppBadge>
                <AppBadge variant={toneToBadgeVariant(latestCommunication.status.tone)}>{latestCommunication.status.label}</AppBadge>
              </div>
              <div className="font-medium text-text">{latestCommunication.content.subject ?? latestCommunication.content.preview ?? 'Communication activity'}</div>
              <div className="body-sm text-text-muted">{latestCommunication.occurredAt ? new Date(latestCommunication.occurredAt).toLocaleString() : '—'}</div>
            </div>
          ) : null}
        </SnapshotCard>

        <SnapshotCard
          title="Next event"
          emptyTitle="No event scheduled"
          emptyDescription="Schedule the next task or appointment for this client."
          ctaHref={`/app/clients/${clientId}/events`}
          ctaLabel="Open events"
        >
          {nextEvent ? (
            <div className="space-y-2">
              <div className="font-medium text-text">{nextEvent.title}</div>
              <div className="body-sm text-text-muted">{nextEvent.startsAt ? new Date(nextEvent.startsAt).toLocaleString() : '—'}</div>
              <div className="flex flex-wrap gap-2">
                <AppBadge variant="neutral">{nextEvent.eventType.replaceAll('_', ' ')}</AppBadge>
                <AppBadge variant="info">{nextEvent.taskSummary.open} open</AppBadge>
                {nextEvent.taskSummary.blocked ? <AppBadge variant="warning">{nextEvent.taskSummary.blocked} blocked</AppBadge> : null}
              </div>
            </div>
          ) : null}
        </SnapshotCard>

        <SnapshotCard
          title="Lead application"
          emptyTitle="No applications yet"
          emptyDescription="Create the first application to begin workflow and rule tracking."
          ctaHref={`/app/clients/${clientId}/applications`}
          ctaLabel="Open applications"
        >
          {leadApplication ? (
            <div className="space-y-2">
              <div className="font-medium text-text">{leadApplication.applicationNumber}</div>
              <div className="body-sm text-text-muted">{leadApplication.productType}</div>
              <div className="flex flex-wrap gap-2">
                <AppBadge variant={toneToBadgeVariant(leadApplication.currentStatus.tone)}>{leadApplication.currentStatus.label}</AppBadge>
                <AppBadge variant="warning">{leadApplication.ruleSummary.warningCount} warnings</AppBadge>
                {leadApplication.ruleSummary.blockingCount ? <AppBadge variant="danger">{leadApplication.ruleSummary.blockingCount} blocking</AppBadge> : null}
              </div>
            </div>
          ) : null}
        </SnapshotCard>

        <SnapshotCard
          title="Recent note"
          emptyTitle="No recent notes"
          emptyDescription="Add context for the next person working this record."
          ctaHref={`/app/clients/${clientId}/notes`}
          ctaLabel="Open notes"
        >
          {payload.recentNotes[0] ? (
            <div className="space-y-2">
              <div className="font-medium text-text">{payload.recentNotes[0].authorDisplayName}</div>
              <div className="body-sm text-text-muted">{payload.recentNotes[0].body}</div>
              <div className="text-xs text-text-muted">{payload.recentNotes[0].createdAt ? new Date(payload.recentNotes[0].createdAt).toLocaleString() : '—'}</div>
            </div>
          ) : null}
        </SnapshotCard>
      </div>

      <ClientDispositionPanel clientId={clientId ?? ''} payload={payload} openRequestKey={dispositionOpenRequestKey} />

      <AppCard>
        <AppCardHeader>
          <div className="heading-md">Recent activity</div>
          <div className="body-sm text-text-muted">The latest record changes, notes, and evidence for this client.</div>
        </AppCardHeader>
        <AppCardBody>
          {recentActivity.length ? (
            <div className="space-y-3">
              {recentActivity.map((item) => (
                <Link key={item.id} to={item.href} className="block rounded-lg border border-border bg-muted p-4 transition hover:border-primary/40 hover:bg-surface">
                  <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                      <div className="flex items-center gap-2">
                        <AppBadge variant="neutral">{item.kind}</AppBadge>
                        <div className="font-medium text-text">{item.title}</div>
                      </div>
                      <div className="body-sm mt-2 text-text-muted">{item.body}</div>
                    </div>
                    <div className="text-xs text-text-muted">{item.at ? new Date(item.at).toLocaleString() : '—'}</div>
                  </div>
                </Link>
              ))}
            </div>
          ) : (
            <EmptyState title="No recent activity" description="Notes, document uploads, and audit evidence will appear here as the record changes." />
          )}
        </AppCardBody>
      </AppCard>

      {renderProfileCard()}
    </div>
  )

  const renderActivePanel = () => {
    if (activeTab === 'overview') {
      return renderOverviewPanel()
    }

    if (activeTab === 'communications') {
      return (
        <ClientCommunicationsPanel
          clientId={clientId ?? ''}
          fallbackEmail={payload.client.primaryEmail}
          fallbackPhone={payload.client.primaryPhone}
          initialComposer={composerMode}
        />
      )
    }

    if (activeTab === 'events') {
      return <ClientEventsPanel clientId={clientId ?? ''} />
    }

    if (activeTab === 'applications') {
      return <ClientApplicationsPanel clientId={clientId ?? ''} />
    }

    if (activeTab === 'notes') {
      return (
        <AppCard>
          <AppCardHeader>
            <div className="heading-md">Notes</div>
            <div className="body-sm text-text-muted">User-authored notes are distinct from future system-generated note flows.</div>
          </AppCardHeader>
          <AppCardBody>
            <div className="space-y-4">
              <div className="space-y-2">
                <label className="label-sm text-text">New note</label>
                <AppTextarea value={noteBody} onChange={(event) => setNoteBody(event.currentTarget.value)} placeholder="Capture a governed note for this client." />
              </div>
              <AppButton type="button" onClick={() => createNoteMutation.mutateAsync(noteBody)} disabled={createNoteMutation.isPending || noteBody.trim().length === 0}>
                {createNoteMutation.isPending ? 'Saving…' : 'Add note'}
              </AppButton>
              <div className="space-y-3">
                {payload.recentNotes.length ? payload.recentNotes.map((note) => (
                  <div key={note.id} className="rounded-lg border border-border bg-muted p-4">
                    <div className="flex items-center justify-between gap-3">
                      <div className="font-medium text-text">{note.authorDisplayName}</div>
                      <div className="text-xs text-text-muted">{note.createdAt ? new Date(note.createdAt).toLocaleString() : '—'}</div>
                    </div>
                    <div className="body-sm mt-2 text-text-muted">{note.body}</div>
                  </div>
                )) : <EmptyState title="No notes yet" description="Add the first governed note to establish a client history baseline." />}
              </div>
            </div>
          </AppCardBody>
        </AppCard>
      )
    }

    if (activeTab === 'documents') {
      return (
        <AppCard>
          <AppCardHeader>
            <div className="heading-md">Documents</div>
            <div className="body-sm text-text-muted">File bodies stay in tenant-aware storage while metadata stays in the database.</div>
          </AppCardHeader>
          <AppCardBody>
            <div className="space-y-4">
              <div className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_220px_auto] lg:items-end">
                <div className="space-y-2">
                  <label className="label-sm text-text">Attachment</label>
                  <AppInput type="file" onChange={(event) => setDocumentFile(event.currentTarget.files?.[0] ?? null)} />
                </div>
                <div className="space-y-2">
                  <label className="label-sm text-text">Category</label>
                  <AppInput value={attachmentCategory} onChange={(event) => setAttachmentCategory(event.currentTarget.value)} placeholder="Profile, intake, ID…" />
                </div>
                <AppButton
                  type="button"
                  onClick={() => {
                    if (!documentFile) return
                    const formData = new FormData()
                    formData.append('file', documentFile)
                    if (attachmentCategory) formData.append('attachmentCategory', attachmentCategory)
                    uploadDocumentMutation.mutateAsync(formData)
                  }}
                  disabled={uploadDocumentMutation.isPending || !documentFile}
                >
                  {uploadDocumentMutation.isPending ? 'Uploading…' : 'Upload document'}
                </AppButton>
              </div>
              <div className="space-y-3">
                {payload.recentDocuments.length ? payload.recentDocuments.map((document) => (
                  <div key={document.id} className="rounded-lg border border-border bg-muted p-4">
                    <div className="flex flex-wrap items-start justify-between gap-3">
                      <div>
                        <div className="font-medium text-text">{document.originalFilename}</div>
                        <div className="text-xs text-text-muted">{document.mimeType} • {Math.round(document.sizeBytes / 1024)} KB</div>
                      </div>
                      <div className="text-xs text-text-muted">{document.uploadedAt ? new Date(document.uploadedAt).toLocaleString() : '—'}</div>
                    </div>
                    <div className="body-sm mt-2 text-text-muted">Uploaded by {document.uploadedByDisplayName}{document.attachmentCategory ? ` • ${document.attachmentCategory}` : ''}</div>
                  </div>
                )) : <EmptyState title="No documents yet" description="Upload the first document to validate governed storage and metadata handling." />}
              </div>
            </div>
          </AppCardBody>
        </AppCard>
      )
    }

    if (activeTab === 'audit') {
      return payload.recentAudit.length ? (
        <AppCard>
          <AppCardHeader>
            <div className="heading-md">Recent audit evidence</div>
            <div className="body-sm text-text-muted">Client create, update, note, and document actions stay reviewable through immutable audit records.</div>
          </AppCardHeader>
          <AppCardBody>
            <div className="space-y-3">
              {payload.recentAudit.map((entry) => (
                <div key={entry.id} className="rounded-lg border border-border bg-muted p-4">
                  <div className="font-medium text-text">{entry.action}</div>
                  <div className="body-sm mt-1 text-text-muted">{entry.actorDisplayName} • {entry.subjectType} • {entry.createdAt ? new Date(entry.createdAt).toLocaleString() : '—'}</div>
                </div>
              ))}
            </div>
          </AppCardBody>
        </AppCard>
      ) : <EmptyState title="No audit entries yet" description="Client mutations will appear here once they are recorded through the audit module." />
    }

    return <EmptyState title="Workspace tab unavailable" description="The requested workspace view is not available for this client." />
  }

  return (
    <div className="space-y-6">
      <PageHeader
        title={payload.client.displayName}
        description="The client workspace centralizes profile data, disposition, communications, events, applications, notes, documents, and audit evidence in one governed record view."
        actions={<AppButton type="button" variant="secondary" onClick={() => navigate('/app/clients')}>Back to list</AppButton>}
      />

      <AppCard>
        <AppCardBody>
          <div className="flex flex-wrap items-start justify-between gap-4">
            <div>
              <div className="heading-lg text-text">{payload.client.displayName}</div>
              <div className="body-sm mt-1 text-text-muted">{payload.client.primaryEmail ?? 'No email yet'} • {payload.client.primaryPhone ?? 'No phone yet'}</div>
            </div>
            <div className="flex items-center gap-3">
              <ClientStatusBadge status={payload.client.status} />
              <div className="text-xs text-text-muted">Last activity {payload.summary.lastActivityAt ? new Date(payload.summary.lastActivityAt).toLocaleString() : 'Not recorded yet'}</div>
            </div>
          </div>

          <div className="mt-4 grid gap-3 md:grid-cols-4">
            {summaryCards.map(([label, value]) => (
              <div key={label} className="rounded-lg border border-border bg-muted p-4">
                <div className="label-sm uppercase tracking-[0.12em] text-text-muted">{label}</div>
                <div className="heading-lg mt-2 text-text">{value}</div>
              </div>
            ))}
          </div>
        </AppCardBody>
      </AppCard>

      <ClientWorkspaceTabs tabs={payload.tabs} />
      {renderActivePanel()}

      <ClientQuickNoteDialog
        open={quickNoteOpen}
        onOpenChange={setQuickNoteOpen}
        busy={createNoteMutation.isPending}
        onSubmit={async (body) => {
          await createNoteMutation.mutateAsync(body)
        }}
      />

      <ApplicationCreateDialog
        open={applicationCreateOpen}
        onOpenChange={setApplicationCreateOpen}
        busy={applicationCreateMutation.isPending}
        onSubmit={async (body) => {
          await applicationCreateMutation.mutateAsync(body)
        }}
      />

      <EventCreateDialog
        open={eventCreateOpen}
        onOpenChange={setEventCreateOpen}
        selectedDate={new Date().toISOString().slice(0, 10)}
        initialClientId={clientId ?? ''}
      />
    </div>
  )
}

function SnapshotCard({
  title,
  emptyTitle,
  emptyDescription,
  ctaHref,
  ctaLabel,
  children
}: {
  title: string
  emptyTitle: string
  emptyDescription: string
  ctaHref: string
  ctaLabel: string
  children: ReactNode
}) {
  return (
    <AppCard>
      <AppCardHeader>
        <div className="heading-md">{title}</div>
      </AppCardHeader>
      <AppCardBody className="space-y-4">
        {children ? children : <EmptyState title={emptyTitle} description={emptyDescription} />}
        <AppButton asChild type="button" variant="secondary"><Link to={ctaHref}>{ctaLabel}</Link></AppButton>
      </AppCardBody>
    </AppCard>
  )
}

function ActionButton({
  children,
  onClick,
  disabled = false,
  helper
}: {
  children: ReactNode
  onClick: () => void
  disabled?: boolean
  helper?: string
}) {
  return (
    <div className={cn('space-y-2', disabled && 'opacity-70')}>
      <AppButton type="button" variant="secondary" onClick={onClick} disabled={disabled}>{children}</AppButton>
      {disabled && helper ? <div className="text-xs text-text-muted">{helper}</div> : null}
    </div>
  )
}
