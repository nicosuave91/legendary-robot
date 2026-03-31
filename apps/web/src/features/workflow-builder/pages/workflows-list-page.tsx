import { useMemo, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { Link, useNavigate } from 'react-router-dom'
import { AppButton, AppCard, AppCardBody, AppCardHeader, AppInput, AppTextarea, EmptyState, LoadingSkeleton, PageHeader } from '@/components/ui'
import { workflowsApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import { WorkflowStatusBadge } from '@/features/workflow-builder/components/workflow-status-badge'
import { useToast } from '@/components/shell/toast-host'

const defaultTriggerDefinition = JSON.stringify({ event: 'application.created', subjectType: 'application', filters: [] }, null, 2)
const defaultStepsDefinition = JSON.stringify([
  { type: 'condition', definition: { fact: 'currentStatus', operator: 'eq', value: 'submitted' } },
  { type: 'wait', definition: { durationMinutes: 60 } },
  { type: 'create_client_note', definition: { title: 'Follow-up', bodyTemplate: 'This workflow matched a newly submitted application.' } }
], null, 2)

export function WorkflowsListPage() {
  const queryClient = useQueryClient()
  const navigate = useNavigate()
  const { notify } = useToast()
  const [statusFilter, setStatusFilter] = useState('')
  const [form, setForm] = useState({
    workflowKey: '',
    name: '',
    description: '',
    triggerDefinition: defaultTriggerDefinition,
    stepsDefinition: defaultStepsDefinition,
  })

  const filters = useMemo(() => (statusFilter ? { status: statusFilter } : {}), [statusFilter])
  const listQuery = useQuery({
    queryKey: queryKeys.workflows.list(filters),
    queryFn: () => workflowsApi.list(filters)
  })

  const createMutation = useMutation({
    mutationFn: async () => workflowsApi.create({
      workflowKey: form.workflowKey,
      name: form.name,
      description: form.description || null,
      triggerDefinition: JSON.parse(form.triggerDefinition),
      stepsDefinition: JSON.parse(form.stepsDefinition)
    }),
    onSuccess: async (response) => {
      await queryClient.invalidateQueries({ queryKey: queryKeys.workflows.all })
      notify({ title: 'Draft workflow created', description: 'The workflow catalog now has an editable Sprint 8 draft version.', tone: 'success' })
      navigate(`/app/workflows/${response.data.workflow.id}`)
    },
    onError: (error) => notify({ title: 'Workflow creation failed', description: error instanceof Error ? error.message : 'The draft workflow could not be created.', tone: 'danger' })
  })

  const items = listQuery.data?.data.items ?? []

  return (
    <div className="space-y-6">
      <PageHeader title="Workflow Builder" description="Workflows publish immutable versions, bind execution to a version ID, and queue durable run logs for monitoring." />
      <div className="grid gap-6 xl:grid-cols-[minmax(0,1.3fr)_420px]">
        <AppCard>
          <AppCardHeader>
            <div className="flex flex-wrap items-center justify-between gap-3">
              <div>
                <div className="heading-md">Workflow catalog</div>
                <div className="body-sm text-text-muted">Monitor draft and published workflows before drilling into run evidence.</div>
              </div>
              <AppInput value={statusFilter} onChange={(event) => setStatusFilter(event.currentTarget.value)} placeholder="Filter by status" />
            </div>
          </AppCardHeader>
          <AppCardBody>
            {listQuery.isLoading ? <LoadingSkeleton lines={8} /> : null}
            {!listQuery.isLoading && items.length === 0 ? <EmptyState title="No workflows yet" description="Create the first workflow draft to establish versioned execution behavior." /> : null}
            <div className="space-y-3">
              {items.map((item) => (
                <Link key={item.id} to={`/app/workflows/${item.id}`} className="block rounded-lg border border-border bg-muted p-4 transition hover:border-primary/40 hover:bg-surface">
                  <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                      <div className="heading-md text-text">{item.name}</div>
                      <div className="body-sm text-text-muted">{item.workflowKey} • trigger {item.triggerSummary}</div>
                    </div>
                    <WorkflowStatusBadge status={item.status} />
                  </div>
                  <div className="body-sm mt-2 text-text-muted">{item.description || 'No description captured yet.'}</div>
                  <div className="mt-3 grid gap-3 text-xs text-text-muted sm:grid-cols-3">
                    <div>Draft version: v{item.currentDraftVersionNumber ?? '—'}</div>
                    <div>Published version: v{item.latestPublishedVersionNumber ?? '—'}</div>
                    <div>Updated: {item.updatedAt ? new Date(item.updatedAt).toLocaleString() : '—'}</div>
                  </div>
                </Link>
              ))}
            </div>
          </AppCardBody>
        </AppCard>

        <AppCard>
          <AppCardHeader>
            <div className="heading-md">Create draft workflow</div>
            <div className="body-sm text-text-muted">Drafts remain editable until publish. Runs only execute against published immutable versions.</div>
          </AppCardHeader>
          <AppCardBody>
            <div className="space-y-4">
              <div className="space-y-2"><label className="label-sm text-text">Workflow key</label><AppInput value={form.workflowKey} onChange={(event) => setForm((current) => ({ ...current, workflowKey: event.currentTarget.value }))} placeholder="app-submission-followup" /></div>
              <div className="space-y-2"><label className="label-sm text-text">Name</label><AppInput value={form.name} onChange={(event) => setForm((current) => ({ ...current, name: event.currentTarget.value }))} placeholder="Submitted application follow-up" /></div>
              <div className="space-y-2"><label className="label-sm text-text">Description</label><AppTextarea value={form.description} onChange={(event) => setForm((current) => ({ ...current, description: event.currentTarget.value }))} placeholder="Describe when this workflow should run and what it does." /></div>
              <div className="space-y-2"><label className="label-sm text-text">Trigger definition JSON</label><AppTextarea className="min-h-[160px] font-mono text-xs" value={form.triggerDefinition} onChange={(event) => setForm((current) => ({ ...current, triggerDefinition: event.currentTarget.value }))} /></div>
              <div className="space-y-2"><label className="label-sm text-text">Steps definition JSON</label><AppTextarea className="min-h-[220px] font-mono text-xs" value={form.stepsDefinition} onChange={(event) => setForm((current) => ({ ...current, stepsDefinition: event.currentTarget.value }))} /></div>
              <AppButton type="button" onClick={() => createMutation.mutate()} disabled={createMutation.isPending || !form.workflowKey.trim() || !form.name.trim()}>{createMutation.isPending ? 'Creating…' : 'Create workflow draft'}</AppButton>
            </div>
          </AppCardBody>
        </AppCard>
      </div>
    </div>
  )
}
