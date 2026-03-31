import { useMemo, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { AppBadge, AppButton, AppCard, AppCardBody, AppCardHeader, EmptyState, LoadingSkeleton } from '@/components/ui'
import { applicationsApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import { useToast } from '@/components/shell/toast-host'
import { ApplicationCreateDialog } from '@/features/applications/components/application-create-dialog'
import { ApplicationDetailPanel } from '@/features/applications/components/application-detail-panel'

type Props = {
  clientId: string
}

function toneToVariant(tone: string) {
  return tone === 'success' ? 'success' : tone === 'warning' ? 'warning' : tone === 'danger' ? 'danger' : tone === 'info' ? 'info' : 'neutral'
}

export function ClientApplicationsPanel({ clientId }: Props) {
  const queryClient = useQueryClient()
  const { notify } = useToast()
  const [createOpen, setCreateOpen] = useState(false)
  const [selectedApplicationId, setSelectedApplicationId] = useState<string | null>(null)

  const listQuery = useQuery({
    queryKey: queryKeys.applications.list(clientId),
    queryFn: () => applicationsApi.list(clientId)
  })

  const items = listQuery.data?.data.items ?? []

  const effectiveSelectedApplicationId = selectedApplicationId ?? items[0]?.id ?? null

  const detailQuery = useQuery({
    queryKey: effectiveSelectedApplicationId ? queryKeys.applications.detail(clientId, effectiveSelectedApplicationId) : [...queryKeys.applications.all, 'detail', clientId, 'empty'],
    queryFn: () => applicationsApi.get(clientId, effectiveSelectedApplicationId ?? ''),
    enabled: Boolean(effectiveSelectedApplicationId)
  })

  const createMutation = useMutation({
    mutationFn: (body: { productType: string, externalReference?: string | null, amountRequested?: number | null, submittedAt?: string | null }) =>
      applicationsApi.create(clientId, body),
    onSuccess: async (response) => {
      setSelectedApplicationId(response.data.application.id)
      setCreateOpen(false)
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.applications.list(clientId) }),
        queryClient.invalidateQueries({ queryKey: queryKeys.applications.detail(clientId, response.data.application.id) }),
        queryClient.invalidateQueries({ queryKey: queryKeys.clients.detail(clientId) })
      ])
      notify({
        title: 'Application created',
        description: 'Status history and rule-note evidence were created through the governed service path.',
        tone: 'success'
      })
    }
  })

  const ruleCounts = useMemo(() => {
    if (!detailQuery.data?.data.application.ruleSummary) return null
    return detailQuery.data.data.application.ruleSummary
  }, [detailQuery.data])

  if (listQuery.isLoading) {
    return <LoadingSkeleton lines={8} />
  }

  return (
    <>
      <div className="grid gap-6 xl:grid-cols-[360px_minmax(0,1fr)]">
        <AppCard>
          <AppCardHeader>
            <div className="flex items-center justify-between gap-3">
              <div>
                <div className="heading-md">Applications</div>
                <div className="body-sm text-text-muted">Create and review client-linked applications without leaving the governed workspace.</div>
              </div>
              <AppButton type="button" onClick={() => setCreateOpen(true)}>Create</AppButton>
            </div>
          </AppCardHeader>
          <AppCardBody>
            {items.length ? (
              <div className="space-y-3">
                {items.map((application) => (
                  <button
                    key={application.id}
                    type="button"
                    onClick={() => setSelectedApplicationId(application.id)}
                    className="w-full rounded-lg border border-border bg-muted p-4 text-left transition hover:border-primary/40"
                  >
                    <div className="flex items-start justify-between gap-3">
                      <div>
                        <div className="font-medium text-text">{application.applicationNumber}</div>
                        <div className="body-sm text-text-muted">{application.productType}</div>
                      </div>
                      <AppBadge variant={toneToVariant(application.currentStatus.tone)}>{application.currentStatus.label}</AppBadge>
                    </div>
                    <div className="mt-3 text-xs text-text-muted">
                      {application.ownerDisplayName ?? 'Unassigned'} • {application.createdAt ? new Date(application.createdAt).toLocaleString() : '—'}
                    </div>
                    <div className="mt-2 text-xs text-text-muted">
                      {application.ruleSummary.warningCount} warnings • {application.ruleSummary.blockingCount} blocking notes
                    </div>
                  </button>
                ))}
              </div>
            ) : (
              <EmptyState title="No applications yet" description="Create the first governed application to start status history and rule-note evidence." />
            )}
          </AppCardBody>
        </AppCard>

        <div className="space-y-4">
          {ruleCounts ? (
            <div className="grid gap-3 md:grid-cols-3">
              <div className="rounded-lg border border-border bg-muted p-4">
                <div className="label-sm uppercase tracking-[0.12em] text-text-muted">Info</div>
                <div className="heading-lg mt-2 text-text">{ruleCounts.infoCount}</div>
              </div>
              <div className="rounded-lg border border-border bg-muted p-4">
                <div className="label-sm uppercase tracking-[0.12em] text-text-muted">Warnings</div>
                <div className="heading-lg mt-2 text-text">{ruleCounts.warningCount}</div>
              </div>
              <div className="rounded-lg border border-border bg-muted p-4">
                <div className="label-sm uppercase tracking-[0.12em] text-text-muted">Blocking</div>
                <div className="heading-lg mt-2 text-text">{ruleCounts.blockingCount}</div>
              </div>
            </div>
          ) : null}

          {detailQuery.isLoading && effectiveSelectedApplicationId ? <LoadingSkeleton lines={8} /> : <ApplicationDetailPanel clientId={clientId} payload={detailQuery.data?.data} />}
        </div>
      </div>

      <ApplicationCreateDialog
        open={createOpen}
        onOpenChange={setCreateOpen}
        busy={createMutation.isPending}
        onSubmit={(body) => createMutation.mutateAsync(body)}
      />
    </>
  )
}
