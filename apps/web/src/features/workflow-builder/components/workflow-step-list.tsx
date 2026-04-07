import { AppBadge, AppButton, AppCard, AppCardBody, AppCardHeader, AppInput, AppSelect, AppTextarea } from '@/components/ui'
import {
  WORKFLOW_OPERATOR_OPTIONS,
  WORKFLOW_STEP_TYPE_LABELS,
  createWorkflowStep,
  summarizeWorkflowStep
} from '@/features/workflow-builder/workflow-builder-utils'
import type { WorkflowBuilderStepState, WorkflowBuilderStepType } from '@/features/workflow-builder/workflow-builder-types'

type Props = {
  steps: WorkflowBuilderStepState[]
  onChange: (steps: WorkflowBuilderStepState[]) => void
}

const ADD_STEP_ORDER: WorkflowBuilderStepType[] = ['condition', 'wait', 'create_client_note', 'send_sms', 'send_email']

export function WorkflowStepList({ steps, onChange }: Props) {
  const updateStep = (stepId: string, next: WorkflowBuilderStepState) => {
    onChange(steps.map((step) => step.id === stepId ? next : step))
  }

  const moveStep = (stepId: string, direction: -1 | 1) => {
    const index = steps.findIndex((step) => step.id === stepId)
    const nextIndex = index + direction

    if (index < 0 || nextIndex < 0 || nextIndex >= steps.length) return

    const nextSteps = [...steps]
    const [current] = nextSteps.splice(index, 1)
    nextSteps.splice(nextIndex, 0, current)
    onChange(nextSteps)
  }

  const duplicateStep = (stepId: string) => {
    const target = steps.find((step) => step.id === stepId)
    if (!target) return
    const duplicated = { ...createWorkflowStep(target.type), definition: { ...target.definition } } as WorkflowBuilderStepState
    const nextSteps: WorkflowBuilderStepState[] = []

    steps.forEach((step) => {
      nextSteps.push(step)
      if (step.id === stepId) nextSteps.push(duplicated)
    })

    onChange(nextSteps)
  }

  const removeStep = (stepId: string) => onChange(steps.filter((step) => step.id !== stepId))

  return (
    <AppCard>
      <AppCardHeader>
        <div className="flex flex-wrap items-center justify-between gap-3">
          <div>
            <div className="heading-md">Workflow steps</div>
            <div className="body-sm text-text-muted">Add the actions this workflow should perform in order.</div>
          </div>
          <div className="flex flex-wrap gap-2">
            {ADD_STEP_ORDER.map((type) => (
              <AppButton key={type} type="button" variant="secondary" onClick={() => onChange([...steps, createWorkflowStep(type)])}>
                Add {WORKFLOW_STEP_TYPE_LABELS[type]}
              </AppButton>
            ))}
          </div>
        </div>
      </AppCardHeader>
      <AppCardBody className="space-y-4">
        {!steps.length ? (
          <div className="rounded-lg border border-dashed border-border bg-surface p-4 text-sm text-text-muted">
            No workflow steps yet. Add at least one supported runtime action before publishing.
          </div>
        ) : null}

        {steps.map((step, index) => (
          <div key={step.id} className="rounded-lg border border-border bg-muted p-4">
            <div className="flex flex-wrap items-start justify-between gap-3">
              <div>
                <div className="flex flex-wrap items-center gap-2">
                  <AppBadge variant="info">Step {index + 1}</AppBadge>
                  <AppBadge variant="neutral">{WORKFLOW_STEP_TYPE_LABELS[step.type]}</AppBadge>
                </div>
                <div className="body-sm mt-2 text-text-muted">{summarizeWorkflowStep(step)}</div>
              </div>
              <div className="flex flex-wrap gap-2">
                <AppButton type="button" variant="ghost" onClick={() => moveStep(step.id, -1)} disabled={index === 0}>Up</AppButton>
                <AppButton type="button" variant="ghost" onClick={() => moveStep(step.id, 1)} disabled={index === steps.length - 1}>Down</AppButton>
                <AppButton type="button" variant="ghost" onClick={() => duplicateStep(step.id)}>Duplicate</AppButton>
                <AppButton type="button" variant="ghost" onClick={() => removeStep(step.id)}>Remove</AppButton>
              </div>
            </div>

            <div className="mt-4 grid gap-4">
              <div className="space-y-2">
                <label className="label-sm text-text">Step type</label>
                <AppSelect
                  value={step.type}
                  onChange={(event) => updateStep(step.id, createWorkflowStep(event.currentTarget.value as WorkflowBuilderStepType))}
                >
                  {ADD_STEP_ORDER.map((type) => <option key={type} value={type}>{WORKFLOW_STEP_TYPE_LABELS[type]}</option>)}
                </AppSelect>
              </div>

              {step.type === 'condition' ? (
                <div className="grid gap-4 lg:grid-cols-3">
                  <div className="space-y-2">
                    <label className="label-sm text-text">Fact</label>
                    <AppInput
                      value={step.definition.fact}
                      onChange={(event) => updateStep(step.id, { ...step, definition: { ...step.definition, fact: event.currentTarget.value } })}
                      placeholder="amountRequested"
                    />
                  </div>
                  <div className="space-y-2">
                    <label className="label-sm text-text">Operator</label>
                    <AppSelect
                      value={step.definition.operator}
                      onChange={(event) => updateStep(step.id, { ...step, definition: { ...step.definition, operator: event.currentTarget.value as typeof step.definition.operator } })}
                    >
                      {WORKFLOW_OPERATOR_OPTIONS.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}
                    </AppSelect>
                  </div>
                  <div className="space-y-2">
                    <label className="label-sm text-text">Value</label>
                    <AppInput
                      disabled={step.definition.operator === 'exists'}
                      value={step.definition.value}
                      onChange={(event) => updateStep(step.id, { ...step, definition: { ...step.definition, value: event.currentTarget.value } })}
                      placeholder={step.definition.operator === 'exists' ? 'Not required for exists' : '25000'}
                    />
                  </div>
                </div>
              ) : null}

              {step.type === 'wait' ? (
                <div className="space-y-2">
                  <label className="label-sm text-text">Duration in minutes</label>
                  <AppInput
                    type="number"
                    min="1"
                    value={step.definition.durationMinutes}
                    onChange={(event) => updateStep(step.id, { ...step, definition: { ...step.definition, durationMinutes: event.currentTarget.value } })}
                  />
                </div>
              ) : null}

              {step.type === 'create_client_note' ? (
                <div className="grid gap-4">
                  <div className="space-y-2">
                    <label className="label-sm text-text">Note title</label>
                    <AppInput
                      value={step.definition.title}
                      onChange={(event) => updateStep(step.id, { ...step, definition: { ...step.definition, title: event.currentTarget.value } })}
                      placeholder="Follow-up"
                    />
                  </div>
                  <div className="space-y-2">
                    <label className="label-sm text-text">Body template</label>
                    <AppTextarea
                      value={step.definition.bodyTemplate}
                      onChange={(event) => updateStep(step.id, { ...step, definition: { ...step.definition, bodyTemplate: event.currentTarget.value } })}
                      placeholder="This workflow matched a newly submitted application."
                    />
                  </div>
                </div>
              ) : null}

              {step.type === 'send_sms' ? (
                <div className="space-y-2">
                  <label className="label-sm text-text">SMS body template</label>
                  <AppTextarea
                    value={step.definition.bodyTemplate}
                    onChange={(event) => updateStep(step.id, { ...step, definition: { ...step.definition, bodyTemplate: event.currentTarget.value } })}
                    placeholder="Thanks for submitting your application."
                  />
                </div>
              ) : null}

              {step.type === 'send_email' ? (
                <div className="grid gap-4">
                  <div className="space-y-2">
                    <label className="label-sm text-text">Email subject template</label>
                    <AppInput
                      value={step.definition.subjectTemplate}
                      onChange={(event) => updateStep(step.id, { ...step, definition: { ...step.definition, subjectTemplate: event.currentTarget.value } })}
                      placeholder="Application received"
                    />
                  </div>
                  <div className="space-y-2">
                    <label className="label-sm text-text">Email body template</label>
                    <AppTextarea
                      value={step.definition.bodyTemplate}
                      onChange={(event) => updateStep(step.id, { ...step, definition: { ...step.definition, bodyTemplate: event.currentTarget.value } })}
                      placeholder="We have received your application."
                    />
                  </div>
                </div>
              ) : null}
            </div>
          </div>
        ))}
      </AppCardBody>
    </AppCard>
  )
}
