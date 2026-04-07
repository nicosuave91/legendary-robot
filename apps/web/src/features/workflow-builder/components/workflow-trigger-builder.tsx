import { AppButton, AppCard, AppCardBody, AppCardHeader, AppInput, AppSelect } from '@/components/ui'
import { WORKFLOW_OPERATOR_OPTIONS, createWorkflowFilter } from '@/features/workflow-builder/workflow-builder-utils'
import type { WorkflowTriggerBuilderState } from '@/features/workflow-builder/workflow-builder-types'

type Props = {
  value: WorkflowTriggerBuilderState
  onChange: (value: WorkflowTriggerBuilderState) => void
}

export function WorkflowTriggerBuilder({ value, onChange }: Props) {
  return (
    <AppCard>
      <AppCardHeader>
        <div className="heading-md">Start condition</div>
        <div className="body-sm text-text-muted">Choose what should start this workflow and narrow it with optional filters.</div>
      </AppCardHeader>
      <AppCardBody className="space-y-4">
        <div className="grid gap-4 lg:grid-cols-2">
          <div className="space-y-2">
            <label className="label-sm text-text">Event name</label>
            <AppInput
              value={value.event}
              onChange={(event) => onChange({ ...value, event: event.currentTarget.value })}
              placeholder="application.created"
            />
            <div className="text-xs text-text-muted">Use the event name produced by the current workflow or application lifecycle.</div>
          </div>
          <div className="space-y-2">
            <label className="label-sm text-text">Subject type</label>
            <AppInput
              value={value.subjectType}
              onChange={(event) => onChange({ ...value, subjectType: event.currentTarget.value })}
              placeholder="application"
            />
            <div className="text-xs text-text-muted">Subject type should match the record this workflow runs against.</div>
          </div>
        </div>

        <div className="space-y-3">
          <div className="flex items-center justify-between gap-3">
            <div>
              <div className="font-medium text-text">Trigger filters</div>
              <div className="text-xs text-text-muted">Add optional fact rules before the workflow starts.</div>
            </div>
            <AppButton
              type="button"
              variant="secondary"
              onClick={() => onChange({ ...value, filters: [...value.filters, createWorkflowFilter()] })}
            >
              Add trigger filter
            </AppButton>
          </div>

          {!value.filters.length ? (
            <div className="rounded-lg border border-dashed border-border bg-surface p-4 text-sm text-text-muted">
              No trigger filters yet. This workflow will respond to every matching event and subject type.
            </div>
          ) : null}

          {value.filters.map((filter) => (
            <div key={filter.id} className="grid gap-3 rounded-lg border border-border bg-muted p-4 lg:grid-cols-[minmax(0,1fr)_180px_minmax(0,1fr)_auto] lg:items-end">
              <div className="space-y-2">
                <label className="label-sm text-text">Fact</label>
                <AppInput
                  value={filter.fact}
                  onChange={(event) => onChange({
                    ...value,
                    filters: value.filters.map((item) => item.id === filter.id ? { ...item, fact: event.currentTarget.value } : item)
                  })}
                  placeholder="currentStatus"
                />
              </div>
              <div className="space-y-2">
                <label className="label-sm text-text">Operator</label>
                <AppSelect
                  value={filter.operator}
                  onChange={(event) => onChange({
                    ...value,
                    filters: value.filters.map((item) => item.id === filter.id ? { ...item, operator: event.currentTarget.value as typeof filter.operator } : item)
                  })}
                >
                  {WORKFLOW_OPERATOR_OPTIONS.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}
                </AppSelect>
              </div>
              <div className="space-y-2">
                <label className="label-sm text-text">Value</label>
                <AppInput
                  value={filter.value}
                  disabled={filter.operator === 'exists'}
                  onChange={(event) => onChange({
                    ...value,
                    filters: value.filters.map((item) => item.id === filter.id ? { ...item, value: event.currentTarget.value } : item)
                  })}
                  placeholder={filter.operator === 'exists' ? 'Not required for exists' : 'submitted'}
                />
              </div>
              <AppButton
                type="button"
                variant="ghost"
                onClick={() => onChange({ ...value, filters: value.filters.filter((item) => item.id !== filter.id) })}
              >
                Remove
              </AppButton>
            </div>
          ))}
        </div>
      </AppCardBody>
    </AppCard>
  )
}
