import { useMemo, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import { PageHeader, AppBadge, AppButton, AppCard, AppCardBody, AppCardHeader, AppInput, EmptyState, LoadingSkeleton } from '@/components/ui'
import { clientsApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'

export function CommunicationsPage() {
  const navigate = useNavigate()
  const [search, setSearch] = useState('')

  const filters = useMemo(() => ({
    search: search || undefined,
    sort: 'updated_at' as const,
    direction: 'desc' as const,
    perPage: 50
  }), [search])

  const clientsQuery = useQuery({
    queryKey: queryKeys.clients.list(filters),
    queryFn: () => clientsApi.list(filters)
  })

  const items = useMemo(() => {
    return (clientsQuery.data?.data.items ?? []).filter((client) => client.primaryEmail || client.primaryPhone)
  }, [clientsQuery.data])

  return (
    <div className="space-y-6">
      <PageHeader
        title="Communications"
        description="Open a governed client workspace to send SMS, email, or calls so delivery evidence and callback history stay attached to the client record."
        actions={<AppButton type="button" variant="secondary" onClick={() => navigate('/app/clients')}>Open clients</AppButton>}
      />

      <AppCard>
        <AppCardHeader>
          <div className="heading-md">Client communication entry points</div>
          <div className="body-sm text-text-muted">This surface helps users enter the correct client workspace instead of sending communications from a disconnected global page.</div>
        </AppCardHeader>
        <AppCardBody className="space-y-4">
          <div className="grid gap-4 md:grid-cols-[minmax(0,1fr)_auto] md:items-end">
            <div className="space-y-2">
              <label className="label-sm text-text" htmlFor="communications-search">Find client</label>
              <AppInput
                id="communications-search"
                value={search}
                onChange={(event) => setSearch(event.currentTarget.value)}
                placeholder="Search by client name, email, or phone"
              />
            </div>
            <div className="body-sm text-text-muted">{items.length} contact-ready clients</div>
          </div>

          {clientsQuery.isLoading ? <LoadingSkeleton lines={6} /> : null}

          {!clientsQuery.isLoading && items.length === 0 ? (
            <EmptyState
              title="No contact-ready clients found"
              description={search ? 'Try adjusting the current search.' : 'Clients with an email address or phone number will appear here as communication entry points.'}
            />
          ) : null}

          <div className="space-y-3">
            {items.map((client) => (
              <div key={client.id} className="rounded-lg border border-border bg-muted p-4">
                <div className="flex flex-wrap items-start justify-between gap-4">
                  <div className="space-y-2">
                    <div className="flex flex-wrap items-center gap-2">
                      <div className="font-medium text-text">{client.displayName}</div>
                      <AppBadge variant="neutral">{client.status}</AppBadge>
                      {client.primaryEmail ? <AppBadge variant="info">Email</AppBadge> : null}
                      {client.primaryPhone ? <AppBadge variant="info">SMS / Call</AppBadge> : null}
                    </div>
                    <div className="body-sm text-text-muted">
                      {client.primaryEmail ?? 'No email'} • {client.primaryPhone ?? 'No phone'}
                    </div>
                    <div className="text-xs text-text-muted">
                      Owner {client.ownerDisplayName ?? 'Unassigned'} • Last activity {client.lastActivityAt ? new Date(client.lastActivityAt).toLocaleString() : 'Not recorded yet'}
                    </div>
                  </div>

                  <div className="flex flex-wrap gap-2">
                    <AppButton
                      type="button"
                      aria-label={`Open communications for ${client.displayName}`}
                      onClick={() => navigate(`/app/clients/${client.id}/communications`)}
                    >
                      Open communications
                    </AppButton>
                    <AppButton
                      type="button"
                      variant="secondary"
                      aria-label={`Open client workspace for ${client.displayName}`}
                      onClick={() => navigate(`/app/clients/${client.id}/overview`)}
                    >
                      Open client
                    </AppButton>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </AppCardBody>
      </AppCard>
    </div>
  )
}
