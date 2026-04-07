import { useEffect, useState } from 'react'
import { AppButton, AppDialog, AppDialogContent, AppTextarea } from '@/components/ui'

type Props = {
  open: boolean
  onOpenChange: (open: boolean) => void
  busy?: boolean
  onSubmit: (body: string) => Promise<void>
}

export function ClientQuickNoteDialog({ open, onOpenChange, busy = false, onSubmit }: Props) {
  const [body, setBody] = useState('')

  useEffect(() => {
    if (open) setBody('')
  }, [open])

  return (
    <AppDialog open={open} onOpenChange={onOpenChange}>
      <AppDialogContent>
        <div className="space-y-4">
          <div>
            <div className="heading-md">Add note</div>
            <div className="body-sm mt-1 text-text-muted">Capture a governed note without leaving the client overview.</div>
          </div>

          <div className="space-y-2">
            <label className="label-sm text-text">Note body</label>
            <AppTextarea value={body} onChange={(event) => setBody(event.currentTarget.value)} placeholder="Capture the next-step note for this client." />
          </div>

          <div className="flex justify-end gap-2">
            <AppButton type="button" variant="secondary" onClick={() => onOpenChange(false)}>Cancel</AppButton>
            <AppButton
              type="button"
              onClick={async () => {
                await onSubmit(body)
                onOpenChange(false)
              }}
              disabled={busy || body.trim().length === 0}
            >
              {busy ? 'Saving…' : 'Add note'}
            </AppButton>
          </div>
        </div>
      </AppDialogContent>
    </AppDialog>
  )
}
