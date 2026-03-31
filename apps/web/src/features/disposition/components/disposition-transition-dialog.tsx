import { useEffect, useState } from 'react'
import { AppButton, AppDialog, AppDialogContent, AppInput, AppSelect } from '@/components/ui'
import type { DispositionTransitionOption, TransitionIssue } from '@/lib/api/generated/client'

type Props = {
  open: boolean
  onOpenChange: (open: boolean) => void
  transitions: DispositionTransitionOption[]
  onSubmit: (payload: { targetDispositionCode: string, reason?: string, acknowledgeWarnings?: boolean }) => Promise<void>
  warnings: TransitionIssue[]
  blockingIssues: TransitionIssue[]
  busy?: boolean
}

export function DispositionTransitionDialog({
  open,
  onOpenChange,
  transitions,
  onSubmit,
  warnings,
  blockingIssues,
  busy = false
}: Props) {
  const [targetDispositionCode, setTargetDispositionCode] = useState(transitions[0]?.code ?? '')
  const [reason, setReason] = useState('')

  useEffect(() => {
    setTargetDispositionCode(transitions[0]?.code ?? '')
  }, [transitions])

  const canSubmit = targetDispositionCode.length > 0 && blockingIssues.length === 0

  return (
    <AppDialog open={open} onOpenChange={onOpenChange}>
      <AppDialogContent>
        <div className="space-y-4">
          <div>
            <div className="heading-md">Change disposition</div>
            <div className="body-sm mt-1 text-text-muted">Lifecycle changes stay server-governed and append-only. Warnings require explicit acknowledgement.</div>
          </div>

          {blockingIssues.length ? (
            <div className="rounded-lg border border-danger/30 bg-danger/5 p-3">
              <div className="font-medium text-danger">This transition is blocked</div>
              <ul className="mt-2 list-disc pl-5 text-sm text-danger">
                {blockingIssues.map((issue) => <li key={issue.code}>{issue.message}</li>)}
              </ul>
            </div>
          ) : null}

          {warnings.length ? (
            <div className="rounded-lg border border-warning/30 bg-warning/5 p-3">
              <div className="font-medium text-warning">Warnings require acknowledgement</div>
              <ul className="mt-2 list-disc pl-5 text-sm text-warning">
                {warnings.map((issue) => <li key={issue.code}>{issue.message}</li>)}
              </ul>
            </div>
          ) : null}

          <div className="space-y-2">
            <label className="label-sm text-text">Next disposition</label>
            <AppSelect value={targetDispositionCode} onChange={(event) => setTargetDispositionCode(event.currentTarget.value)}>
              {transitions.map((transition) => <option key={transition.code} value={transition.code}>{transition.label}</option>)}
            </AppSelect>
          </div>

          <div className="space-y-2">
            <label className="label-sm text-text">Reason</label>
            <AppInput value={reason} onChange={(event) => setReason(event.currentTarget.value)} placeholder="Capture rationale for the audit trail." />
          </div>

          <div className="flex flex-wrap gap-2">
            <AppButton
              type="button"
              disabled={!canSubmit || busy}
              onClick={() => onSubmit({ targetDispositionCode, reason })}
            >
              {busy ? 'Saving…' : 'Submit transition'}
            </AppButton>
            {warnings.length ? (
              <AppButton
                type="button"
                variant="secondary"
                disabled={!canSubmit || busy}
                onClick={() => onSubmit({ targetDispositionCode, reason, acknowledgeWarnings: true })}
              >
                {busy ? 'Saving…' : 'Acknowledge warnings and continue'}
              </AppButton>
            ) : null}
            <AppButton type="button" variant="ghost" onClick={() => onOpenChange(false)}>Close</AppButton>
          </div>
        </div>
      </AppDialogContent>
    </AppDialog>
  )
}
