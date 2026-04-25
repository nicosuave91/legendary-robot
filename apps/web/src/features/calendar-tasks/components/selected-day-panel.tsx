import { CalendarClock, FileText, FolderOpen, Plus } from 'lucide-react'
import { Link } from 'react-router-dom'
import {
  AppBadge,
  AppButton,
  AppCard,
  AppCardBody,
  AppCardHeader,
  EmptyState,
  LoadingSkeleton,
} from '@/components/ui'
import { dayLabel, formatTimeRange } from '@/features/calendar-tasks/calendar-utils'
import type { CalendarDayResponse, CalendarEventSummary } from '@/lib/api/generated/client'

type SelectedDayPanelProps = {
  isLoading: boolean
  data?: CalendarDayResponse
  upcomingEvents?: CalendarEventSummary[]
  onOpenEvent: (eventId: string) => void
}

function buildFallbackDayResponse(events: CalendarEventSummary[]): CalendarDayResponse {
  const selectedDate = events[0]?.startsAt?.slice(0, 10) ?? new Date().toISOString().slice(0, 10)

  return {
    selectedDate,
    isToday: selectedDate === new Date().toISOString().slice(0, 10),
    summary: {
      eventCount: events.length,
      openTaskCount: events.reduce((total, event) => total + event.taskSummary.open, 0),
      completedTaskCount: events.reduce((total, event) => total + event.taskSummary.completed, 0),
      blockedTaskCount: events.reduce((total, event) => total + event.taskSummary.blocked, 0),
      skippedTaskCount: events.reduce((total, event) => total + event.taskSummary.skipped, 0),
    },
    events,
  }
}

export function SelectedDayPanel({
  isLoading,
  data,
  upcomingEvents = [],
  onOpenEvent,
}: SelectedDayPanelProps) {
  const resolvedData = data ?? (upcomingEvents.length ? buildFallbackDayResponse(upcomingEvents) : undefined)

  if (isLoading && !resolvedData) {
    return <LoadingSkeleton lines={6} />
  }

  if (!resolvedData) {
    return (
      <div className="self-start">
        <EmptyState
          title="Select a date"
          description="Choose a date to review scheduled work."
        />
      </div>
    )
  }

  if (!resolvedData.events.length) {
    return (
      <AppCard tone="secondary" className="self-start">
        <AppCardHeader density="compact">
          <div className="heading-md">{dayLabel(resolvedData.selectedDate)}</div>
        </AppCardHeader>
        <AppCardBody density="compact">
          <div className="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-dashed border-border bg-muted/15 px-4 py-3">
            <div className="body-sm text-text-muted">Nothing scheduled — add an event.</div>
            <AppButton asChild size="sm">
              <Link to="/app/calendar">
                <Plus size={14} />
                Create event
              </Link>
            </AppButton>
          </div>
        </AppCardBody>
      </AppCard>
    )
  }

  return (
    <AppCard tone="secondary" className="self-start">
      <AppCardHeader density="compact">
        <div className="flex flex-wrap items-start justify-between gap-3">
          <div className="space-y-1">
            <div className="heading-md">{dayLabel(resolvedData.selectedDate)}</div>
            <div className="body-sm text-text-muted">
              Selected-day support panel.
            </div>
          </div>
          <div className="flex flex-wrap gap-2">
            <AppBadge variant="neutral">{resolvedData.summary.eventCount} events</AppBadge>
            <AppBadge variant="info">{resolvedData.summary.openTaskCount} open</AppBadge>
            <AppBadge variant="success">
              {resolvedData.summary.completedTaskCount} done
            </AppBadge>
          </div>
        </div>
      </AppCardHeader>
      <AppCardBody density="compact">
        <div className="space-y-3">
          {resolvedData.events.map((event) => (
            <div
              key={event.id}
              className="rounded-lg border border-border bg-surface px-4 py-3 shadow-xs"
            >
              <div className="flex flex-wrap items-start justify-between gap-3">
                <div className="min-w-0 flex-1">
                  <div className="flex flex-wrap items-center gap-2">
                    <CalendarClock size={15} className="text-text-muted" />
                    <div className="font-semibold text-text">{event.title}</div>
                    <AppBadge variant="neutral">
                      {event.eventType.replace(/_/g, ' ')}
                    </AppBadge>
                  </div>
                  <div className="body-sm mt-1 text-text-muted">
                    {formatTimeRange(event.startsAt, event.endsAt, event.isAllDay)}
                  </div>
                  {event.description ? (
                    <div className="body-sm mt-2 text-text-muted">
                      {event.description}
                    </div>
                  ) : null}
                  <div className="mt-2 flex flex-wrap gap-2">
                    {event.client ? (
                      <AppBadge variant="info">
                        <FileText size={12} className="mr-1 inline-block" />
                        {event.client.displayName}
                      </AppBadge>
                    ) : null}
                    <AppBadge variant="neutral">{event.taskSummary.open} open</AppBadge>
                    <AppBadge variant="success">
                      {event.taskSummary.completed} done
                    </AppBadge>
                    {event.taskSummary.blocked ? (
                      <AppBadge variant="warning">
                        {event.taskSummary.blocked} blocked
                      </AppBadge>
                    ) : null}
                  </div>
                </div>
                <div className="flex flex-wrap gap-2">
                  <AppButton type="button" size="sm" onClick={() => onOpenEvent(event.id)}>
                    Open event
                  </AppButton>
                  {event.client ? (
                    <AppButton asChild type="button" variant="secondary" size="sm">
                      <Link to={`/app/clients/${event.client.id}/events`}>
                        <FolderOpen size={14} className="mr-1.5 inline-block" />
                        Open file
                      </Link>
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
