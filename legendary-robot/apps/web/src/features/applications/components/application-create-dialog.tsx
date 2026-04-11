import { useEffect } from 'react'
import { useForm } from 'react-hook-form'
import { z } from 'zod'
import { zodResolver } from '@hookform/resolvers/zod'
import { AppButton, AppDialog, AppDialogContent, AppInput } from '@/components/ui'

const createApplicationSchema = z.object({
  productType: z.string().min(2, 'Product type is required'),
  externalReference: z.string().optional().or(z.literal('')),
  amountRequested: z.string().optional().or(z.literal('')),
  submittedAt: z.string().optional().or(z.literal(''))
})

type FormValues = z.infer<typeof createApplicationSchema>

type Props = {
  open: boolean
  onOpenChange: (open: boolean) => void
  busy?: boolean
  onSubmit: (payload: {
    productType: string
    externalReference?: string | null
    amountRequested?: number | null
    submittedAt?: string | null
  }) => Promise<void>
}

export function ApplicationCreateDialog({ open, onOpenChange, busy = false, onSubmit }: Props) {
  const form = useForm<FormValues>({
    resolver: zodResolver(createApplicationSchema),
    defaultValues: {
      productType: '',
      externalReference: '',
      amountRequested: '',
      submittedAt: ''
    }
  })

  useEffect(() => {
    if (open) {
      form.reset({
        productType: '',
        externalReference: '',
        amountRequested: '',
        submittedAt: ''
      })
    }
  }, [open, form])

  return (
    <AppDialog open={open} onOpenChange={onOpenChange}>
      <AppDialogContent>
        <form
          className="space-y-4"
          onSubmit={form.handleSubmit(async (values) => {
            await onSubmit({
              productType: values.productType,
              externalReference: values.externalReference || null,
              amountRequested: values.amountRequested ? Number(values.amountRequested) : null,
              submittedAt: values.submittedAt ? new Date(values.submittedAt).toISOString() : null
            })
          })}
        >
          <div>
            <div className="heading-md">Create application</div>
            <div className="body-sm mt-1 text-text-muted">Applications remain tenant-scoped and server-governed. Rule notes will appear after creation when applicable.</div>
          </div>

          <div className="space-y-2">
            <label className="label-sm text-text">Product type</label>
            <AppInput {...form.register('productType')} placeholder="Mortgage refinance, intake packet, policy review…" />
            {form.formState.errors.productType ? <div className="text-sm text-danger">{form.formState.errors.productType.message}</div> : null}
          </div>

          <div className="space-y-2">
            <label className="label-sm text-text">External reference</label>
            <AppInput {...form.register('externalReference')} placeholder="Optional originating system reference" />
          </div>

          <div className="space-y-2">
            <label className="label-sm text-text">Amount requested</label>
            <AppInput type="number" step="0.01" {...form.register('amountRequested')} placeholder="Optional amount used for rule evaluation" />
          </div>

          <div className="space-y-2">
            <label className="label-sm text-text">Submitted at</label>
            <AppInput type="datetime-local" {...form.register('submittedAt')} />
          </div>

          <div className="flex gap-2">
            <AppButton type="submit" disabled={busy}>{busy ? 'Saving…' : 'Create application'}</AppButton>
            <AppButton type="button" variant="ghost" onClick={() => onOpenChange(false)}>Cancel</AppButton>
          </div>
        </form>
      </AppDialogContent>
    </AppDialog>
  )
}
