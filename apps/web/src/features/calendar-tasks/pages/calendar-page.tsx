import { useMemo, useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { useSearchParams } from 'react-router-dom'
import { PageHeader, AppButton, EmptyState } from '@/components/ui'
import { EventCreateDialog } from '@/features/calendar-tasks/components/event-create-dialog'
import { EventDetailDrawer } from '@/features/calendar-tasks/components/event-detail-drawer'
import { MonthCalendar } from '@/features/calendar-tasks/components/month-calendar'
import { SelectedDayPanel } from '@/features/calendar-tasks/components/selected-day-panel'
import { dateKey, monthRange, parseDateKey } from '@/features/calendar-tasks/calendar-utils'
import { calendarApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import { hasPermission } from '@/lib/auth/permission-map'
import { useAuth } from '@/lib/auth/auth-hooks'

export function CalendarPage() {
  const [searchParams] = useSearchParams()
  const initialDate = searchParams.get('date') ?? dateKey(new Date())
  const initialEventId = searchParams.get('eventId')
  const [visibleMonth, setVisibleMonth] = useState(parseDateKey(initialDate))
  const [selectedDate, setSelectedDate] = useState(initialDate)
  const [selectedEventId, setSelectedEventId] = useState<string | null>(initialEventId)
  const [detailOpen, setDetailOpen] = useState(Boolean(initialEventId))
  const [createOpen, setCreateOpen] = useState(false)
  const { data: auth } = useAuth()

  const range = useMemo(() => monthRange(visibleMonth), [visibleMonth])
  const monthQuery = useQuery({ queryKey: queryKeys.calendar.range(range.startDate, range.endDate), queryFn: () => calendarApi.list({ startDate: range.startDate, endDate: range.endDate }) })
  const dayQuery = useQuery({ queryKey: queryKeys.calendar.day(selectedDate), queryFn: () => calendarApi.day(selectedDate) })

  const items = monthQuery.data?.data.items ?? []
  const canCreate = hasPermission(auth?.permissions ?? [], 'calendar.create')

  return (
    <div className="space-y-6">
      <PageHeader title="Calendar & Tasks" description="Operational calendar drilldown for day selection, event detail, linked files, and durable task history." actions={canCreate ? <AppButton type="button" onClick={() => setCreateOpen(true)}>Create event</AppButton> : undefined} />
      {(monthQuery.isError || dayQuery.isError) && !monthQuery.data && !dayQuery.data ? <EmptyState title="Calendar data could not load" description="Verify the calendar contracts, generated client sync, and API wiring." /> : null}
      <div className="grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.95fr)]">
        <MonthCalendar month={visibleMonth} selectedDate={selectedDate} today={dateKey(new Date())} events={items} onMonthChange={setVisibleMonth} onSelectDate={setSelectedDate} onOpenEvent={(eventId) => { setSelectedEventId(eventId); setDetailOpen(true) }} />
        <SelectedDayPanel isLoading={dayQuery.isLoading} data={dayQuery.data?.data} onOpenEvent={(eventId) => { setSelectedEventId(eventId); setDetailOpen(true) }} />
      </div>
      <EventDetailDrawer eventId={selectedEventId} open={detailOpen} onOpenChange={setDetailOpen} />
      <EventCreateDialog open={createOpen} onOpenChange={setCreateOpen} selectedDate={selectedDate} />
    </div>
  )
}
