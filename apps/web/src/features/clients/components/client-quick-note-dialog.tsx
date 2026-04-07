import { useEffect, useState } from 'react'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { AppButton, AppDialog, AppDialogContent, AppTextarea } from '@/components/ui'
import { clientsApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import { useToast } from '@/components/shell/toast-host'

type Props = {
  clientId: string
  open: boolean
  onOpenChange: (open: boolean) => void
}

export function ClientQuickNoteDialog({ clientId, open, onOpenChange }: Props) {
  const queryClient = useQueryClient()
  const { notify } = useToast()
  const [body, setBody] = useState('')

  useEffect(() => {
    if (open) {
      setBody('')
    }
  }, [open])

  const createNoteMutation = useMutation({
    mutationFn: async () => clientsApi.createNote(clientId, { body }),
    onSuccess: async () => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.clients.detail(clientId) }),
        queryClient.invalidateQueries({ queryKey: queryKeys.clients.all }),
        queryClient.invalidateQueries({ queryKey: queryKeys.dashboard.all }),
      ])
      notify({
        title: 'Note added',
        description: 'The client note was saved and the workspace refreshed from the server.',
        tone: 'success',
      })
      onOpenChange(false)
    },
    onError: (error) => {
      notify({
        title: 'Note could not be saved',
        description: error instanceof Error ? error.message : 'The note could not be created.',
        tone: 'danger',
      })
    },
  })

  return (
    <AppDialog open={open} onOpenChange={onOpenChange}>
      <AppDialogContent>
        <div className="space-y-4">
          <div>
            <div className="heading-md">Add note</div>
            <div className="body-sm mt-1 text-text-muted">
              Capture a quick governed note without leaving the client overview.
            </div>
          </div>

          <div className="space-y-2">
            <label className="label-sm text-text">Note</label>
            <AppTextarea
              value={body}
              onChange={(event) => setBody(event.currentTarget.value)}
              placeholder="Capture a client update, next step, or important context."
            />
          </div>

          <div className="flex justify-end gap-2">
            <AppButton type="button" variant="secondary" onClick={() => onOpenChange(false)}>
              Cancel
            </AppButton>
            <AppButton
              type="button"
              onClick={() => createNoteMutation.mutate()}
              disabled={createNoteMutation.isPending || body.trim().length === 0}
            >
              {createNoteMutation.isPending ? 'Saving…' : 'Save note'}
            </AppButton>
          </div>
        </div>
      </AppDialogContent>
    </AppDialog>
  )
}
