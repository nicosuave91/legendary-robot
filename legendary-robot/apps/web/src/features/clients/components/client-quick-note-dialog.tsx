import { useEffect } from 'react'
import { useForm } from 'react-hook-form'
import { z } from 'zod'
import { zodResolver } from '@hookform/resolvers/zod'
import { AppButton, AppDialog, AppDialogContent, AppTextarea } from '@/components/ui'

const quickNoteSchema = z.object({
  body: z.string().min(2, 'A note is required'),
})

type QuickNoteValues = z.infer<typeof quickNoteSchema>

type Props = {
  open: boolean
  onOpenChange: (open: boolean) => void
  busy?: boolean
  onSubmit: (payload: { body: string }) => Promise<void>
}

export function ClientQuickNoteDialog({ open, onOpenChange, busy = false, onSubmit }: Props) {
  const form = useForm<QuickNoteValues>({
    resolver: zodResolver(quickNoteSchema),
    defaultValues: {
      body: '',
    },
  })

  useEffect(() => {
    if (open) {
      form.reset({ body: '' })
    }
  }, [form, open])

  return (
    <AppDialog open={open} onOpenChange={onOpenChange}>
      <AppDialogContent>
        <form
          className="space-y-4"
          onSubmit={form.handleSubmit(async (values) => {
            await onSubmit({ body: values.body })
          })}
        >
          <div>
            <div className="heading-md">Add note</div>
            <div className="body-sm mt-1 text-text-muted">
              Add a governed note without leaving the client overview.
            </div>
          </div>

          <div className="space-y-2">
            <label className="label-sm text-text">Note</label>
            <AppTextarea
              {...form.register('body')}
              placeholder="Capture the latest client context, decision, or follow-up detail."
            />
            {form.formState.errors.body ? (
              <div className="text-sm text-danger">{form.formState.errors.body.message}</div>
            ) : null}
          </div>

          <div className="flex gap-2">
            <AppButton type="submit" disabled={busy}>
              {busy ? 'Saving…' : 'Add note'}
            </AppButton>
            <AppButton type="button" variant="ghost" onClick={() => onOpenChange(false)}>
              Cancel
            </AppButton>
          </div>
        </form>
      </AppDialogContent>
    </AppDialog>
  )
}
