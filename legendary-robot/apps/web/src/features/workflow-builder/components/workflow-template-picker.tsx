import { AppBadge, AppButton } from '@/components/ui'
import { cn } from '@/lib/utils/cn'
import type { WorkflowTemplatePreset } from '@/features/workflow-builder/workflow-builder-types'

type Props = {
  presets: WorkflowTemplatePreset[]
  selectedKey: string
  onSelect: (key: string) => void
}

export function WorkflowTemplatePicker({ presets, selectedKey, onSelect }: Props) {
  return (
    <div className="space-y-3">
      <div className="grid gap-3">
        {presets.map((preset) => {
          const selected = preset.key === selectedKey
          return (
            <button
              key={preset.key}
              type="button"
              onClick={() => onSelect(preset.key)}
              className={cn(
                'rounded-lg border p-4 text-left transition',
                selected ? 'border-primary bg-surface' : 'border-border bg-muted hover:border-primary/40'
              )}
            >
              <div className="flex items-start justify-between gap-3">
                <div>
                  <div className="font-medium text-text">{preset.name}</div>
                  <div className="body-sm mt-1 text-text-muted">{preset.description}</div>
                </div>
                {selected ? <AppBadge variant="info">Selected</AppBadge> : null}
              </div>
              <div className="mt-3 flex flex-wrap gap-2">
                {preset.builderState.steps.map((step) => (
                  <AppBadge key={step.id} variant="neutral">{step.type.replaceAll('_', ' ')}</AppBadge>
                ))}
              </div>
            </button>
          )
        })}
      </div>
      <div className="body-sm text-text-muted">
        Start from a supported draft shape. You can adjust the trigger and steps after creation without editing JSON.
      </div>
      <AppButton type="button" variant="secondary" onClick={() => onSelect('blank')}>Use blank starter</AppButton>
    </div>
  )
}
