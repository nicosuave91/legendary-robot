import { useEffect, useMemo, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { Link, useParams } from 'react-router-dom'
import { AppBadge, AppButton, AppCard, AppCardBody, AppCardHeader, AppInput, AppSelect, AppTextarea, EmptyState, LoadingSkeleton, PageHeader } from '@/components/ui'
import { rulesApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import { RuleStatusBadge } from '@/features/rules-library/components/rule-status-badge'
import { useToast } from '@/components/shell/toast-host'

function pretty(value: unknown) {
  return JSON.stringify(value ?? {}, null, 2)
}

export function RuleDetailPage() {
  const { ruleId = '' } = useParams<{ ruleId: string }>()
  const queryClient = useQueryClient()
  const { notify } = useToast()
  const detailQuery = useQuery({
    enabled: Boolean(ruleId),
    queryKey: queryKeys.rules.detail(ruleId),
    queryFn: () => rulesApi.get(ruleId)
  })
  const logsQuery = useQuery({
    enabled: Boolean(ruleId),
    queryKey: queryKeys.rules.executionLogs(ruleId),
    queryFn: () => rulesApi.executionLogs(ruleId)
  })

  const payload = detailQuery.data?.data
  const draftVersion = useMemo(() => payload?.versions.find((version) => version.lifecycleState === 'draft') ?? payload?.versions[0], [payload])
  const [form, setForm] = useState({
    name: '',
    description: '',
    moduleScope: 'applications',
    subjectType: 'application',
    triggerEvent: 'application.created',
    severity: 'warning',
    executionLabel: '',
    noteTemplate: '',
    conditionDefinition: '{}',
    actionDefinition: '{}'
  })

  useEffect(() => {
    if (!payload || !draftVersion) return
    setForm({
      name: payload.rule.name,
      description: payload.rule.description ?? '',
      moduleScope: payload.rule.moduleScope,
      subjectType: payload.rule.subjectType,
      triggerEvent: draftVersion.triggerEvent,
      severity: draftVersion.severity,
      executionLabel: draftVersion.executionLabel ?? '',
      noteTemplate: draftVersion.noteTemplate ?? '',
      conditionDefinition: pretty(draftVersion.conditionDefinition),
      actionDefinition: pretty(draftVersion.actionDefinition)
    })
  }, [draftVersion, payload])

  const saveMutation = useMutation({
    mutationFn: async () => rulesApi.updateDraft(ruleId, {
      name: form.name,
      description: form.description || null,
      moduleScope: form.moduleScope,
      subjectType: form.subjectType,
      triggerEvent: form.triggerEvent,
      severity: form.severity,
      executionLabel: form.executionLabel || null,
      noteTemplate: form.noteTemplate || null,
      conditionDefinition: JSON.parse(form.conditionDefinition),
      actionDefinition: JSON.parse(form.actionDefinition)
    }),
    onSuccess: async () => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.rules.detail(ruleId) }),
        queryClient.invalidateQueries({ queryKey: queryKeys.rules.all })
      ])
      notify({ title: 'Draft saved', description: 'The mutable draft version remains separate from published rule history.', tone: 'success' })
    },
    onError: (error) => notify({ title: 'Draft save failed', description: error instanceof Error ? error.message : 'Unable to save the rule draft.', tone: 'danger' })
  })

  const publishMutation = useMutation({
    mutationFn: async () => rulesApi.publish(ruleId),
    onSuccess: async () => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.rules.detail(ruleId) }),
        queryClient.invalidateQueries({ queryKey: queryKeys.rules.executionLogs(ruleId) }),
        queryClient.invalidateQueries({ queryKey: queryKeys.rules.all })
      ])
      notify({ title: 'Rule published', description: 'The latest rule version is now immutable and execution-ready.', tone: 'success' })
    },
    onError: (error) => notify({ title: 'Publish failed', description: error instanceof Error ? error.message : 'The rule could not be published.', tone: 'danger' })
  })

  if (detailQuery.isLoading) return <LoadingSkeleton lines={10} />
  if (!payload) return <EmptyState title="Rule not found" description="The requested rule could not be resolved for the active tenant." />

  const executionLogs = logsQuery.data?.data.items ?? payload.executionLogs ?? []
  const latestPublished = payload.versions.find((version) => version.lifecycleState === 'published')

  return (
    <div className="space-y-6">
      <PageHeader
        title={payload.rule.name}
        description="Rules publish immutable versions. Applications now bind execution evidence to a published rule version instead of a provisional evaluator."
        actions={<><Link to="/app/rules"><AppButton type="button" variant="secondary">Back to rules</AppButton></Link><AppButton type="button" onClick={() => publishMutation.mutate()} disabled={publishMutation.isPending}>{publishMutation.isPending ? 'Publishing…' : 'Publish latest draft'}</AppButton></>}
      />

      <div className="grid gap-6 xl:grid-cols-[minmax(0,1.3fr)_420px]">
        <div className="space-y-6">
          <AppCard>
            <AppCardHeader>
              <div className="flex flex-wrap items-center justify-between gap-3">
                <div>
                  <div className="heading-md">Draft editor</div>
                  <div className="body-sm text-text-muted">Saving updates the current draft version or creates a new draft revision from the latest published version.</div>
                </div>
                <div className="flex items-center gap-2">
                  <RuleStatusBadge status={payload.rule.status} />
                  {draftVersion?.lifecycleState === 'draft' ? <AppBadge variant="info">Editable draft</AppBadge> : <AppBadge variant="warning">Revision will be cloned</AppBadge>}
                </div>
              </div>
            </AppCardHeader>
            <AppCardBody>
              <div className="space-y-4">
                <div className="space-y-2"><label className="label-sm text-text">Name</label><AppInput value={form.name} onChange={(event) => setForm((current) => ({ ...current, name: event.currentTarget.value }))} /></div>
                <div className="space-y-2"><label className="label-sm text-text">Description</label><AppTextarea value={form.description} onChange={(event) => setForm((current) => ({ ...current, description: event.currentTarget.value }))} /></div>
                <div className="grid gap-4 md:grid-cols-2">
                  <div className="space-y-2"><label className="label-sm text-text">Module scope</label><AppSelect value={form.moduleScope} onChange={(event) => setForm((current) => ({ ...current, moduleScope: event.currentTarget.value }))}><option value="applications">Applications</option><option value="disposition">Disposition</option><option value="communications">Communications</option><option value="client">Client</option></AppSelect></div>
                  <div className="space-y-2"><label className="label-sm text-text">Subject type</label><AppSelect value={form.subjectType} onChange={(event) => setForm((current) => ({ ...current, subjectType: event.currentTarget.value }))}><option value="application">Application</option><option value="client">Client</option><option value="communication">Communication</option></AppSelect></div>
                  <div className="space-y-2"><label className="label-sm text-text">Trigger event</label><AppInput value={form.triggerEvent} onChange={(event) => setForm((current) => ({ ...current, triggerEvent: event.currentTarget.value }))} /></div>
                  <div className="space-y-2"><label className="label-sm text-text">Severity</label><AppSelect value={form.severity} onChange={(event) => setForm((current) => ({ ...current, severity: event.currentTarget.value }))}><option value="info">Info</option><option value="warning">Warning</option><option value="blocking">Blocking</option></AppSelect></div>
                </div>
                <div className="space-y-2"><label className="label-sm text-text">Execution label</label><AppInput value={form.executionLabel} onChange={(event) => setForm((current) => ({ ...current, executionLabel: event.currentTarget.value }))} /></div>
                <div className="space-y-2"><label className="label-sm text-text">Note template</label><AppTextarea value={form.noteTemplate} onChange={(event) => setForm((current) => ({ ...current, noteTemplate: event.currentTarget.value }))} /></div>
                <div className="space-y-2"><label className="label-sm text-text">Condition definition JSON</label><AppTextarea className="min-h-[160px] font-mono text-xs" value={form.conditionDefinition} onChange={(event) => setForm((current) => ({ ...current, conditionDefinition: event.currentTarget.value }))} /></div>
                <div className="space-y-2"><label className="label-sm text-text">Action definition JSON</label><AppTextarea className="min-h-[160px] font-mono text-xs" value={form.actionDefinition} onChange={(event) => setForm((current) => ({ ...current, actionDefinition: event.currentTarget.value }))} /></div>
                <AppButton type="button" onClick={() => saveMutation.mutate()} disabled={saveMutation.isPending}>{saveMutation.isPending ? 'Saving…' : latestPublished && draftVersion?.lifecycleState !== 'draft' ? 'Create draft revision' : 'Save draft changes'}</AppButton>
              </div>
            </AppCardBody>
          </AppCard>

          <AppCard>
            <AppCardHeader>
              <div className="heading-md">Execution evidence</div>
              <div className="body-sm text-text-muted">Rule evaluations are append-only and bind to the immutable version that executed.</div>
            </AppCardHeader>
            <AppCardBody>
              {logsQuery.isLoading ? <LoadingSkeleton lines={5} /> : null}
              {!logsQuery.isLoading && executionLogs.length === 0 ? <EmptyState title="No execution evidence yet" description="Execution logs appear here after the rule evaluates against Applications or other governed events." /> : null}
              <div className="space-y-3">
                {executionLogs.map((log) => (
                  <div key={log.id} className="rounded-lg border border-border bg-muted p-4">
                    <div className="flex flex-wrap items-start justify-between gap-3">
                      <div>
                        <div className="font-medium text-text">{log.triggerEvent}</div>
                        <div className="text-xs text-text-muted">Subject {log.subjectType} #{log.subjectId} • version {log.ruleVersionId}</div>
                      </div>
                      <AppBadge variant={log.outcome === 'blocking' || log.outcome === 'error' ? 'danger' : log.outcome === 'applied' ? 'success' : 'info'}>{log.outcome}</AppBadge>
                    </div>
                    <div className="mt-2 text-xs text-text-muted">{log.executedAt ? new Date(log.executedAt).toLocaleString() : '—'} • correlation {log.correlationId || 'none'}</div>
                    <pre className="mt-3 overflow-x-auto rounded-md bg-surface p-3 text-xs text-text-muted">{pretty(log.contextSnapshot)}</pre>
                  </div>
                ))}
              </div>
            </AppCardBody>
          </AppCard>
        </div>

        <div className="space-y-6">
          <AppCard>
            <AppCardHeader>
              <div className="heading-md">Version history</div>
              <div className="body-sm text-text-muted">Published versions are locked. Future edits create new draft rows and preserve the immutable publication trail.</div>
            </AppCardHeader>
            <AppCardBody>
              <div className="space-y-3">
                {payload.versions.map((version) => (
                  <div key={version.id} className="rounded-lg border border-border bg-muted p-4">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                      <div>
                        <div className="font-medium text-text">Version {version.versionNumber}</div>
                        <div className="text-xs text-text-muted">{version.triggerEvent} • checksum {version.checksum.slice(0, 12)}…</div>
                      </div>
                      <RuleStatusBadge status={version.lifecycleState} />
                    </div>
                    <div className="mt-2 text-xs text-text-muted">Published: {version.publishedAt ? new Date(version.publishedAt).toLocaleString() : 'Not published'} • by {version.publishedBy ?? '—'}</div>
                    <pre className="mt-3 overflow-x-auto rounded-md bg-surface p-3 text-xs text-text-muted">{pretty({ conditionDefinition: version.conditionDefinition, actionDefinition: version.actionDefinition })}</pre>
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
              <div className="body-sm text-text-muted space-y-2">
                <p>Rule key: <span className="font-medium text-text">{payload.rule.ruleKey}</span></p>
                <p>Current status: <span className="font-medium text-text">{payload.rule.status}</span></p>
                <p>Latest published version: <span className="font-medium text-text">v{payload.rule.latestPublishedVersionNumber ?? '—'}</span></p>
                <p>Current draft version: <span className="font-medium text-text">v{payload.rule.currentDraftVersionNumber ?? '—'}</span></p>
              </div>
            </AppCardBody>
          </AppCard>
        </div>
      </div>
    </div>
  )
}
