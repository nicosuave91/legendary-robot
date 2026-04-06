import { useMemo, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useInfiniteQuery } from '@tanstack/react-query'
import { PageHeader, AppBadge, AppButton, AppCard, AppCardBody, AppCardHeader, AppInput, EmptyState, LoadingSkeleton } from '@/components/ui'
import { CommunicationStatusBadge } from '@/features/communications/components/communication-status-badge'
import { communicationsApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'

type ChannelFilter = 'all' | 'sms' | 'email' | 'voice'
type StatusFilter = 'all' | 'pending' | 'failed'

export function CommunicationsPage() {
  const navigate = useNavigate()
  const [search, setSearch] = useState('')
  const [channel, setChannel] = useState<ChannelFilter>('all')
  const [status, setStatus] = useState<StatusFilter>('all')

  const filters = useMemo(() => ({
    search: search || undefined,
    channel,
    status,
    limit: 20
  }), [search, channel, status])

  const inboxQuery = useInfiniteQuery({
    queryKey: queryKeys.communications.inbox(filters),
    queryFn: ({ pageParam }) => communicationsApi.inbox({
      ...filters,
      cursor: typeof pageParam === 'string' ? pageParam : undefined
    }),
    initialPageParam: undefined as string | undefined,
    getNextPageParam: (lastPage) => lastPage.data.paging.hasMore ? lastPage.data.paging.nextCursor : undefined,
    refetchInterval: (query) => query.state.data?.pages[0]?.data.refresh.hasPendingRecentItems ? 5000 : false,
  })

  const items = inboxQuery.data?.pages.flatMap((page) => page.data.items) ?? []
  const totalItems = inboxQuery.data?.pages[0]?.data.summary.itemCount ?? 0
  const channelButtons = useMemo(() => ([{ key: 'all', label: 'All' }, { key: 'sms', label: 'SMS' }, { key: 'email', label: 'Email' }, { key: 'voice', label: 'Calls' }]) as const, [])

  return (
    <div className="space-y-6">
      <PageHeader
        title="Communications inbox"
        description="Review the newest governed communications activity across visible clients, then drill into the client workspace when action is needed."
        actions={<AppButton type="button" variant="secondary" onClick={() => inboxQuery.refetch()}>Refresh inbox</AppButton>}
      />

      <AppCard>
        <AppCardHeader>
          <div className="heading-md">Operational inbox</div>
          <div className="body-sm text-text-muted">This view aggregates timeline items across the clients you are allowed to view. Statuses remain callback-evidence-driven and the browser is never the source of truth.</div>
        </AppCardHeader>
        <AppCardBody className="space-y-4">
          <div className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
            <div className="space-y-2">
              <label className="label-sm text-text" htmlFor="communications-search">Search inbox</label>
              <AppInput
                id="communications-search"
                value={search}
                onChange={(event) => setSearch(event.currentTarget.value)}
                placeholder="Search by client, address, subject, or preview"
              />
            </div>
            <div className="body-sm text-text-muted">{totalItems} matching activities</div>
          </div>

          <div className="flex flex-wrap gap-2">
            {channelButtons.map((filter) => (
              <AppButton key={filter.key} type="button" variant={channel === filter.key ? 'primary' : 'secondary'} onClick={() => setChannel(filter.key)}>
                {filter.label}
              </AppButton>
            ))}
            <AppButton type="button" variant={status === 'failed' ? 'primary' : 'secondary'} onClick={() => setStatus((current) => current === 'failed' ? 'all' : 'failed')}>
              Failures
            </AppButton>
            <AppButton type="button" variant={status === 'pending' ? 'primary' : 'secondary'} onClick={() => setStatus((current) => current === 'pending' ? 'all' : 'pending')}>
              Pending
            </AppButton>
          </div>

          {inboxQuery.isLoading ? <LoadingSkeleton lines={8} /> : null}

          {!inboxQuery.isLoading && items.length === 0 ? (
            <EmptyState
              title="No communications activity found"
              description={search ? 'Try adjusting the current search or filters.' : 'As communication activity is captured across client workspaces, it will appear here in recency order.'}
            />
          ) : null}

          <div className="space-y-3">
            {items.map(({ client, timelineItem }) => (
              <div key={`${client.id}:${timelineItem.id}`} className="rounded-lg border border-border bg-muted p-4">
                <div className="flex flex-wrap items-start justify-between gap-4">
                  <div className="space-y-2">
                    <div className="flex flex-wrap items-center gap-2">
                      <div className="font-medium text-text">{client.displayName}</div>
                      <AppBadge variant="neutral">{client.status}</AppBadge>
                      <AppBadge>{timelineItem.channel.toUpperCase()}</AppBadge>
                      <AppBadge>{timelineItem.direction}</AppBadge>
                      <CommunicationStatusBadge status={timelineItem.status} />
                    </div>

                    <div className="body-sm text-text-muted">
                      {timelineItem.content.subject ?? timelineItem.content.preview ?? 'Communication activity'}
                    </div>

                    <div className="body-sm text-text-muted">
                      {timelineItem.counterpart.address ?? 'No counterpart recorded'} • Owner {client.ownerDisplayName ?? 'Unassigned'}
                    </div>

                    <div className="text-xs text-text-muted">
                      {timelineItem.occurredAt ? new Date(timelineItem.occurredAt).toLocaleString() : 'No activity timestamp'} • Evidence {timelineItem.evidence.source.replaceAll('_', ' ')}
                    </div>
                  </div>

                  <div className="flex flex-wrap gap-2">
                    <AppButton type="button" onClick={() => navigate(`/app/clients/${client.id}/communications`)}>
                      Open communications
                    </AppButton>
                    <AppButton type="button" variant="secondary" onClick={() => navigate(`/app/clients/${client.id}/overview`)}>
                      Open client
                    </AppButton>
                  </div>
                </div>
              </div>
            ))}
          </div>

          {inboxQuery.hasNextPage ? (
            <div className="flex justify-center">
              <AppButton type="button" variant="secondary" onClick={() => inboxQuery.fetchNextPage()} disabled={inboxQuery.isFetchingNextPage}>
                {inboxQuery.isFetchingNextPage ? 'Loading older activity…' : 'Load older activity'}
              </AppButton>
            </div>
          ) : null}
        </AppCardBody>
      </AppCard>
    </div>
  )
}
