import type { ReactNode } from 'react'
import { useEffect, useMemo, useState } from 'react'
import { useForm } from 'react-hook-form'
import { z } from 'zod'
import { zodResolver } from '@hookform/resolvers/zod'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { Link, useNavigate, useParams } from 'react-router-dom'
import { PageHeader, AppBadge, AppButton, AppCard, AppCardBody, AppCardHeader, AppInput, AppSelect, AppTextarea, EmptyState, LoadingSkeleton } from '@/components/ui'
import { ClientStatusBadge } from '@/features/clients/components/client-status-badge'
import { ClientWorkspaceTabs } from '@/features/clients/components/client-workspace-tabs'
import { ClientQuickNoteDialog } from '@/features/clients/components/client-quick-note-dialog'
import { ClientDispositionPanel } from '@/features/disposition/components/client-disposition-panel'
import { ClientCommunicationsPanel } from '@/features/communications/components/client-communications-panel'
import { ClientEventsPanel } from '@/features/calendar-tasks/components/client-events-panel'
import { ClientApplicationsPanel } from '@/features/applications/components/client-applications-panel'
import { ApplicationCreateDialog } from '@/features/applications/components/application-create-dialog'
import { EventCreateDialog } from '@/features/calendar-tasks/components/event-create-dialog'
import { clientsApi, applicationsApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import { useToast } from '@/components/shell/toast-host'
import { cn } from '@/lib/utils/cn'
import type { ClientWorkspaceResponseWithOverview } from '@/features/clients/types/client-workspace-overview'

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

function toneToBadgeVariant(tone: string): 'neutral' | 'info' | 'success' | 'warning' | 'danger' {
  if (tone === 'success') return 'success'
  if (tone === 'warning') return 'warning'
  if (tone === 'danger') return 'danger'
  if (tone === 'info') return 'info'
  return 'neutral'
}

function formatDateTime(value?: string | null) {
  return value ? new Date(value).toLocaleString() : '—'
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
  const [eventCreateOpen, setEventCreateOpen] = useState(false)
  const [applicationCreateOpen, setApplicationCreateOpen] = useState(false)

  const activeTab = validTabs.includes((tab ?? 'overview') as WorkspaceTab) ? (tab ?? 'overview') as WorkspaceTab : 'overview'

  const detailQuery = useQuery({
    enabled: Boolean(clientId),
    queryKey: queryKeys.clients.detail(clientId ?? ''),
    queryFn: () => clientsApi.get(clientId ?? '') as Promise<{ data: ClientWorkspaceResponseWithOverview }>,
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
      setQuickNoteOpen(false)
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
      setApplicationCreateOpen(false)
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.clients.detail(clientId ?? '') }),
        queryClient.invalidateQueries({ queryKey: queryKeys.applications.list(clientId ?? '') }),
      ])
      notify({
        title: 'Application created',
        description: 'The client overview and application workspace now reflect the new governed record.',
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

  if (detailQuery.isLoading && !payload) {
    return <LoadingSkeleton lines={8} />
  }

  if (!payload?.client) {
    return <EmptyState title="Client not found" description="The requested workspace could not be resolved for the current tenant and role scope." />
  }

  const overview = payload.overview

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
        <AppCardBody>
          <div className="flex flex-wrap gap-3">
            <AppButton asChild type="button" disabled={!payload.client.primaryPhone}>
              <Link to={`/app/clients/${payload.client.id}/communications?compose=call`}>Call</Link>
            </AppButton>
            <AppButton asChild type="button" variant="secondary" disabled={!payload.client.primaryPhone}>
              <Link to={`/app/clients/${payload.client.id}/communications?compose=sms`}>Text</Link>
            </AppButton>
            <AppButton asChild type="button" variant="secondary" disabled={!payload.client.primaryEmail}>
              <Link to={`/app/clients/${payload.client.id}/communications?compose=email`}>Email</Link>
            </AppButton>
            <AppButton type="button" variant="secondary" onClick={() => setEventCreateOpen(true)}>Schedule event</AppButton>
            <AppButton type="button" variant="secondary" onClick={() => setApplicationCreateOpen(true)}>Create application</AppButton>
            <AppButton type="button" variant="secondary" onClick={() => setQuickNoteOpen(true)}>Add note</AppButton>
            <AppButton type="button" variant="secondary" onClick={() => document.getElementById('client-disposition-panel')?.scrollIntoView({ behavior: 'smooth', block: 'start' })}>
              Change disposition
            </AppButton>
          </div>
          <div className="mt-3 text-xs text-text-muted">
            {payload.client.primaryPhone ? null : 'Add a primary phone to enable call and text actions. '}
            {payload.client.primaryEmail ? null : 'Add a primary email to enable email actions.'}
          </div>
        </AppCardBody>
      </AppCard>

      <AppCard>
        <AppCardHeader>
          <div className="heading-md">Recommended next step</div>
          <div className="body-sm text-text-muted">What should happen next for this client based on current governed activity.</div>
        </AppCardHeader>
        <AppCardBody>
          {overview?.recommendedAction ? (
            <div className="flex flex-wrap items-start justify-between gap-4 rounded-lg border border-border bg-muted p-4">
              <div className="space-y-2">
                <AppBadge variant={toneToBadgeVariant(overview.recommendedAction.tone)}>
                  {overview.recommendedAction.code.replaceAll('_', ' ')}
                </AppBadge>
                <div className="heading-md text-text">{overview.recommendedAction.title}</div>
                <div className="body-sm text-text-muted">{overview.recommendedAction.description}</div>
              </div>
              <AppButton asChild type="button">
                <Link to={overview.recommendedAction.ctaHref}>{overview.recommendedAction.ctaLabel}</Link>
              </AppButton>
            </div>
          ) : (
            <EmptyState title="No urgent action" description="This client does not currently have a blocked workflow, failed outreach, or upcoming task needing attention." />
          )}
        </AppCardBody>
      </AppCard>

      <div className="grid gap-4 xl:grid-cols-2">
        <SnapshotCard
          title="Latest communication"
          ctaHref={`/app/clients/${payload.client.id}/communications`}
          ctaLabel="Open communications"
        >
          {overview?.latestCommunication ? (
            <div className="space-y-2">
              <div className="flex flex-wrap items-center gap-2">
                <AppBadge>{overview.latestCommunication.channel.toUpperCase()}</AppBadge>
                <AppBadge variant="neutral">{overview.latestCommunication.direction}</AppBadge>
                <AppBadge variant={toneToBadgeVariant(overview.latestCommunication.status.tone)}>
                  {overview.latestCommunication.status.label}
                </AppBadge>
              </div>
              <div className="body-sm text-text-muted">{overview.latestCommunication.preview ?? 'No preview available.'}</div>
              <div className="text-xs text-text-muted">{formatDateTime(overview.latestCommunication.occurredAt)}</div>
            </div>
          ) : (
            <EmptyState title="No communications yet" description="Start with a text, email, or call from the action bar." />
          )}
        </SnapshotCard>

        <SnapshotCard
          title="Next event"
          ctaHref={`/app/clients/${payload.client.id}/events`}
          ctaLabel="Open events"
        >
          {overview?.nextEvent ? (
            <div className="space-y-2">
              <div className="font-medium text-text">{overview.nextEvent.title}</div>
              <div className="body-sm text-text-muted">
                {overview.nextEvent.eventType.replaceAll('_', ' ')} • {formatDateTime(overview.nextEvent.startsAt)}
              </div>
              <div className="flex flex-wrap gap-2">
                <AppBadge variant="info">{overview.nextEvent.taskSummary.open} open tasks</AppBadge>
                <AppBadge variant={overview.nextEvent.taskSummary.blocked > 0 ? 'warning' : 'neutral'}>
                  {overview.nextEvent.taskSummary.blocked} blocked
                </AppBadge>
              </div>
            </div>
          ) : (
            <EmptyState title="No event scheduled" description="Schedule the next task or appointment for this client." />
          )}
        </SnapshotCard>

        <SnapshotCard
          title="Lead application"
          ctaHref={`/app/clients/${payload.client.id}/applications`}
          ctaLabel="Open applications"
        >
          {overview?.leadApplication ? (
            <div className="space-y-2">
              <div className="font-medium text-text">{overview.leadApplication.applicationNumber}</div>
              <div className="body-sm text-text-muted">{overview.leadApplication.productType}</div>
              <div className="flex flex-wrap gap-2">
                <AppBadge variant={toneToBadgeVariant(overview.leadApplication.currentStatus.tone)}>
                  {overview.leadApplication.currentStatus.label}
                </AppBadge>
                <AppBadge variant={overview.leadApplication.ruleSummary.blockingCount > 0 ? 'danger' : 'warning'}>
                  {overview.leadApplication.ruleSummary.blockingCount} blocking
                </AppBadge>
                <AppBadge variant="warning">{overview.leadApplication.ruleSummary.warningCount} warnings</AppBadge>
              </div>
            </div>
          ) : (
            <EmptyState title="No applications yet" description="Create the first application to begin workflow and rule tracking." />
          )}
        </SnapshotCard>

        <SnapshotCard
          title="Recent note"
          ctaHref={`/app/clients/${payload.client.id}/notes`}
          ctaLabel="Open notes"
        >
          {overview?.recentNote ? (
            <div className="space-y-2">
              <div className="body-sm text-text-muted">{overview.recentNote.body}</div>
              <div className="text-xs text-text-muted">
                {overview.recentNote.authorDisplayName} • {formatDateTime(overview.recentNote.createdAt)}
              </div>
            </div>
          ) : (
            <EmptyState title="No notes yet" description="Add a note from the action bar to capture the latest client context." />
          )}
        </SnapshotCard>
      </div>

      <div id="client-disposition-panel">
        <ClientDispositionPanel clientId={clientId ?? ''} payload={payload as any} />
      </div>

      <AppCard>
        <AppCardHeader>
          <div className="heading-md">Recent activity</div>
          <div className="body-sm text-text-muted">The latest record changes, notes, and evidence for this client.</div>
        </AppCardHeader>
        <AppCardBody>
          <div className="space-y-3">
            {payload.recentAudit.slice(0, 5).map((entry) => (
              <div key={entry.id} className="rounded-lg border border-border bg-muted p-4">
                <div className="flex flex-wrap items-center justify-between gap-3">
                  <div className="font-medium text-text">{entry.action}</div>
                  <div className="text-xs text-text-muted">{formatDateTime(entry.createdAt)}</div>
                </div>
                <div className="body-sm mt-1 text-text-muted">{entry.actorDisplayName} • {entry.subjectType}</div>
              </div>
            ))}
            {payload.recentAudit.length === 0 ? (
              <EmptyState title="No recent activity" description="Audit-backed client activity will appear here after the first governed mutation." />
            ) : null}
          </div>
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
                )) : <EmptyState title="No documents yet" description="Upload the first document to validate Sprint 4 governed storage and metadata handling." />}
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
        onSubmit={async ({ body }) => {
          await createNoteMutation.mutateAsync(body)
        }}
      />

      <EventCreateDialog
        open={eventCreateOpen}
        onOpenChange={setEventCreateOpen}
        selectedDate={new Date().toISOString().slice(0, 10)}
        initialClientId={payload.client.id}
      />

      <ApplicationCreateDialog
        open={applicationCreateOpen}
        onOpenChange={setApplicationCreateOpen}
        busy={createApplicationMutation.isPending}
        onSubmit={async (body) => {
          await createApplicationMutation.mutateAsync(body)
        }}
      />
    </div>
  )
}

function SnapshotCard({
  title,
  ctaHref,
  ctaLabel,
  children,
}: {
  title: string
  ctaHref: string
  ctaLabel: string
  children: ReactNode
}) {
  return (
    <AppCard>
      <AppCardHeader>
        <div className="flex items-start justify-between gap-3">
          <div className="heading-md">{title}</div>
          <AppButton asChild type="button" variant="secondary" size="sm">
            <Link to={ctaHref}>{ctaLabel}</Link>
          </AppButton>
        </div>
      </AppCardHeader>
      <AppCardBody>{children}</AppCardBody>
    </AppCard>
  )
}
