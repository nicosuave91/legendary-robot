import { AppButton, AppCard, AppCardBody, AppCardHeader } from '@/components/ui'

type ImportCommitPanelProps = {
  canCommit: boolean
  status: string
  onValidate: () => void
  onCommit: () => void
  validatePending?: boolean
  commitPending?: boolean
}

export function ImportCommitPanel({
  canCommit,
  status,
  onValidate,
  onCommit,
  validatePending = false,
  commitPending = false,
}: ImportCommitPanelProps) {
  const isValidating = ['validation_queued', 'validating'].includes(status)
  const isCommitting = ['commit_queued', 'committing'].includes(status)

  return (
    <AppCard>
      <AppCardHeader>
        <div className="heading-md">Validation and commit</div>
        <div className="body-sm text-text-muted">Commit is explicit and irreversible. The browser never decides readiness on its own.</div>
      </AppCardHeader>
      <AppCardBody>
        <div className="space-y-4">
          <div className="rounded-lg border border-border bg-muted p-4 body-sm text-text-muted">
            Current status: <span className="font-medium text-text">{status.replaceAll('_', ' ')}</span>
          </div>
          <div className="flex flex-wrap gap-3">
            <AppButton type="button" variant="secondary" onClick={onValidate} disabled={validatePending || isValidating || isCommitting}>
              {validatePending || isValidating ? 'Validating…' : 'Validate staged file'}
            </AppButton>
            <AppButton type="button" onClick={onCommit} disabled={!canCommit || commitPending || isCommitting}>
              {commitPending || isCommitting ? 'Committing…' : 'Commit validated rows'}
            </AppButton>
          </div>
        </div>
      </AppCardBody>
    </AppCard>
  )
}
