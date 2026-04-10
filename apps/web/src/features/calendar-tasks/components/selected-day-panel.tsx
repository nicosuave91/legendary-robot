import { CalendarClock, FileText, FolderOpen, Plus } from 'lucide-react'
import { Link } from 'react-router-dom'
import { AppBadge, AppButton, AppCard, AppCardBody, AppCardHeader, EmptyState, LoadingSkeleton } from '@/components/ui'
import { dayLabel, formatTimeRange } from '@/features/calendar-tasks/calendar-utils'
import type { CalendarDayResponse } from '@/lib/api/generated/client'

type SelectedDayPanelProps = {
  isLoading: boolean
  data?: CalendarDayResponse
  onOpenEvent: (eventId: string) => void
}

export function SelectedDayPanel({ isLoading, data, onOpenEvent }: SelectedDayPanelProps) {
  if (isLoading && !data) {
    return <LoadingSkeleton lines={6} />
  }

  if (!data) {
    return <EmptyState title="Select a date" description="Choose a calendar date to review scheduled work and required tasks." />
  }

  if (!data.events.length) {
    return (
      <AppCard>
        <AppCardHeader>
          <div className="heading-md">{dayLabel(data.selectedDate)}</div>
        </AppCardHeader>
        <AppCardBody>
          <div className="rounded-lg border border-dashed border-border bg-muted/40 px-4 py-5">
            <div className="flex flex-wrap items-center justify-between gap-3">
              <div>
                <div className="font-medium text-text">Nothing scheduled</div>
                <div className="body-sm text-text-muted">Add an event for this day.</div>
              </div>
              <AppButton type="button" variant="secondary">
                <Plus size={14} />
                Create event
              </AppButton>
            </div>
          </div>
        </AppCardBody>
      </AppCard>
    )
  }

  return (
    <AppCard>
      <AppCardHeader>
        <div className="flex flex-wrap items-start justify-between gap-3">
          <div>
            <div className="heading-md">{dayLabel(data.selectedDate)}</div>
          </div>
          <div className="flex flex-wrap gap-2">
            <AppBadge variant="neutral">{data.summary.eventCount} events</AppBadge>
            <AppBadge variant="info">{data.summary.openTaskCount} open tasks</AppBadge>
            <AppBadge variant="success">{data.summary.completedTaskCount} completed</AppBadge>
          </div>
        </div>
      </AppCardHeader>
      <AppCardBody>
        <div className="space-y-3">
          {data.events.map((event) => (
            <div key={event.id} className="rounded-lg border border-border bg-muted p-4">
              <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <div className="flex items-center gap-2">
                    <CalendarClock size={16} className="text-text-muted" />
                    <div className="font-semibold text-text">{event.title}</div>
                    <AppBadge variant="neutral">{event.eventType.replace(/_/g, ' ')}</AppBadge>
                  </div>
                  <div className="body-sm mt-1 text-text-muted">{formatTimeRange(event.startsAt, event.endsAt, event.isAllDay)}</div>
                  {event.description ? <div className="body-sm mt-2 text-text-muted">{event.description}</div> : null}
                  <div className="mt-2 flex flex-wrap gap-2">
                    {event.client ? (
                      <AppBadge variant="info"><FileText size={12} className="mr-1 inline-block" />{event.client.displayName}</AppBadge>
                    ) : null}
                    <AppBadge variant="neutral">{event.taskSummary.open} open</AppBadge>
                    <AppBadge variant="success">{event.taskSummary.completed} done</AppBadge>
                    {event.taskSummary.blocked ? <AppBadge variant="warning">{event.taskSummary.blocked} blocked</AppBadge> : null}
                  </div>
                </div>
                <div className="flex flex-wrap gap-2">
                  <AppButton type="button" onClick={() => onOpenEvent(event.id)}>Open event</AppButton>
                  {event.client ? (
                    <AppButton asChild type="button" variant="secondary">
                      <Link to={`/app/clients/${event.client.id}/events`}><FolderOpen size={14} className="mr-2 inline-block" />Open file</Link>
                    </AppButton>
                  ) : null}
                </div>
              </div>
            </div>
          ))}
        </div>
      </AppCardBody>
    </AppCard>
  )
}
