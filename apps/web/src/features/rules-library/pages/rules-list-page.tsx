import { useMemo, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { Link, useNavigate } from 'react-router-dom'
import {
  AppBadge,
  AppButton,
  AppCard,
  AppCardBody,
  AppCardHeader,
  AppInput,
  AppSelect,
  AppTextarea,
  EmptyState,
  LoadingSkeleton,
  PageCanvas,
  PageHeader,
  PageSplit,
} from '@/components/ui'
import { RuleStatusBadge } from '@/features/rules-library/components/rule-status-badge'
import { rulesApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import { useToast } from '@/components/shell/toast-host'

const defaultConditionDefinition = JSON.stringify(
  { all: [{ fact: 'amountRequested', operator: 'gte', value: 250000 }] },
  null,
  2,
)
const defaultActionDefinition = JSON.stringify(
  {
    type: 'application_note',
    outcome: 'warning',
    title: 'High-value review note',
    bodyTemplate:
      'This application matched a governed review rule and should receive additional human review.',
  },
  null,
  2,
)

export function RulesListPage() {
  const queryClient = useQueryClient()
  const navigate = useNavigate()
  const { notify } = useToast()
  const [statusFilter, setStatusFilter] = useState('')
  const [moduleScopeFilter, setModuleScopeFilter] = useState('')
  const [form, setForm] = useState({
    ruleKey: '',
    name: '',
    description: '',
    moduleScope: 'applications',
    subjectType: 'application',
    triggerEvent: 'application.created',
    severity: 'warning',
    conditionDefinition: defaultConditionDefinition,
    actionDefinition: defaultActionDefinition,
    executionLabel: 'Governed application rule',
    noteTemplate: 'A governed rule matched this application.',
  })

  const filters = useMemo(
    () => ({
      ...(statusFilter ? { status: statusFilter } : {}),
      ...(moduleScopeFilter ? { moduleScope: moduleScopeFilter } : {}),
    }),
    [moduleScopeFilter, statusFilter],
  )

  const listQuery = useQuery({
    queryKey: queryKeys.rules.list(filters),
    queryFn: () => rulesApi.list(filters),
  })

  const createMutation = useMutation({
    mutationFn: async () =>
      rulesApi.create({
        ruleKey: form.ruleKey,
        name: form.name,
        description: form.description || null,
        moduleScope: form.moduleScope,
        subjectType: form.subjectType,
        triggerEvent: form.triggerEvent,
        severity: form.severity,
        conditionDefinition: JSON.parse(form.conditionDefinition),
        actionDefinition: JSON.parse(form.actionDefinition),
        executionLabel: form.executionLabel || null,
        noteTemplate: form.noteTemplate || null,
      }),
    onSuccess: async (response) => {
      const ruleId = response.data.rule.id
      await queryClient.invalidateQueries({ queryKey: queryKeys.rules.all })
      notify({
        title: 'Draft rule created',
        description:
          'The rule catalog now includes an editable draft version.',
        tone: 'success',
      })
      navigate(`/app/rules/${ruleId}`)
    },
    onError: (error) => {
      notify({
        title: 'Rule draft failed',
        description:
          error instanceof Error ? error.message : 'The draft could not be created.',
        tone: 'danger',
      })
    },
  })

  const items = listQuery.data?.data.items ?? []

  return (
    <PageCanvas density="compact">
      <PageHeader
        variant="governance"
        eyebrow="Governance"
        title="Rules Library"
        description="Catalog, author, and publish immutable rule behavior for applications and other governed domains."
        statusSummary={
          <>
            <AppBadge variant="neutral">{items.length} rules</AppBadge>
            {statusFilter ? <AppBadge variant="info">Status: {statusFilter}</AppBadge> : null}
          </>
        }
      />

      <PageSplit variant="governance">
        <AppCard>
          <AppCardHeader density="compact">
            <div className="flex flex-wrap items-center justify-between gap-3">
              <div>
                <div className="heading-md">Rule catalog</div>
                <div className="body-sm text-text-muted">
                  Primary governance surface for reviewing lifecycle, scope, and publication state.
                </div>
              </div>
              <div className="grid gap-2 sm:grid-cols-2">
                <AppSelect value={statusFilter} onChange={(event) => setStatusFilter(event.currentTarget.value)}>
                  <option value="">All statuses</option>
                  <option value="draft">Draft</option>
                  <option value="published">Published</option>
                  <option value="retired">Retired</option>
                </AppSelect>
                <AppSelect value={moduleScopeFilter} onChange={(event) => setModuleScopeFilter(event.currentTarget.value)}>
                  <option value="">All modules</option>
                  <option value="applications">Applications</option>
                  <option value="disposition">Disposition</option>
                  <option value="communications">Communications</option>
                  <option value="client">Client</option>
                </AppSelect>
              </div>
            </div>
          </AppCardHeader>
          <AppCardBody density="compact">
            {listQuery.isLoading ? <LoadingSkeleton lines={8} /> : null}
            {!listQuery.isLoading && items.length === 0 ? (
              <EmptyState
                title="No rules yet"
                description="Create the first governed draft rule to establish an immutable publication history."
              />
            ) : null}
            <div className="space-y-3">
              {items.map((item) => (
                <Link
                  key={item.id}
                  to={`/app/rules/${item.id}`}
                  className="block rounded-xl border border-border bg-muted/20 px-4 py-3 transition hover:border-primary/40 hover:bg-surface"
                >
                  <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                      <div className="heading-md text-text">{item.name}</div>
                      <div className="body-sm text-text-muted">
                        {item.ruleKey} • {item.moduleScope} • latest published v
                        {item.latestPublishedVersionNumber ?? '—'}
                      </div>
                    </div>
                    <RuleStatusBadge status={item.status} />
                  </div>
                  <div className="body-sm mt-2 text-text-muted">
                    {item.description || 'No description captured yet.'}
                  </div>
                  <div className="mt-3 grid gap-3 text-xs text-text-muted sm:grid-cols-3">
                    <div>Draft version: v{item.currentDraftVersionNumber ?? '—'}</div>
                    <div>
                      Published at:{' '}
                      {item.latestPublishedAt
                        ? new Date(item.latestPublishedAt).toLocaleString()
                        : 'Not published'}
                    </div>
                    <div>
                      Updated:{' '}
                      {item.updatedAt ? new Date(item.updatedAt).toLocaleString() : '—'}
                    </div>
                  </div>
                </Link>
              ))}
            </div>
          </AppCardBody>
        </AppCard>

        <AppCard tone="secondary">
          <AppCardHeader density="compact">
            <div className="heading-md">Create draft rule</div>
            <div className="body-sm text-text-muted">
              Secondary authoring surface for creating a governed draft before publish.
            </div>
          </AppCardHeader>
          <AppCardBody density="compact" className="space-y-4">
            <div className="space-y-2">
              <label className="label-sm text-text">Rule key</label>
              <AppInput
                value={form.ruleKey}
                onChange={(event) =>
                  setForm((current) => ({ ...current, ruleKey: event.currentTarget.value }))
                }
                placeholder="app-high-value-review"
              />
            </div>
            <div className="space-y-2">
              <label className="label-sm text-text">Name</label>
              <AppInput
                value={form.name}
                onChange={(event) =>
                  setForm((current) => ({ ...current, name: event.currentTarget.value }))
                }
                placeholder="High-value application review"
              />
            </div>
            <div className="space-y-2">
              <label className="label-sm text-text">Description</label>
              <AppTextarea
                value={form.description}
                onChange={(event) =>
                  setForm((current) => ({ ...current, description: event.currentTarget.value }))
                }
                placeholder="Explain where this rule applies in the funnel."
              />
            </div>
            <div className="grid gap-3 md:grid-cols-2">
              <div className="space-y-2">
                <label className="label-sm text-text">Module scope</label>
                <AppSelect
                  value={form.moduleScope}
                  onChange={(event) =>
                    setForm((current) => ({ ...current, moduleScope: event.currentTarget.value }))
                  }
                >
                  <option value="applications">Applications</option>
                  <option value="disposition">Disposition</option>
                  <option value="communications">Communications</option>
                  <option value="client">Client</option>
                </AppSelect>
              </div>
              <div className="space-y-2">
                <label className="label-sm text-text">Subject type</label>
                <AppSelect
                  value={form.subjectType}
                  onChange={(event) =>
                    setForm((current) => ({ ...current, subjectType: event.currentTarget.value }))
                  }
                >
                  <option value="application">Application</option>
                  <option value="client">Client</option>
                  <option value="communication">Communication</option>
                </AppSelect>
              </div>
            </div>
            <div className="space-y-2">
              <label className="label-sm text-text">Trigger event</label>
              <AppInput
                value={form.triggerEvent}
                onChange={(event) =>
                  setForm((current) => ({ ...current, triggerEvent: event.currentTarget.value }))
                }
              />
            </div>
            <div className="space-y-2">
              <label className="label-sm text-text">Severity</label>
              <AppSelect
                value={form.severity}
                onChange={(event) =>
                  setForm((current) => ({ ...current, severity: event.currentTarget.value }))
                }
              >
                <option value="info">Info</option>
                <option value="warning">Warning</option>
                <option value="blocking">Blocking</option>
              </AppSelect>
            </div>
            <div className="space-y-2">
              <label className="label-sm text-text">Execution label</label>
              <AppInput
                value={form.executionLabel}
                onChange={(event) =>
                  setForm((current) => ({ ...current, executionLabel: event.currentTarget.value }))
                }
              />
            </div>
            <div className="space-y-2">
              <label className="label-sm text-text">Note template</label>
              <AppTextarea
                value={form.noteTemplate}
                onChange={(event) =>
                  setForm((current) => ({ ...current, noteTemplate: event.currentTarget.value }))
                }
              />
            </div>
            <div className="space-y-2">
              <label className="label-sm text-text">Condition definition JSON</label>
              <AppTextarea
                className="min-h-[132px] font-mono text-xs"
                value={form.conditionDefinition}
                onChange={(event) =>
                  setForm((current) => ({
                    ...current,
                    conditionDefinition: event.currentTarget.value,
                  }))
                }
              />
            </div>
            <div className="space-y-2">
              <label className="label-sm text-text">Action definition JSON</label>
              <AppTextarea
                className="min-h-[132px] font-mono text-xs"
                value={form.actionDefinition}
                onChange={(event) =>
                  setForm((current) => ({
                    ...current,
                    actionDefinition: event.currentTarget.value,
                  }))
                }
              />
            </div>
            <AppButton
              type="button"
              onClick={() => createMutation.mutate()}
              disabled={createMutation.isPending || !form.ruleKey.trim() || !form.name.trim()}
            >
              {createMutation.isPending ? 'Creating…' : 'Create governed draft'}
            </AppButton>
          </AppCardBody>
        </AppCard>
      </PageSplit>
    </PageCanvas>
  )
}
