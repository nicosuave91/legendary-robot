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
  AppTextarea,
  EmptyState,
  LoadingSkeleton,
  PageCanvas,
  PageGrid,
  PageHeader,
} from '@/components/ui'
import { workflowsApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import { WorkflowStatusBadge } from '@/features/workflow-builder/components/workflow-status-badge'
import { WorkflowTemplatePicker } from '@/features/workflow-builder/components/workflow-template-picker'
import {
  WORKFLOW_TEMPLATE_PRESETS,
  compileWorkflowBuilderToContract,
  getWorkflowTemplatePreset,
} from '@/features/workflow-builder/workflow-builder-utils'
import { useToast } from '@/components/shell/toast-host'

export function WorkflowsListPage() {
  const queryClient = useQueryClient()
  const navigate = useNavigate()
  const { notify } = useToast()
  const [statusFilter, setStatusFilter] = useState('')
  const [selectedTemplateKey, setSelectedTemplateKey] = useState(
    WORKFLOW_TEMPLATE_PRESETS[0].key,
  )
  const [form, setForm] = useState({
    workflowKey: '',
    name: '',
    description: '',
  })

  const filters = useMemo(
    () => (statusFilter ? { status: statusFilter } : {}),
    [statusFilter],
  )
  const listQuery = useQuery({
    queryKey: queryKeys.workflows.list(filters),
    queryFn: () => workflowsApi.list(filters),
  })

  const createMutation = useMutation({
    mutationFn: async () => {
      const preset = getWorkflowTemplatePreset(selectedTemplateKey)
      const compiled = compileWorkflowBuilderToContract(preset.builderState)

      return workflowsApi.create({
        workflowKey: form.workflowKey.trim(),
        name: form.name.trim(),
        description: form.description.trim() || null,
        triggerDefinition: compiled.triggerDefinition,
        stepsDefinition: compiled.stepsDefinition,
      })
    },
    onSuccess: async (response) => {
      await queryClient.invalidateQueries({ queryKey: queryKeys.workflows.all })
      notify({
        title: 'Draft workflow created',
        description:
          'The workflow was created from a guided starter and remains editable until publish.',
        tone: 'success',
      })
      navigate(`/app/workflows/${response.data.workflow.id}`)
    },
    onError: (error) =>
      notify({
        title: 'Workflow creation failed',
        description:
          error instanceof Error
            ? error.message
            : 'The draft workflow could not be created.',
        tone: 'danger',
      }),
  })

  const items = listQuery.data?.data.items ?? []
  const selectedTemplate = getWorkflowTemplatePreset(selectedTemplateKey)

  return (
    <PageCanvas>
      <PageHeader
        variant="governance"
        eyebrow="Rules & workflow governance"
        title="Workflow Builder"
        description="Create an operational sequence from a guided starter, refine it in draft, and publish an immutable runtime version."
        status={
          <AppBadge variant="neutral">Status {statusFilter || 'all'}</AppBadge>
        }
        filters={
          <AppInput
            value={statusFilter}
            onChange={(event) => setStatusFilter(event.currentTarget.value)}
            placeholder="Filter by status"
          />
        }
      />

      <PageGrid layout="governance">
        <AppCard>
          <AppCardHeader>
            <div className="heading-md">Workflow catalog</div>
            <div className="body-sm text-text-muted">
              Monitor draft and published workflows before drilling into version history and run evidence.
            </div>
          </AppCardHeader>
          <AppCardBody>
            {listQuery.isLoading ? <LoadingSkeleton lines={8} /> : null}
            {!listQuery.isLoading && items.length === 0 ? (
              <EmptyState
                compact
                title="No workflows created yet"
                description="Start with a blank workflow or choose a guided starter."
              />
            ) : null}
            <div className="space-y-3">
              {items.map((item) => (
                <Link
                  key={item.id}
                  to={`/app/workflows/${item.id}`}
                  className="block rounded-xl border border-border bg-muted/35 p-4 transition hover:border-primary/40 hover:bg-surface"
                >
                  <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                      <div className="heading-md text-text">{item.name}</div>
                      <div className="body-sm text-text-muted">
                        {item.workflowKey} • trigger {item.triggerSummary}
                      </div>
                    </div>
                    <WorkflowStatusBadge status={item.status} />
                  </div>
                  <div className="body-sm mt-2 text-text-muted">
                    {item.description || 'No description captured yet.'}
                  </div>
                  <div className="mt-3 grid gap-3 text-xs text-text-muted sm:grid-cols-3">
                    <div>Draft version: v{item.currentDraftVersionNumber ?? '—'}</div>
                    <div>Published version: v{item.latestPublishedVersionNumber ?? '—'}</div>
                    <div>
                      Updated:{' '}
                      {item.updatedAt
                        ? new Date(item.updatedAt).toLocaleString()
                        : '—'}
                    </div>
                  </div>
                </Link>
              ))}
            </div>
          </AppCardBody>
        </AppCard>

        <AppCard>
          <AppCardHeader>
            <div className="heading-md">Create workflow draft</div>
            <div className="body-sm text-text-muted">
              Use a guided starter so the first draft is valid and readable without editing raw runtime structures.
            </div>
          </AppCardHeader>
          <AppCardBody className="space-y-5">
            <WorkflowTemplatePicker
              presets={WORKFLOW_TEMPLATE_PRESETS}
              selectedKey={selectedTemplateKey}
              onSelect={setSelectedTemplateKey}
            />

            <div className="rounded-xl border border-border bg-muted/35 p-4">
              <div className="label-sm uppercase tracking-[0.12em] text-text-muted">
                Selected starter
              </div>
              <div className="mt-2 font-medium text-text">{selectedTemplate.name}</div>
              <div className="body-sm mt-1 text-text-muted">
                {selectedTemplate.builderState.steps.length} step
                {selectedTemplate.builderState.steps.length === 1 ? '' : 's'} •{' '}
                {selectedTemplate.workflowKeyHint}
              </div>
            </div>

            <div className="space-y-2">
              <label className="label-sm text-text">Workflow key</label>
              <AppInput
                value={form.workflowKey}
                onChange={(event) =>
                  setForm((current) => ({
                    ...current,
                    workflowKey: event.currentTarget.value,
                  }))
                }
                placeholder={selectedTemplate.workflowKeyHint}
              />
            </div>

            <div className="space-y-2">
              <label className="label-sm text-text">Workflow name</label>
              <AppInput
                value={form.name}
                onChange={(event) =>
                  setForm((current) => ({
                    ...current,
                    name: event.currentTarget.value,
                  }))
                }
                placeholder="Submitted application follow-up"
              />
            </div>

            <div className="space-y-2">
              <label className="label-sm text-text">What does this workflow do?</label>
              <AppTextarea
                value={form.description}
                onChange={(event) =>
                  setForm((current) => ({
                    ...current,
                    description: event.currentTarget.value,
                  }))
                }
                placeholder="Describe when this workflow should run and what it does."
              />
            </div>

            <AppButton
              type="button"
              onClick={() => createMutation.mutate()}
              disabled={
                createMutation.isPending ||
                !form.workflowKey.trim() ||
                !form.name.trim()
              }
            >
              {createMutation.isPending ? 'Creating…' : 'Create workflow draft'}
            </AppButton>
          </AppCardBody>
        </AppCard>
      </PageGrid>
    </PageCanvas>
  )
}
