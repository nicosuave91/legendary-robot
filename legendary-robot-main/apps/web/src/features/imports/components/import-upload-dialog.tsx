import { useState } from 'react'
import { AppButton, AppDialog, AppDialogContent, AppInput, AppSelect } from '@/components/ui'

type ImportUploadDialogProps = {
  isPending?: boolean
  onSubmit: (payload: { importType: string; file: File }) => void
}

export function ImportUploadDialog({ isPending = false, onSubmit }: ImportUploadDialogProps) {
  const [open, setOpen] = useState(false)
  const [importType, setImportType] = useState('clients')
  const [file, setFile] = useState<File | null>(null)

  const handleSubmit = () => {
    if (!file) return
    onSubmit({ importType, file })
    setOpen(false)
    setFile(null)
  }

  return (
    <AppDialog open={open} onOpenChange={setOpen}>
      <AppButton type="button" onClick={() => setOpen(true)}>
        Upload import file
      </AppButton>
      <AppDialogContent>
        <div className="space-y-4">
          <div>
            <div className="heading-md">Upload governed import</div>
            <div className="body-sm mt-1 text-text-muted">
              Files stage first, validate second, and only reach production records after an explicit commit.
            </div>
          </div>
          <div className="space-y-2">
            <label className="label-sm text-text">Import type</label>
            <AppSelect value={importType} onChange={(event) => setImportType(event.currentTarget.value)}>
              <option value="clients">Clients</option>
            </AppSelect>
          </div>
          <div className="space-y-2">
            <label className="label-sm text-text">CSV file</label>
            <AppInput type="file" accept=".csv,text/csv,.txt" onChange={(event) => setFile(event.currentTarget.files?.[0] ?? null)} />
          </div>
          <div className="flex justify-end gap-3">
            <AppButton type="button" variant="secondary" onClick={() => setOpen(false)}>
              Cancel
            </AppButton>
            <AppButton type="button" onClick={handleSubmit} disabled={isPending || !file}>
              {isPending ? 'Uploading…' : 'Stage import'}
            </AppButton>
          </div>
        </div>
      </AppDialogContent>
    </AppDialog>
  )
}
