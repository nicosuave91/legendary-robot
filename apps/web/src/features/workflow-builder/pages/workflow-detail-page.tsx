import { useEffect, useMemo, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { Link, useParams } from 'react-router-dom'
import {
  AppBadge,
  AppButton,
  AppCard,
  AppCardBody,
  AppCardHeader,
  AppDialog,
  AppDialogContent,
  AppInput,
  AppTextarea,
  EmptyState,
  LoadingSkeleton,
  PageCanvas,
  PageHeader,
  PageSplit,
} from '@/components/ui'
import { WorkflowStatusBadge } from '@/features/workflow-builder/components/workflow-status-badge'
import { WorkflowStepList } from '@/features/workflow-builder/components/workflow-step-list'
import { WorkflowTriggerBuilder } from '@/features/workflow-builder/components/workflow-trigger-builder'
import { WorkflowUnsavedChangesBar } from '@/features/workflow-builder/components/workflow-unsaved-changes-bar'
import type { WorkflowBuilderState } from '@/features/workflow-builder/workflow-builder-types'
import {
  compileWorkflowBuilderToContract,
  parseWorkflowContractToBuilderState,
  validateBuilderStateBeforeSave,
} from '@/features/workflow-builder/workflow-builder-utils'
import { workflowsApi, type WorkflowDetailEnvelopeWithDraftValidation } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import { useToast } from '@/components/shell/toast-host'

function pretty(value: unknown) {
  return JSON.stringify(value ?? {}, null, 2)
}

function buildSnapshot(args: {
  name: string
  description: string
  builderState: WorkflowBuilderState
}) {
  const compiled = compileWorkflowBuilderToContract(args.builderState)

  return JSON.stringify({
    name: args.name,
    description: args.description,
    triggerDefinition: compiled.triggerDefinition,
    stepsDefinition: compiled.stepsDefinition,
  })
}

export function WorkflowDetailPage() {
  const { workflowId = '' } = useParams<{ workflowId: string }>()
  const queryClient = useQueryClient()
  const { notify } = useToast()
  const [selectedRunId, setSelectedRunId] = useState<string | null>(null)
  const [publishDialogOpen, setPublishDialogOpen] = useState(false)

  const detailQuery = useQuery({
    enabled: Boolean(workflowId),
    queryKey: queryKeys.workflows.detail(workflowId),
    queryFn: () => workflowsApi.get(workflowId),
  })

  const runsQuery = useQuery({
    enabled: Boolean(workflowId),
    queryKey: queryKeys.workflows.runs(workflowId),
    queryFn: () => workflowsApi.runs(workflowId),
  })

  const runDetailQuery = useQuery({
    enabled: Boolean(workflowId && selectedRunId),
    queryKey: queryKeys.workflows.runDetail(workflowId, selectedRunId ?? ''),
    queryFn: () => workflowsApi.run(workflowId, selectedRunId ?? ''),
  })

  const payload = detailQuery.data?.data as WorkflowDetailEnvelopeWithDraftValidation['data'] | undefined
  const draftVersion = useMemo(
    () => payload?.versions.find((version) => version.lifecycleState === 'draft') ?? payload?.versions[0],
    [payload],
  )

  const [name, setName] = useState('')
  const [description, setDescription] = useState('')
  const [builderState, setBuilderState] = useState<WorkflowBuilderState>({
    trigger: { event: '', subjectType: '', filters: [] },
    steps: [],
  })
  const [initialSnapshot, setInitialSnapshot] = useState('')

  useEffect(() => {
    if (!payload || !draftVersion) return

    const nextBuilderState = parseWorkflowContractToBuilderState(
      (draftVersion.triggerDefinition ?? {}) as Record<string, unknown>,
      (draftVersion.stepsDefinition ?? []) as Array<Record<string, unknown>>,
    )

    setName(payload.workflow.name)
    setDescription(payload.workflow.description ?? '')
    setBuilderState(nextBuilderState)
    setInitialSnapshot(
      buildSnapshot({
        name: payload.workflow.name,
        description: payload.workflow.description ?? '',
        builderState: nextBuilderState,
      }),
    )
  }, [draftVersion, payload])

  useEffect(() => {
    const firstRunId = runsQuery.data?.data.items?.[0]?.id
    if (!selectedRunId && firstRunId) setSelectedRunId(firstRunId)
  }, [runsQuery.data, selectedRunId])

  const clientSideErrors = useMemo(
    () => validateBuilderStateBeforeSave(builderState),
    [builderState],
  )
  const currentSnapshot = useMemo(
    () => buildSnapshot({ name, description, builderState }),
    [builderState, description, name],
  )
  const hasUnsavedChanges = Boolean(initialSnapshot) && currentSnapshot !== initialSnapshot

  const saveMutation = useMutation({
    mutationFn: async () => {
      const compiled = compileWorkflowBuilderToContract(builderState)
      return workflowsApi.updateDraft(workflowId, {
        name,
        description: description || null,
        triggerDefinition: compiled.triggerDefinition,
        stepsDefinition: compiled.stepsDefinition,
      })
    },
    onSuccess: async () => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.workflows.detail(workflowId) }),
        queryClient.invalidateQueries({ queryKey: queryKeys.workflows.all }),
      ])
      notify({
        title: 'Draft saved',
        description:
          'Your changes were saved to the editable draft. Published versions stay unchanged.',
        tone: 'success',
      })
    },
    onError: (error) =>
      notify({
        title: 'Could not save draft',
        description:
          error instanceof Error ? error.message : 'Check required workflow fields and try again.',
        tone: 'danger',
      }),
  })

  const publishMutation = useMutation({
    mutationFn: async () => workflowsApi.publish(workflowId),
    onSuccess: async () => {
      setPublishDialogOpen(false)
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.workflows.detail(workflowId) }),
        queryClient.invalidateQueries({ queryKey: queryKeys.workflows.runs(workflowId) }),
        queryClient.invalidateQueries({ queryKey: queryKeys.workflows.all }),
      ])
      notify({
        title: 'Workflow published',
        description:
          'Future executions now bind to the new immutable workflow version.',
        tone: 'success',
      })
    },
    onError: (error) =>
      notify({
        title: 'Workflow publish failed',
        description:
          error instanceof Error ? error.message : 'The workflow could not be published.',
        tone: 'danger',
      }),
  })

  if (detailQuery.isLoading) return <LoadingSkeleton lines={10} />
  if (!payload) {
    return (
      <EmptyState
        title="Workflow not found"
        description="The requested workflow could not be resolved for the active tenant."
      />
    )
  }

  const runs = runsQuery.data?.data.items ?? []
  const runDetail = runDetailQuery.data?.data
  const draftValidation = payload.draftValidation
  const publishBlocked =
    publishMutation.isPending ||
    (draftValidation?.hasDraft && !draftValidation.isValid) ||
    clientSideErrors.length > 0

  return (
    <PageCanvas density="compact">
      <PageHeader
        variant="governance"
        eyebrow="Governance"
        title={payload.workflow.name}
        description="Governed workflow detail with draft editing, publish validation, and runtime evidence."
        statusSummary={
          <>
            <WorkflowStatusBadge status={payload.workflow.status} />
            {draftVersion?.lifecycleState === 'draft' ? (
              <AppBadge variant="info">Editable draft</AppBadge>
            ) : (
              <AppBadge variant="warning">Revision will be cloned</AppBadge>
            )}
          </>
        }
        secondaryActions={
          <Link to="/app/workflows">
            <AppButton type="button" variant="secondary">
              Back to workflows
            </AppButton>
          </Link>
        }
        actions={
          <AppButton
            type="button"
            onClick={() => setPublishDialogOpen(true)}
            disabled={publishBlocked}
          >
            {publishMutation.isPending ? 'Publishing…' : 'Publish version'}
          </AppButton>
        }
      />

      <PageSplit variant="governance">
        <div className="space-y-4">
          <AppCard>
            <AppCardHeader density="compact">
              <div className="heading-md">Workflow draft</div>
              <div className="body-sm text-text-muted">
                Primary authoring surface for the current draft definition.
              </div>
            </AppCardHeader>
            <AppCardBody density="compact" className="space-y-4">
              <div className="space-y-2">
                <label className="label-sm text-text">Name</label>
                <AppInput value={name} onChange={(event) => setName(event.currentTarget.value)} />
              </div>
              <div className="space-y-2">
                <label className="label-sm text-text">Description</label>
                <AppTextarea
                  value={description}
                  onChange={(event) => setDescription(event.currentTarget.value)}
                />
              </div>
            </AppCardBody>
          </AppCard>

          <WorkflowTriggerBuilder
            value={builderState.trigger}
            onChange={(trigger) => setBuilderState((current) => ({ ...current, trigger }))}
          />

          <WorkflowStepList
            steps={builderState.steps}
            onChange={(steps) => setBuilderState((current) => ({ ...current, steps }))}
          />

          <AppCard tone="secondary">
            <AppCardHeader density="compact">
              <div className="flex flex-wrap items-center justify-between gap-3">
                <div>
                  <div className="heading-md">Publish check</div>
                  <div className="body-sm text-text-muted">
                    This combines local draft guidance with server-side runtime validation.
                  </div>
                </div>
                {draftValidation?.hasDraft ? (
                  draftValidation.isValid && clientSideErrors.length === 0 ? (
                    <AppBadge variant="success">Ready to publish</AppBadge>
                  ) : (
                    <AppBadge variant="danger">Needs fixes</AppBadge>
                  )
                ) : (
                  <AppBadge variant="neutral">No draft</AppBadge>
                )}
              </div>
            </AppCardHeader>
            <AppCardBody density="compact" className="space-y-3">
              {clientSideErrors.length ? (
                <div className="space-y-3">
                  {clientSideErrors.map((issue) => (
                    <div
                      key={issue}
                      className="rounded-xl border border-warning/30 bg-warning/5 p-4"
                    >
                      <div className="body-sm text-text">{issue}</div>
                    </div>
                  ))}
                </div>
              ) : null}

              {!draftValidation?.hasDraft ? (
                <EmptyState
                  title="No draft to validate"
                  description="Create or clone a draft revision to see publish-time validation results."
                />
              ) : null}

              {draftValidation?.hasDraft && draftValidation.isValid && clientSideErrors.length === 0 ? (
                <div className="rounded-xl border border-border bg-surface px-4 py-3">
                  <div className="font-medium text-text">This draft is ready to publish.</div>
                  <div className="body-sm mt-1 text-text-muted">
                    The current trigger and step definitions match the supported runtime contract.
                  </div>
                </div>
              ) : null}

              {draftValidation?.hasDraft && !draftValidation.isValid ? (
                <div className="space-y-3">
                  {draftValidation.errors.map((issue) => (
                    <div
                      key={`${issue.path}-${issue.code}`}
                      className="rounded-xl border border-danger/30 bg-danger/5 p-4"
                    >
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

          <AppCard tone="secondary">
            <AppCardHeader density="compact">
              <div className="heading-md">Run monitoring</div>
              <div className="body-sm text-text-muted">
                Published versions keep durable run logs and version binding for every queued execution.
              </div>
            </AppCardHeader>
            <AppCardBody density="compact">
              {runsQuery.isLoading ? <LoadingSkeleton lines={6} /> : null}
              {!runsQuery.isLoading && runs.length === 0 ? (
                <EmptyState
                  title="No workflow runs yet"
                  description="Matching domain events will queue runs here after the workflow is published."
                />
              ) : null}
              <div className="grid gap-4 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
                <div className="space-y-3">
                  {runs.map((run) => (
                    <button
                      key={run.id}
                      type="button"
                      className={`w-full rounded-xl border px-4 py-3 text-left ${
                        selectedRunId === run.id
                          ? 'border-primary bg-surface'
                          : 'border-border bg-muted/20'
                      }`}
                      onClick={() => setSelectedRunId(run.id)}
                    >
                      <div className="flex flex-wrap items-center justify-between gap-3">
                        <div>
                          <div className="font-medium text-text">{run.triggerEvent}</div>
                          <div className="text-xs text-text-muted">
                            Subject {run.subjectType} #{run.subjectId}
                          </div>
                        </div>
                        <WorkflowStatusBadge status={run.status} />
                      </div>
                      <div className="mt-2 text-xs text-text-muted">
                        Version {run.workflowVersionId} • queued{' '}
                        {run.queuedAt ? new Date(run.queuedAt).toLocaleString() : '—'}
                      </div>
                    </button>
                  ))}
                </div>
                <div>
                  {runDetailQuery.isLoading ? <LoadingSkeleton lines={5} /> : null}
                  {!runDetail && !runDetailQuery.isLoading ? (
                    <EmptyState
                      title="Select a run"
                      description="Choose a queued or completed run to inspect execution logs."
                    />
                  ) : null}
                  {runDetail ? (
                    <div className="space-y-3">
                      <div className="rounded-xl border border-border bg-surface px-4 py-3">
                        <div className="flex flex-wrap items-center justify-between gap-3">
                          <div>
                            <div className="font-medium text-text">Run {runDetail.run.id}</div>
                            <div className="text-xs text-text-muted">
                              Workflow version {runDetail.run.workflowVersionId}
                            </div>
                          </div>
                          <WorkflowStatusBadge status={runDetail.run.status} />
                        </div>
                        <div className="mt-2 text-xs text-text-muted">
                          Correlation {runDetail.run.correlationId || 'none'} • failure{' '}
                          {runDetail.run.failedAt
                            ? new Date(runDetail.run.failedAt).toLocaleString()
                            : 'not failed'}
                        </div>
                      </div>
                      {runDetail.logs.map((log) => (
                        <div
                          key={log.id}
                          className="rounded-xl border border-border bg-surface px-4 py-3"
                        >
                          <div className="flex flex-wrap items-center justify-between gap-3">
                            <div className="font-medium text-text">{log.logType}</div>
                            <div className="text-xs text-text-muted">
                              step {log.stepIndex ?? '—'}
                            </div>
                          </div>
                          <div className="body-sm mt-2 text-text-muted">{log.message}</div>
                          <div className="mt-2 text-xs text-text-muted">
                            {log.occurredAt ? new Date(log.occurredAt).toLocaleString() : '—'}
                          </div>
                          <pre className="mt-3 overflow-x-auto rounded-lg bg-muted/20 p-3 text-xs text-text-muted">
                            {pretty(log.payloadSnapshot)}
                          </pre>
                        </div>
                      ))}
                    </div>
                  ) : null}
                </div>
              </div>
            </AppCardBody>
          </AppCard>

          {hasUnsavedChanges ? (
            <WorkflowUnsavedChangesBar
              onReset={() => {
                if (!payload || !draftVersion) return
                const nextBuilderState = parseWorkflowContractToBuilderState(
                  (draftVersion.triggerDefinition ?? {}) as Record<string, unknown>,
                  (draftVersion.stepsDefinition ?? []) as Array<Record<string, unknown>>,
                )
                setName(payload.workflow.name)
                setDescription(payload.workflow.description ?? '')
                setBuilderState(nextBuilderState)
              }}
              onSave={() => saveMutation.mutate()}
              saveDisabled={saveMutation.isPending || clientSideErrors.length > 0}
              saving={saveMutation.isPending}
            />
          ) : null}
        </div>

        <div className="space-y-4">
          <AppCard tone="inset">
            <AppCardHeader density="compact">
              <div className="heading-md">Publish summary</div>
            </AppCardHeader>
            <AppCardBody density="compact" className="space-y-2 body-sm text-text-muted">
              <p>
                Event: <span className="font-medium text-text">{builderState.trigger.event || '—'}</span>
              </p>
              <p>
                Subject type:{' '}
                <span className="font-medium text-text">{builderState.trigger.subjectType || '—'}</span>
              </p>
              <p>
                Trigger filters:{' '}
                <span className="font-medium text-text">{builderState.trigger.filters.length}</span>
              </p>
              <p>
                Workflow steps:{' '}
                <span className="font-medium text-text">{builderState.steps.length}</span>
              </p>
              <p>
                Latest published version:{' '}
                <span className="font-medium text-text">
                  v{payload.workflow.latestPublishedVersionNumber ?? '—'}
                </span>
              </p>
            </AppCardBody>
          </AppCard>

          <AppCard tone="secondary">
            <AppCardHeader density="compact">
              <div className="heading-md">Version history</div>
              <div className="body-sm text-text-muted">
                Every published version remains immutable and reviewable.
              </div>
            </AppCardHeader>
            <AppCardBody density="compact">
              <div className="space-y-3">
                {payload.versions.map((version) => (
                  <div
                    key={version.id}
                    className="rounded-xl border border-border bg-surface px-4 py-3"
                  >
                    <div className="flex flex-wrap items-center justify-between gap-3">
                      <div>
                        <div className="font-medium text-text">Version {version.versionNumber}</div>
                        <div className="text-xs text-text-muted">
                          checksum {version.checksum.slice(0, 12)}…
                        </div>
                      </div>
                      <WorkflowStatusBadge status={version.lifecycleState} />
                    </div>
                    <div className="mt-2 text-xs text-text-muted">
                      Published:{' '}
                      {version.publishedAt
                        ? new Date(version.publishedAt).toLocaleString()
                        : 'Not published'}{' '}
                      • by {version.publishedBy ?? '—'}
                    </div>
                    <pre className="mt-3 overflow-x-auto rounded-lg bg-muted/20 p-3 text-xs text-text-muted">
                      {pretty({
                        triggerDefinition: version.triggerDefinition,
                        stepsDefinition: version.stepsDefinition,
                      })}
                    </pre>
                  </div>
                ))}
              </div>
            </AppCardBody>
          </AppCard>
        </div>
      </PageSplit>

      <AppDialog open={publishDialogOpen} onOpenChange={setPublishDialogOpen}>
        <AppDialogContent>
          <div className="space-y-4">
            <div>
              <div className="heading-md">Publish workflow version?</div>
              <div className="body-sm mt-1 text-text-muted">
                Publishing freezes this draft into an immutable version. New runs will use the published version.
              </div>
            </div>
            <div className="flex justify-end gap-2">
              <AppButton
                type="button"
                variant="secondary"
                onClick={() => setPublishDialogOpen(false)}
              >
                Cancel
              </AppButton>
              <AppButton
                type="button"
                onClick={() => publishMutation.mutate()}
                disabled={publishBlocked}
              >
                {publishMutation.isPending ? 'Publishing…' : 'Publish version'}
              </AppButton>
            </div>
          </div>
        </AppDialogContent>
      </AppDialog>
    </PageCanvas>
  )
}
