import { AppButton } from '@/components/ui'

type Props = {
  onReset: () => void
  onSave: () => void
  saveDisabled?: boolean
  saving?: boolean
}

export function WorkflowUnsavedChangesBar({ onReset, onSave, saveDisabled = false, saving = false }: Props) {
  return (
    <div className="sticky bottom-4 z-10 rounded-lg border border-border bg-surface/95 p-4 shadow-lg backdrop-blur">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <div className="font-medium text-text">Unsaved draft changes</div>
          <div className="body-sm text-text-muted">Save the editable draft before publishing a new immutable version.</div>
        </div>
        <div className="flex gap-2">
          <AppButton type="button" variant="secondary" onClick={onReset}>Reset changes</AppButton>
          <AppButton type="button" onClick={onSave} disabled={saveDisabled}>{saving ? 'Saving…' : 'Save draft'}</AppButton>
        </div>
      </div>
    </div>
  )
}
