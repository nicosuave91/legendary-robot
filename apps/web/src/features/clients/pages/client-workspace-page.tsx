import { useEffect, useMemo, useState } from 'react'
import { useForm } from 'react-hook-form'
import { z } from 'zod'
import { zodResolver } from '@hookform/resolvers/zod'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { Link, useNavigate, useParams } from 'react-router-dom'
import { PageHeader, AppBadge, AppButton, AppCard, AppCardBody, AppCardHeader, AppInput, AppSelect, AppTextarea, EmptyState, LoadingSkeleton } from '@/components/ui'
import type { ClientDocumentSummary, ClientWorkspaceResponse } from '@/lib/api/generated/client'
import { ClientStatusBadge } from '@/features/clients/components/client-status-badge'
import { ClientWorkspaceTabs } from '@/features/clients/components/client-workspace-tabs'
import { ClientQuickNoteDialog } from '@/features/clients/components/client-quick-note-dialog'
import type { ClientWorkspaceResponseWithOverview } from '@/features/clients/types/client-workspace-overview'
import { ClientDispositionPanel } from '@/features/disposition/components/client-disposition-panel'
import { ClientCommunicationsPanel } from '@/features/communications/components/client-communications-panel'
import { ClientEventsPanel } from '@/features/calendar-tasks/components/client-events-panel'
import { ClientApplicationsPanel } from '@/features/applications/components/client-applications-panel'
import { ApplicationCreateDialog } from '@/features/applications/components/application-create-dialog'
import { EventCreateDialog } from '@/features/calendar-tasks/components/event-create-dialog'
import { applicationsApi, clientsApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import { useToast } from '@/components/shell/toast-host'

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

type ActivityItem = {
  id: string
  kind: string
  title: string
  description: string
  occurredAt: string | null
  href: string | null
}

function toneToBadgeVariant(tone?: string | null): 'neutral' | 'info' | 'success' | 'warning' | 'danger' {
  if (tone === 'info') return 'info'
  if (tone === 'success') return 'success'
  if (tone === 'warning') return 'warning'
  if (tone === 'danger') return 'danger'
  return 'neutral'
}

export function ClientWorkspacePage() {
  const navigate = useNavigate()
  const queryClient = useQueryClient()
  const { clientId, tab } = useParams<{ clientId: string, tab?: WorkspaceTab }>()
  const { notify } = useToast()
  const [noteBody, setNoteBody] = useState('')
  const [documentFile, setDocumentFile] = useState<File | null>(null)
  const [attachmentCategory, setAttachmentCategory] = useState('')
  const [quickNoteOpen, setQuickNoteOpen] = useState(false)
  const [createEventOpen, setCreateEventOpen] = useState(false)
  const [createApplicationOpen, setCreateApplicationOpen] = useState(false)
  const [dispositionOpenSignal, setDispositionOpenSignal] = useState(0)

  const activeTab = validTabs.includes((tab ?? 'overview') as WorkspaceTab) ? (tab ?? 'overview') as WorkspaceTab : 'overview'

  const detailQuery = useQuery({
    enabled: Boolean(clientId),
    queryKey: queryKeys.clients.detail(clientId ?? ''),
    queryFn: () => clientsApi.get(clientId ?? '')
  })

  const payload = detailQuery.data?.data as ClientWorkspaceResponseWithOverview | undefined
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

  const createApplicationMutation = useMutation({
    mutationFn: (body: { productType: string, externalReference?: string | null, amountRequested?: number | null, submittedAt?: string | null }) =>
      applicationsApi.create(clientId ?? '', body),
    onSuccess: async () => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.applications.list(clientId ?? '') }),
        queryClient.invalidateQueries({ queryKey: queryKeys.clients.detail(clientId ?? '') }),
      ])
      setCreateApplicationOpen(false)
      notify({
        title: 'Application created',
        description: 'The client application and related workspace summaries were refreshed from the server.',
        tone: 'success'
      })
    },
    onError: (error) => {
      notify({
        title: 'Application creation failed',
        description: error instanceof Error ? error.message : 'The application could not be created.',
        tone: 'danger'
      })
    }
  })

  const summaryCards = useMemo(() => payload ? [
    ['Notes', payload.summary.notesCount],
    ['Documents', payload.summary.documentsCount],
    ['Events', payload.summary.eventsCount],
    ['Applications', payload.summary.applicationsCount]
  ] : [], [payload])

  const recentActivity = useMemo((): ActivityItem[] => {
    if (!payload?.client) return []

    const items: ActivityItem[] = []

    if (payload.overview?.latestCommunication) {
      items.push({
        id: `communication-${payload.overview.latestCommunication.id}`,
        kind: 'Communication',
        title: payload.overview.latestCommunication.content.subject ?? payload.overview.latestCommunication.content.preview ?? 'Communication activity',
        description: payload.overview.latestCommunication.counterpart.address ?? payload.overview.latestCommunication.status.displayLabel,
        occurredAt: payload.overview.latestCommunication.occurredAt,
        href: `/app/clients/${payload.client.id}/communications`
      })
    }

    if (payload.overview?.nextEvent) {
      items.push({
        id: `event-${payload.overview.nextEvent.id}`,
        kind: 'Event',
        title: payload.overview.nextEvent.title,
        description: `${payload.overview.nextEvent.eventType.replaceAll('_', ' ')} • ${payload.overview.nextEvent.taskSummary.open} open tasks`,
        occurredAt: payload.overview.nextEvent.startsAt,
        href: `/app/clients/${payload.client.id}/events`
      })
    }

    if (payload.overview?.leadApplication) {
      items.push({
        id: `application-${payload.overview.leadApplication.id}`,
        kind: 'Application',
        title: payload.overview.leadApplication.applicationNumber,
        description: `${payload.overview.leadApplication.productType} • ${payload.overview.leadApplication.currentStatus.label}`,
        occurredAt: payload.overview.leadApplication.updatedAt,
        href: `/app/clients/${payload.client.id}/applications`
      })
    }

    if (payload.overview?.recentNote) {
      items.push({
        id: `note-${payload.overview.recentNote.id}`,
        kind: 'Note',
        title: 'Recent note',
        description: payload.overview.recentNote.body,
        occurredAt: payload.overview.recentNote.createdAt,
        href: `/app/clients/${payload.client.id}/notes`
      })
    }

    if (payload.recentDocuments[0]) {
      items.push({
        id: `document-${payload.recentDocuments[0].id}`,
        kind: 'Document',
        title: payload.recentDocuments[0].originalFilename,
        description: payload.recentDocuments[0].attachmentCategory ?? payload.recentDocuments[0].mimeType,
        occurredAt: payload.recentDocuments[0].uploadedAt,
        href: `/app/clients/${payload.client.id}/documents`
      })
    }

    if (payload.recentAudit[0]) {
      items.push({
        id: `audit-${payload.recentAudit[0].id}`,
        kind: 'Audit',
        title: payload.recentAudit[0].action,
        description: `${payload.recentAudit[0].actorDisplayName} • ${payload.recentAudit[0].subjectType}`,
        occurredAt: payload.recentAudit[0].createdAt,
        href: `/app/clients/${payload.client.id}/audit`
      })
    }

    return items
      .sort((left, right) => new Date(right.occurredAt ?? 0).getTime() - new Date(left.occurredAt ?? 0).getTime())
      .slice(0, 5)
  }, [payload])

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
        <div className="body-sm text-text-muted">Update core client details while keeping lifecycle, communication, and document history governed separately.</div>
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

  const renderOverviewPanel = () => {
    const recommendedAction = payload.overview?.recommendedAction
    const latestCommunication = payload.overview?.latestCommunication
    const nextEvent = payload.overview?.nextEvent
    const leadApplication = payload.overview?.leadApplication
    const recentNote = payload.overview?.recentNote

    return (
      <div className="space-y-6">
        <AppCard>
          <AppCardBody>
            <div className="flex flex-wrap gap-3">
              <AppButton type="button" onClick={() => navigate(`/app/clients/${payload.client.id}/communications?compose=call`)} disabled={!payload.client.primaryPhone}>
                Call
              </AppButton>
              <AppButton type="button" variant="secondary" onClick={() => navigate(`/app/clients/${payload.client.id}/communications?compose=sms`)} disabled={!payload.client.primaryPhone}>
                Text
              </AppButton>
              <AppButton type="button" variant="secondary" onClick={() => navigate(`/app/clients/${payload.client.id}/communications?compose=email`)} disabled={!payload.client.primaryEmail}>
                Email
              </AppButton>
              <AppButton type="button" variant="secondary" onClick={() => setCreateEventOpen(true)}>
                Schedule event
              </AppButton>
              <AppButton type="button" variant="secondary" onClick={() => setCreateApplicationOpen(true)}>
                Create application
              </AppButton>
              <AppButton type="button" variant="secondary" onClick={() => setQuickNoteOpen(true)}>
                Add note
              </AppButton>
              <AppButton type="button" variant="secondary" onClick={() => setDispositionOpenSignal((current) => current + 1)}>
                Change disposition
              </AppButton>
            </div>
            <div className="mt-3 flex flex-wrap gap-4 text-xs text-text-muted">
              {!payload.client.primaryPhone ? <span>No primary phone saved</span> : null}
              {!payload.client.primaryEmail ? <span>No primary email saved</span> : null}
            </div>
          </AppCardBody>
        </AppCard>

        <AppCard>
          <AppCardHeader>
            <div className="heading-md">Recommended next step</div>
            <div className="body-sm text-text-muted">What should happen next for this client based on current governed activity.</div>
          </AppCardHeader>
          <AppCardBody>
            {recommendedAction ? (
              <div className="flex flex-wrap items-start justify-between gap-4">
                <div className="space-y-2">
                  <AppBadge variant={toneToBadgeVariant(recommendedAction.tone)}>
                    {recommendedAction.code.replaceAll('_', ' ')}
                  </AppBadge>
                  <div className="heading-md text-text">{recommendedAction.title}</div>
                  <div className="body-sm text-text-muted">{recommendedAction.description}</div>
                </div>
                {recommendedAction.ctaHref && recommendedAction.ctaLabel ? (
                  <AppButton asChild type="button">
                    <Link to={recommendedAction.ctaHref}>{recommendedAction.ctaLabel}</Link>
                  </AppButton>
                ) : null}
              </div>
            ) : (
              <EmptyState title="No urgent action" description="This client does not currently require an immediate next step." />
            )}
          </AppCardBody>
        </AppCard>

        <div className="grid gap-4 xl:grid-cols-2">
          <AppCard>
            <AppCardHeader>
              <div className="heading-md">Latest communication</div>
              <div className="body-sm text-text-muted">Most recent outreach or reply recorded for this client.</div>
            </AppCardHeader>
            <AppCardBody>
              {latestCommunication ? (
                <div className="space-y-3">
                  <div className="flex flex-wrap items-center gap-2">
                    <AppBadge>{latestCommunication.channel.toUpperCase()}</AppBadge>
                    <AppBadge variant={toneToBadgeVariant(latestCommunication.status.tone)}>{latestCommunication.status.displayLabel}</AppBadge>
                  </div>
                  <div className="font-medium text-text">{latestCommunication.content.subject ?? latestCommunication.content.preview ?? 'Communication activity'}</div>
                  <div className="body-sm text-text-muted">{latestCommunication.counterpart.address ?? 'No recipient detail'}</div>
                  <div className="text-xs text-text-muted">{latestCommunication.occurredAt ? new Date(latestCommunication.occurredAt).toLocaleString() : '—'}</div>
                  <AppButton asChild type="button" variant="secondary">
                    <Link to={`/app/clients/${payload.client.id}/communications`}>Open communications</Link>
                  </AppButton>
                </div>
              ) : (
                <EmptyState title="No communications yet" description="Start with a text, email, or call from the action rail." />
              )}
            </AppCardBody>
          </AppCard>

          <AppCard>
            <AppCardHeader>
              <div className="heading-md">Next event</div>
              <div className="body-sm text-text-muted">Upcoming scheduled work linked to this client.</div>
            </AppCardHeader>
            <AppCardBody>
              {nextEvent ? (
                <div className="space-y-3">
                  <div className="flex flex-wrap items-center gap-2">
                    <AppBadge>{nextEvent.eventType.replaceAll('_', ' ')}</AppBadge>
                    <AppBadge variant={nextEvent.taskSummary.blocked > 0 ? 'warning' : 'info'}>
                      {nextEvent.taskSummary.open} open • {nextEvent.taskSummary.blocked} blocked
                    </AppBadge>
                  </div>
                  <div className="font-medium text-text">{nextEvent.title}</div>
                  <div className="body-sm text-text-muted">
                    {nextEvent.startsAt ? new Date(nextEvent.startsAt).toLocaleString() : '—'}
                  </div>
                  <AppButton asChild type="button" variant="secondary">
                    <Link to={`/app/clients/${payload.client.id}/events`}>Open events</Link>
                  </AppButton>
                </div>
              ) : (
                <EmptyState title="No event scheduled" description="Schedule the next task or appointment for this client." />
              )}
            </AppCardBody>
          </AppCard>

          <AppCard>
            <AppCardHeader>
              <div className="heading-md">Lead application</div>
              <div className="body-sm text-text-muted">Current application requiring review or follow-up.</div>
            </AppCardHeader>
            <AppCardBody>
              {leadApplication ? (
                <div className="space-y-3">
                  <div className="flex flex-wrap items-center gap-2">
                    <AppBadge variant={toneToBadgeVariant(leadApplication.currentStatus.tone)}>{leadApplication.currentStatus.label}</AppBadge>
                    <AppBadge variant={leadApplication.ruleSummary.blockingCount > 0 ? 'danger' : leadApplication.ruleSummary.warningCount > 0 ? 'warning' : 'neutral'}>
                      {leadApplication.ruleSummary.warningCount} warnings • {leadApplication.ruleSummary.blockingCount} blocking
                    </AppBadge>
                  </div>
                  <div className="font-medium text-text">{leadApplication.applicationNumber}</div>
                  <div className="body-sm text-text-muted">{leadApplication.productType}</div>
                  <AppButton asChild type="button" variant="secondary">
                    <Link to={`/app/clients/${payload.client.id}/applications`}>Open applications</Link>
                  </AppButton>
                </div>
              ) : (
                <EmptyState title="No applications yet" description="Create the first application to begin workflow and rule tracking." />
              )}
            </AppCardBody>
          </AppCard>

          <AppCard>
            <AppCardHeader>
              <div className="heading-md">Recent note</div>
              <div className="body-sm text-text-muted">Most recent governed note captured on this record.</div>
            </AppCardHeader>
            <AppCardBody>
              {recentNote ? (
                <div className="space-y-3">
                  <div className="font-medium text-text">{recentNote.authorDisplayName}</div>
                  <div className="body-sm text-text-muted">{recentNote.body}</div>
                  <div className="text-xs text-text-muted">{recentNote.createdAt ? new Date(recentNote.createdAt).toLocaleString() : '—'}</div>
                  <AppButton asChild type="button" variant="secondary">
                    <Link to={`/app/clients/${payload.client.id}/notes`}>Open notes</Link>
                  </AppButton>
                </div>
              ) : (
                <EmptyState title="No recent note" description="Add the first note from the action rail or notes tab." />
              )}
            </AppCardBody>
          </AppCard>
        </div>

        <ClientDispositionPanel clientId={clientId ?? ''} payload={payload as ClientWorkspaceResponse} openSignal={dispositionOpenSignal} />

        <AppCard>
          <AppCardHeader>
            <div className="heading-md">Recent activity</div>
            <div className="body-sm text-text-muted">The latest record changes, notes, and evidence for this client.</div>
          </AppCardHeader>
          <AppCardBody>
            {recentActivity.length ? (
              <div className="space-y-3">
                {recentActivity.map((item) => (
                  <div key={item.id} className="rounded-lg border border-border bg-muted p-4">
                    <div className="flex flex-wrap items-start justify-between gap-3">
                      <div>
                        <div className="flex flex-wrap items-center gap-2">
                          <AppBadge variant="neutral">{item.kind}</AppBadge>
                        </div>
                        <div className="mt-2 font-medium text-text">{item.title}</div>
                        <div className="body-sm mt-1 text-text-muted">{item.description}</div>
                        <div className="text-xs text-text-muted">{item.occurredAt ? new Date(item.occurredAt).toLocaleString() : '—'}</div>
                      </div>
                      {item.href ? (
                        <AppButton asChild type="button" variant="secondary">
                          <Link to={item.href}>Open</Link>
                        </AppButton>
                      ) : null}
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <EmptyState title="No recent activity" description="Client activity will appear here as communications, notes, documents, and status changes occur." />
            )}
          </AppCardBody>
        </AppCard>

        {renderProfileCard()}
      </div>
    )
  }

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
                {payload.recentDocuments.length ? payload.recentDocuments.map((document: ClientDocumentSummary) => (
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
        description="The client workspace centralizes profile data, lifecycle state, communications, events, applications, notes, documents, and audit evidence in one governed record view."
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

      <ClientQuickNoteDialog clientId={clientId ?? ''} open={quickNoteOpen} onOpenChange={setQuickNoteOpen} />
      <EventCreateDialog open={createEventOpen} onOpenChange={setCreateEventOpen} selectedDate={new Date().toISOString().slice(0, 10)} initialClientId={clientId ?? ''} />
      <ApplicationCreateDialog
        open={createApplicationOpen}
        onOpenChange={setCreateApplicationOpen}
        busy={createApplicationMutation.isPending}
        onSubmit={async (body) => {
          await createApplicationMutation.mutateAsync(body)
        }}
      />
    </div>
  )
}
