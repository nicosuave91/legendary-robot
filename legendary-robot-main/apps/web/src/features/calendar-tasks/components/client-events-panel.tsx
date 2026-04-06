import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router-dom'
import { AppBadge, AppButton, AppCard, AppCardBody, AppCardHeader, EmptyState, LoadingSkeleton } from '@/components/ui'
import { EventDetailDrawer } from '@/features/calendar-tasks/components/event-detail-drawer'
import { dateKey, formatTimeRange } from '@/features/calendar-tasks/calendar-utils'
import { calendarApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'

type ClientEventsPanelProps = {
  clientId: string
}

export function ClientEventsPanel({ clientId }: ClientEventsPanelProps) {
  const [selectedEventId, setSelectedEventId] = useState<string | null>(null)
  const [detailOpen, setDetailOpen] = useState(false)
  const eventsQuery = useQuery({
    queryKey: queryKeys.calendar.clientEvents(clientId),
    queryFn: () => calendarApi.clientEvents(clientId),
  })

  const items = eventsQuery.data?.data.items ?? []

  if (eventsQuery.isLoading && !eventsQuery.data) {
    return <LoadingSkeleton lines={6} />
  }

  return (
    <>
      <AppCard>
        <AppCardHeader>
          <div className="heading-md">Client events</div>
          <div className="body-sm text-text-muted">This tab reads canonical calendar data without moving event authority into the client module.</div>
        </AppCardHeader>
        <AppCardBody>
          {!items.length ? <EmptyState title="No linked events yet" description="Calendar-linked events for this file will appear here once they are scheduled." /> : null}
          <div className="space-y-3">
            {items.map((event) => (
              <div key={event.id} className="rounded-lg border border-border bg-muted p-4">
                <div className="flex flex-wrap items-start justify-between gap-3">
                  <div>
                    <div className="font-medium text-text">{event.title}</div>
                    <div className="body-sm mt-1 text-text-muted">{event.startsAt ? new Date(event.startsAt).toLocaleDateString() : '—'} · {formatTimeRange(event.startsAt, event.endsAt, event.isAllDay)}</div>
                    <div className="mt-2 flex flex-wrap gap-2">
                      <AppBadge variant="neutral">{event.eventType.replace(/_/g, ' ')}</AppBadge>
                      <AppBadge variant="info">{event.taskSummary.open} open tasks</AppBadge>
                    </div>
                  </div>
                  <div className="flex flex-wrap gap-2">
                    <AppButton type="button" onClick={() => { setSelectedEventId(event.id); setDetailOpen(true) }}>Open event</AppButton>
                    <AppButton asChild type="button" variant="secondary"><Link to={`/app/calendar?date=${event.startsAt ? dateKey(new Date(event.startsAt)) : ''}&eventId=${event.id}`}>Open in calendar</Link></AppButton>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </AppCardBody>
      </AppCard>
      <EventDetailDrawer eventId={selectedEventId} open={detailOpen} onOpenChange={setDetailOpen} />
    </>
  )
}
