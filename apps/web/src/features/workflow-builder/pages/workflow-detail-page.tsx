import { useEffect, useMemo, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { Link, useParams } from 'react-router-dom'
import { AppBadge, AppButton, AppCard, AppCardBody, AppCardHeader, AppInput, AppTextarea, EmptyState, LoadingSkeleton, PageHeader } from '@/components/ui'
import { workflowsApi, type WorkflowDetailEnvelopeWithDraftValidation } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import { WorkflowStatusBadge } from '@/features/workflow-builder/components/workflow-status-badge'
import { useToast } from '@/components/shell/toast-host'

function pretty(value: unknown) {
  return JSON.stringify(value ?? {}, null, 2)
}

export function WorkflowDetailPage() {
  const { workflowId = '' } = useParams<{ workflowId: string }>()
  const queryClient = useQueryClient()
  const { notify } = useToast()
  const [selectedRunId, setSelectedRunId] = useState<string | null>(null)

  const detailQuery = useQuery({
    enabled: Boolean(workflowId),
    queryKey: queryKeys.workflows.detail(workflowId),
    queryFn: () => workflowsApi.get(workflowId)
  })

  const runsQuery = useQuery({
    enabled: Boolean(workflowId),
    queryKey: queryKeys.workflows.runs(workflowId),
    queryFn: () => workflowsApi.runs(workflowId)
  })

  const runDetailQuery = useQuery({
    enabled: Boolean(workflowId && selectedRunId),
    queryKey: queryKeys.workflows.runDetail(workflowId, selectedRunId ?? ''),
    queryFn: () => workflowsApi.run(workflowId, selectedRunId ?? '')
  })

  const payload = detailQuery.data?.data as WorkflowDetailEnvelopeWithDraftValidation['data'] | undefined
  const draftVersion = useMemo(() => payload?.versions.find((version) => version.lifecycleState === 'draft') ?? payload?.versions[0], [payload])
  const [form, setForm] = useState({
    name: '',
    description: '',
    triggerDefinition: '{}',
    stepsDefinition: '[]'
  })

  useEffect(() => {
    if (!payload || !draftVersion) return

    setForm({
      name: payload.workflow.name,
      description: payload.workflow.description ?? '',
      triggerDefinition: pretty(draftVersion.triggerDefinition),
      stepsDefinition: pretty(draftVersion.stepsDefinition)
    })
  }, [draftVersion, payload])

  useEffect(() => {
    const firstRunId = runsQuery.data?.data.items?.[0]?.id
    if (!selectedRunId && firstRunId) setSelectedRunId(firstRunId)
  }, [runsQuery.data, selectedRunId])

  const saveMutation = useMutation({
    mutationFn: async () => workflowsApi.updateDraft(workflowId, {
      name: form.name,
      description: form.description || null,
      triggerDefinition: JSON.parse(form.triggerDefinition),
      stepsDefinition: JSON.parse(form.stepsDefinition)
    }),
    onSuccess: async () => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.workflows.detail(workflowId) }),
        queryClient.invalidateQueries({ queryKey: queryKeys.workflows.all })
      ])
      notify({ title: 'Workflow draft saved', description: 'The mutable workflow draft remains separate from published execution history.', tone: 'success' })
    },
    onError: (error) => notify({ title: 'Workflow save failed', description: error instanceof Error ? error.message : 'Unable to save the workflow draft.', tone: 'danger' })
  })

  const publishMutation = useMutation({
    mutationFn: async () => workflowsApi.publish(workflowId),
    onSuccess: async () => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.workflows.detail(workflowId) }),
        queryClient.invalidateQueries({ queryKey: queryKeys.workflows.runs(workflowId) }),
        queryClient.invalidateQueries({ queryKey: queryKeys.workflows.all })
      ])
      notify({ title: 'Workflow published', description: 'Future execution now binds to the new immutable workflow version.', tone: 'success' })
    },
    onError: (error) => notify({ title: 'Workflow publish failed', description: error instanceof Error ? error.message : 'The workflow could not be published.', tone: 'danger' })
  })

  if (detailQuery.isLoading) return <LoadingSkeleton lines={10} />
  if (!payload) return <EmptyState title="Workflow not found" description="The requested workflow could not be resolved for the active tenant." />

  const runs = runsQuery.data?.data.items ?? []
  const runDetail = runDetailQuery.data?.data
  const draftValidation = payload.draftValidation

  return (
    <div className="space-y-6">
      <PageHeader
        title={payload.workflow.name}
        description="Workflow execution stays queue-driven and binds every run to a published immutable version ID."
        actions={
          <>
            <Link to="/app/workflows"><AppButton type="button" variant="secondary">Back to workflows</AppButton></Link>
            <AppButton type="button" onClick={() => publishMutation.mutate()} disabled={publishMutation.isPending || (draftValidation?.hasDraft && !draftValidation.isValid)}>
              {publishMutation.isPending ? 'Publishing…' : 'Publish latest draft'}
            </AppButton>
          </>
        }
      />

      <div className="grid gap-6 xl:grid-cols-[minmax(0,1.3fr)_420px]">
        <div className="space-y-6">
          <AppCard>
            <AppCardHeader>
              <div className="flex flex-wrap items-center justify-between gap-3">
                <div>
                  <div className="heading-md">Draft editor</div>
                  <div className="body-sm text-text-muted">Workflow draft definitions remain editable until publish. Published versions are read-only execution sources.</div>
                </div>
                <div className="flex items-center gap-2">
                  <WorkflowStatusBadge status={payload.workflow.status} />
                  {draftVersion?.lifecycleState === 'draft' ? <AppBadge variant="info">Editable draft</AppBadge> : <AppBadge variant="warning">Revision will be cloned</AppBadge>}
                </div>
              </div>
            </AppCardHeader>
            <AppCardBody>
              <div className="space-y-4">
                <div className="space-y-2"><label className="label-sm text-text">Name</label><AppInput value={form.name} onChange={(event) => setForm((current) => ({ ...current, name: event.currentTarget.value }))} /></div>
                <div className="space-y-2"><label className="label-sm text-text">Description</label><AppTextarea value={form.description} onChange={(event) => setForm((current) => ({ ...current, description: event.currentTarget.value }))} /></div>
                <div className="space-y-2"><label className="label-sm text-text">Trigger definition JSON</label><AppTextarea className="min-h-[160px] font-mono text-xs" value={form.triggerDefinition} onChange={(event) => setForm((current) => ({ ...current, triggerDefinition: event.currentTarget.value }))} /></div>
                <div className="space-y-2"><label className="label-sm text-text">Steps definition JSON</label><AppTextarea className="min-h-[220px] font-mono text-xs" value={form.stepsDefinition} onChange={(event) => setForm((current) => ({ ...current, stepsDefinition: event.currentTarget.value }))} /></div>
                <AppButton type="button" onClick={() => saveMutation.mutate()} disabled={saveMutation.isPending}>{saveMutation.isPending ? 'Saving…' : payload.workflow.latestPublishedVersionNumber && draftVersion?.lifecycleState !== 'draft' ? 'Create draft revision' : 'Save draft changes'}</AppButton>
              </div>
            </AppCardBody>
          </AppCard>

          <AppCard>
            <AppCardHeader>
              <div className="flex flex-wrap items-center justify-between gap-3">
                <div>
                  <div className="heading-md">Draft validation</div>
                  <div className="body-sm text-text-muted">Publish now enforces executable trigger and step definitions before a draft can become immutable runtime truth.</div>
                </div>
                {draftValidation?.hasDraft ? (
                  draftValidation.isValid ? <AppBadge variant="success">Publishable</AppBadge> : <AppBadge variant="danger">Needs fixes</AppBadge>
                ) : (
                  <AppBadge variant="secondary">No draft</AppBadge>
                )}
              </div>
            </AppCardHeader>
            <AppCardBody>
              {!draftValidation?.hasDraft ? <EmptyState title="No draft to validate" description="Create or clone a draft revision to see publish-time validation results." /> : null}
              {draftValidation?.hasDraft && draftValidation.isValid ? (
                <div className="rounded-lg border border-border bg-muted p-4">
                  <div className="font-medium text-text">Draft version is publishable.</div>
                  <div className="body-sm mt-2 text-text-muted">The current trigger and step definitions match the supported runtime contract.</div>
                </div>
              ) : null}
              {draftValidation?.hasDraft && !draftValidation.isValid ? (
                <div className="space-y-3">
                  {draftValidation.errors.map((issue) => (
                    <div key={`${issue.path}-${issue.code}`} className="rounded-lg border border-danger/30 bg-danger/5 p-4">
                      <div className="flex flex-wrap items-center justify-between gap-3">
                        <div className="font-medium text-text">{issue.code}</div>
                        <div className="text-xs text-text-muted">{issue.path}</div>
                      </div>
                      <div className="body-sm mt-2 text-text-muted">{issue.message}</div>
                    </div>
                  ))}
                </div>
              ) : null}
            </AppCardBody>
          </AppCard>

          <AppCard>
            <AppCardHeader>
              <div className="heading-md">Run monitoring</div>
              <div className="body-sm text-text-muted">Every run stores durable status, version binding, and append-only execution logs.</div>
            </AppCardHeader>
            <AppCardBody>
              {runsQuery.isLoading ? <LoadingSkeleton lines={6} /> : null}
              {!runsQuery.isLoading && runs.length === 0 ? <EmptyState title="No workflow runs yet" description="Matching domain events will queue runs here after the workflow is published." /> : null}
              <div className="grid gap-4 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
                <div className="space-y-3">
                  {runs.map((run) => (
                    <button key={run.id} type="button" className={`w-full rounded-lg border p-4 text-left ${selectedRunId === run.id ? 'border-primary bg-surface' : 'border-border bg-muted'}`} onClick={() => setSelectedRunId(run.id)}>
                      <div className="flex flex-wrap items-center justify-between gap-3">
                        <div>
                          <div className="font-medium text-text">{run.triggerEvent}</div>
                          <div className="text-xs text-text-muted">Subject {run.subjectType} #{run.subjectId}</div>
                        </div>
                        <WorkflowStatusBadge status={run.status} />
                      </div>
                      <div className="mt-2 text-xs text-text-muted">Version {run.workflowVersionId} • queued {run.queuedAt ? new Date(run.queuedAt).toLocaleString() : '—'}</div>
                    </button>
                  ))}
                </div>
                <div>
                  {runDetailQuery.isLoading ? <LoadingSkeleton lines={5} /> : null}
                  {!runDetail && !runDetailQuery.isLoading ? <EmptyState title="Select a run" description="Choose a queued or completed run to inspect append-only execution logs." /> : null}
                  {runDetail ? (
                    <div className="space-y-3">
                      <div className="rounded-lg border border-border bg-muted p-4">
                        <div className="flex flex-wrap items-center justify-between gap-3">
                          <div>
                            <div className="font-medium text-text">Run {runDetail.run.id}</div>
                            <div className="text-xs text-text-muted">Workflow version {runDetail.run.workflowVersionId}</div>
                          </div>
                          <WorkflowStatusBadge status={runDetail.run.status} />
                        </div>
                        <div className="mt-2 text-xs text-text-muted">Correlation {runDetail.run.correlationId || 'none'} • failure {runDetail.run.failedAt ? new Date(runDetail.run.failedAt).toLocaleString() : 'not failed'}</div>
                      </div>
                      {runDetail.logs.map((log) => (
                        <div key={log.id} className="rounded-lg border border-border bg-muted p-4">
                          <div className="flex flex-wrap items-center justify-between gap-3">
                            <div className="font-medium text-text">{log.logType}</div>
                            <div className="text-xs text-text-muted">step {log.stepIndex ?? '—'}</div>
                          </div>
                          <div className="body-sm mt-2 text-text-muted">{log.message}</div>
                          <div className="mt-2 text-xs text-text-muted">{log.occurredAt ? new Date(log.occurredAt).toLocaleString() : '—'}</div>
                          <pre className="mt-3 overflow-x-auto rounded-md bg-surface p-3 text-xs text-text-muted">{pretty(log.payloadSnapshot)}</pre>
                        </div>
                      ))}
                    </div>
                  ) : null}
                </div>
              </div>
            </AppCardBody>
          </AppCard>
        </div>

        <div className="space-y-6">
          <AppCard>
            <AppCardHeader>
              <div className="heading-md">Version history</div>
              <div className="body-sm text-text-muted">Publication freezes trigger criteria and ordered steps into an immutable execution source.</div>
            </AppCardHeader>
            <AppCardBody>
              <div className="space-y-3">
                {payload.versions.map((version) => (
                  <div key={version.id} className="rounded-lg border border-border bg-muted p-4">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                      <div>
                        <div className="font-medium text-text">Version {version.versionNumber}</div>
                        <div className="text-xs text-text-muted">checksum {version.checksum.slice(0, 12)}…</div>
                      </div>
                      <WorkflowStatusBadge status={version.lifecycleState} />
                    </div>
                    <div className="mt-2 text-xs text-text-muted">Published: {version.publishedAt ? new Date(version.publishedAt).toLocaleString() : 'Not published'} • by {version.publishedBy ?? '—'}</div>
                    <pre className="mt-3 overflow-x-auto rounded-md bg-surface p-3 text-xs text-text-muted">{pretty({ triggerDefinition: version.triggerDefinition, stepsDefinition: version.stepsDefinition })}</pre>
                  </div>
                ))}
              </div>
            </AppCardBody>
          </AppCard>

          <AppCard>
            <AppCardHeader>
              <div className="heading-md">Runtime posture</div>
            </AppCardHeader>
            <AppCardBody>
              <div className="body-sm space-y-2 text-text-muted">
                <p>Workflow key: <span className="font-medium text-text">{payload.workflow.workflowKey}</span></p>
                <p>Status: <span className="font-medium text-text">{payload.workflow.status}</span></p>
                <p>Trigger summary: <span className="font-medium text-text">{payload.workflow.triggerSummary}</span></p>
                <p>Latest published version: <span className="font-medium text-text">v{payload.workflow.latestPublishedVersionNumber ?? '—'}</span></p>
              </div>
            </AppCardBody>
          </AppCard>
        </div>
      </div>
    </div>
  )
}
