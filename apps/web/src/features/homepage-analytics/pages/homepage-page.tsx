import { useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import {
  AppBadge,
  AppButton,
  EmptyState,
  LoadingSkeleton,
  PageCanvas,
  PageHeader,
  PageSplit,
} from '@/components/ui'
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

function formatToday() {
  return new Intl.DateTimeFormat('en-US', {
    weekday: 'long',
    month: 'long',
    day: 'numeric',
  }).format(new Date())
}

export function HomepagePage() {
  const { data: auth } = useAuth()
  const [window, setWindow] = useState<'7d' | '30d' | '90d'>('30d')
  const [visibleMonth, setVisibleMonth] = useState(new Date())
  const [selectedDate, setSelectedDate] = useState(dateKey(new Date()))
  const [selectedEventId, setSelectedEventId] = useState<string | null>(null)
  const [detailOpen, setDetailOpen] = useState(false)

  const summaryQuery = useQuery({
    queryKey: queryKeys.dashboard.summary(),
    queryFn: dashboardApi.summary,
  })
  const productionQuery = useQuery({
    queryKey: queryKeys.dashboard.production(window),
    queryFn: () => dashboardApi.production({ window }),
  })
  const range = useMemo(() => monthRange(visibleMonth), [visibleMonth])
  const monthQuery = useQuery({
    queryKey: queryKeys.calendar.range(range.startDate, range.endDate),
    queryFn: () => calendarApi.list({ startDate: range.startDate, endDate: range.endDate }),
  })
  const dayQuery = useQuery({
    queryKey: queryKeys.calendar.day(selectedDate),
    queryFn: () => calendarApi.day(selectedDate),
  })

  const summary = summaryQuery.data?.data
  const production = productionQuery.data?.data
  const canCreateClient = hasPermission(auth?.permissions ?? [], 'clients.create')

  const greeting = summary?.hero.greeting?.trim() || 'Welcome back'
  const displayName = auth?.user.displayName ?? summary?.hero.userDisplayName ?? 'User'
  const workspaceName = auth?.tenant.name ?? summary?.hero.tenantName ?? 'Workspace'

  return (
    <PageCanvas>
      <PageHeader
        variant="cockpit"
        eyebrow="Cockpit"
        title={`${greeting}, ${displayName}`}
        description={`${formatToday()} · ${workspaceName}`}
        statusSummary={
          <>
            <AppBadge variant="neutral">Operational overview</AppBadge>
            <AppBadge variant="info">{selectedDate}</AppBadge>
          </>
        }
        secondaryActions={
          <AppButton asChild size="sm" variant="secondary">
            <Link to="/app/clients">View all clients</Link>
          </AppButton>
        }
        actions={
          canCreateClient ? (
            <AppButton asChild size="sm">
              <Link to="/app/clients/new">New client</Link>
            </AppButton>
          ) : null
        }
      />

      {summaryQuery.isLoading && !summary ? <LoadingSkeleton lines={5} /> : null}

      {summary?.kpis?.length ? (
        <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
          {summary.kpis.map((card) => (
            <KpiCard key={card.key} card={card} />
          ))}
        </div>
      ) : null}

      <PageSplit variant="cockpit">
        <div className="min-w-0">
          <MonthCalendar
            month={visibleMonth}
            selectedDate={selectedDate}
            today={dateKey(new Date())}
            events={monthQuery.data?.data.items ?? []}
            onMonthChange={setVisibleMonth}
            onSelectDate={setSelectedDate}
            onOpenEvent={(eventId) => {
              setSelectedEventId(eventId)
              setDetailOpen(true)
            }}
          />
        </div>
        <SelectedDayPanel
          isLoading={dayQuery.isLoading}
          data={dayQuery.data?.data}
          onOpenEvent={(eventId) => {
            setSelectedEventId(eventId)
            setDetailOpen(true)
          }}
        />
      </PageSplit>

      {productionQuery.isLoading && !production ? <LoadingSkeleton lines={4} /> : null}
      {production ? (
        <ProductionLineChart
          data={production}
          window={window}
          onWindowChange={setWindow}
        />
      ) : null}

      {(summaryQuery.isError || productionQuery.isError) && !summary && !production ? (
        <EmptyState
          title="Homepage data could not load"
          description="Refresh the page or verify the dashboard and calendar APIs are available."
        />
      ) : null}

      <EventDetailDrawer
        eventId={selectedEventId}
        open={detailOpen}
        onOpenChange={setDetailOpen}
      />
    </PageCanvas>
  )
}
