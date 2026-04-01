import { useState } from 'react'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { AppBadge, AppButton, AppCard, AppCardBody, AppCardHeader } from '@/components/ui'
import { clientsApi } from '@/lib/api/client'
import { ApiError } from '@/lib/api/http'
import { queryKeys } from '@/lib/api/query-keys'
import { useToast } from '@/components/shell/toast-host'
import { DispositionTransitionDialog } from '@/features/disposition/components/disposition-transition-dialog'

type TransitionIssue = {
  code: string
  message: string
}

type ClientDispositionPayload = {
  currentDisposition: {
    tone: string
    label: string
    changedAt?: string | null
    changedByDisplayName?: string | null
  }
  availableDispositionTransitions: Array<{
    code: string
    label: string
  }>
  dispositionHistory: Array<{
    id: string
    toDispositionCode: string
    occurredAt?: string | null
    actorDisplayName?: string | null
    reason?: string | null
  }>
}

type Props = {
  clientId: string
  payload: ClientDispositionPayload
}

export function ClientDispositionPanel({ clientId, payload }: Props) {
  const queryClient = useQueryClient()
  const { notify } = useToast()
  const [dialogOpen, setDialogOpen] = useState(false)
  const [warnings, setWarnings] = useState<TransitionIssue[]>([])
  const [blockingIssues, setBlockingIssues] = useState<TransitionIssue[]>([])

  const transitionMutation = useMutation({
    mutationFn: (body: { targetDispositionCode: string, reason?: string, acknowledgeWarnings?: boolean }) => clientsApi.transitionDisposition(clientId, body),
    onSuccess: async (response) => {
      const data = (response as { data: { warnings: TransitionIssue[]; blockingIssues: TransitionIssue[] } }).data
      setWarnings(data.warnings)
      setBlockingIssues(data.blockingIssues)
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.clients.detail(clientId) }),
        queryClient.invalidateQueries({ queryKey: queryKeys.clients.all })
      ])
      setDialogOpen(false)
      notify({
        title: 'Disposition updated',
        description: 'Lifecycle state moved through the governed state machine and refreshed from the server.',
        tone: 'success'
      })
    },
    onError: (error) => {
      if (error instanceof ApiError && typeof error.payload === 'object' && error.payload && 'data' in error.payload) {
        const data = (error.payload as { data?: { warnings?: TransitionIssue[], blockingIssues?: TransitionIssue[] } }).data
        setWarnings(data?.warnings ?? [])
        setBlockingIssues(data?.blockingIssues ?? [])
      }
    }
  })

  return (
    <>
      <AppCard>
        <AppCardHeader>
          <div className="heading-md">Disposition</div>
          <div className="body-sm text-text-muted">Current lifecycle state is projected from append-only history, not edited directly in the client profile form.</div>
        </AppCardHeader>
        <AppCardBody>
          <div className="flex flex-wrap items-start justify-between gap-4">
            <div className="space-y-3">
              <div className="flex items-center gap-3">
                <AppBadge variant={payload.currentDisposition.tone === 'success' ? 'success' : payload.currentDisposition.tone === 'warning' ? 'warning' : payload.currentDisposition.tone === 'danger' ? 'danger' : payload.currentDisposition.tone === 'info' ? 'info' : 'neutral'}>
                  {payload.currentDisposition.label}
                </AppBadge>
                <div className="text-xs text-text-muted">
                  Last changed {payload.currentDisposition.changedAt ? new Date(payload.currentDisposition.changedAt).toLocaleString() : '—'}
                  {payload.currentDisposition.changedByDisplayName ? ` • ${payload.currentDisposition.changedByDisplayName}` : ''}
                </div>
              </div>

              <div>
                <div className="label-sm uppercase tracking-[0.12em] text-text-muted">Allowed next steps</div>
                <div className="mt-2 flex flex-wrap gap-2">
                  {payload.availableDispositionTransitions.length
                    ? payload.availableDispositionTransitions.map((transition) => <AppBadge key={transition.code} variant="neutral">{transition.label}</AppBadge>)
                    : <span className="body-sm text-text-muted">No additional transitions available from the current state.</span>}
                </div>
              </div>

              <div>
                <div className="label-sm uppercase tracking-[0.12em] text-text-muted">Recent history</div>
                <div className="mt-2 space-y-2">
                  {payload.dispositionHistory.slice(0, 3).map((item) => (
                    <div key={item.id} className="rounded-lg border border-border bg-muted p-3">
                      <div className="font-medium text-text">{item.toDispositionCode}</div>
                      <div className="text-xs text-text-muted">{item.occurredAt ? new Date(item.occurredAt).toLocaleString() : '—'}{item.actorDisplayName ? ` • ${item.actorDisplayName}` : ''}</div>
                      {item.reason ? <div className="body-sm mt-1 text-text-muted">{item.reason}</div> : null}
                    </div>
                  ))}
                </div>
              </div>
            </div>

            <AppButton type="button" onClick={() => setDialogOpen(true)} disabled={!payload.availableDispositionTransitions.length}>
              Change disposition
            </AppButton>
          </div>
        </AppCardBody>
      </AppCard>

      <DispositionTransitionDialog
        open={dialogOpen}
        onOpenChange={setDialogOpen}
        transitions={payload.availableDispositionTransitions}
        warnings={warnings}
        blockingIssues={blockingIssues}
        busy={transitionMutation.isPending}
        onSubmit={async (body) => {
          await transitionMutation.mutateAsync(body)
        }}
      />
    </>
  )
}
