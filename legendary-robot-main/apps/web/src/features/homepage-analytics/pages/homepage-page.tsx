import { useMemo, useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { PageHeader, LoadingSkeleton, EmptyState } from '@/components/ui'
import { HomeHeroCard } from '@/features/homepage-analytics/components/home-hero-card'
import { KpiCard } from '@/features/homepage-analytics/components/kpi-card'
import { ProductionLineChart } from '@/features/homepage-analytics/components/production-line-chart'
import { EventDetailDrawer } from '@/features/calendar-tasks/components/event-detail-drawer'
import { MonthCalendar } from '@/features/calendar-tasks/components/month-calendar'
import { SelectedDayPanel } from '@/features/calendar-tasks/components/selected-day-panel'
import { dateKey, monthRange } from '@/features/calendar-tasks/calendar-utils'
import { dashboardApi, calendarApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import { hasPermission } from '@/lib/auth/permission-map'
import { useAuth } from '@/lib/auth/auth-hooks'

export function HomepagePage() {
  const { data: auth } = useAuth()
  const [window, setWindow] = useState<'7d' | '30d' | '90d'>('30d')
  const [visibleMonth, setVisibleMonth] = useState(new Date())
  const [selectedDate, setSelectedDate] = useState(dateKey(new Date()))
  const [selectedEventId, setSelectedEventId] = useState<string | null>(null)
  const [detailOpen, setDetailOpen] = useState(false)

  const summaryQuery = useQuery({ queryKey: queryKeys.dashboard.summary(), queryFn: dashboardApi.summary })
  const productionQuery = useQuery({ queryKey: queryKeys.dashboard.production(window), queryFn: () => dashboardApi.production({ window }) })
  const range = useMemo(() => monthRange(visibleMonth), [visibleMonth])
  const monthQuery = useQuery({ queryKey: queryKeys.calendar.range(range.startDate, range.endDate), queryFn: () => calendarApi.list({ startDate: range.startDate, endDate: range.endDate }) })
  const dayQuery = useQuery({ queryKey: queryKeys.calendar.day(selectedDate), queryFn: () => calendarApi.day(selectedDate) })

  const summary = summaryQuery.data?.data
  const production = productionQuery.data?.data
  const canCreateClient = hasPermission(auth?.permissions ?? [], 'clients.create')

  return (
    <div className="space-y-6">
      <PageHeader title="Homepage" description="Role-safe production metrics and direct paths into calendar, event, task, and client work now render through the shared shell and generated contracts." />

      {summaryQuery.isLoading && !summary ? <LoadingSkeleton lines={5} /> : null}
      {summary ? <HomeHeroCard hero={summary.hero} canCreateClient={canCreateClient} /> : null}

      {summary?.kpis?.length ? (
        <div className="grid gap-4 xl:grid-cols-4 md:grid-cols-2">
          {summary.kpis.map((card) => <KpiCard key={card.key} card={card} />)}
        </div>
      ) : null}

      <div className="grid gap-6 xl:grid-cols-[minmax(0,1.3fr)_minmax(320px,0.95fr)]">
        <MonthCalendar month={visibleMonth} selectedDate={selectedDate} today={dateKey(new Date())} events={monthQuery.data?.data.items ?? []} onMonthChange={setVisibleMonth} onSelectDate={setSelectedDate} onOpenEvent={(eventId) => { setSelectedEventId(eventId); setDetailOpen(true) }} />
        <SelectedDayPanel isLoading={dayQuery.isLoading} data={dayQuery.data?.data} onOpenEvent={(eventId) => { setSelectedEventId(eventId); setDetailOpen(true) }} />
      </div>

      {productionQuery.isLoading && !production ? <LoadingSkeleton lines={6} /> : null}
      {production ? <ProductionLineChart data={production} window={window} onWindowChange={setWindow} /> : null}

      {(summaryQuery.isError || productionQuery.isError) && !summary && !production ? <EmptyState title="Homepage data could not load" description="The homepage only renders from typed dashboard and calendar APIs. Verify the contract publish step and generated client wiring before continuing." /> : null}
      <EventDetailDrawer eventId={selectedEventId} open={detailOpen} onOpenChange={setDetailOpen} />
    </div>
  )
}
