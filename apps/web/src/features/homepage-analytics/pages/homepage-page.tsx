import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { PageHeader, LoadingSkeleton, EmptyState } from '@/components/ui'
import { HomeHeroCard } from '@/features/homepage-analytics/components/home-hero-card'
import { KpiCard } from '@/features/homepage-analytics/components/kpi-card'
import { ProductionLineChart } from '@/features/homepage-analytics/components/production-line-chart'
import { dashboardApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import { hasPermission } from '@/lib/auth/permission-map'
import { useAuth } from '@/lib/auth/auth-hooks'

export function HomepagePage() {
  const { data: auth } = useAuth()
  const [window, setWindow] = useState<'7d' | '30d' | '90d'>('30d')

  const summaryQuery = useQuery({
    queryKey: queryKeys.dashboard.summary(),
    queryFn: dashboardApi.summary
  })

  const productionQuery = useQuery({
    queryKey: queryKeys.dashboard.production(window),
    queryFn: () => dashboardApi.production({ window })
  })

  const summary = summaryQuery.data?.data
  const production = productionQuery.data?.data
  const canCreateClient = hasPermission(auth?.permissions ?? [], 'clients.create')

  return (
    <div className="space-y-6">
      <PageHeader
        title="Homepage"
        description="Role-safe production metrics and direct paths into the client workspace now render through the shared Sprint 1 shell and typed DTO contracts."
      />

      {summaryQuery.isLoading && !summary ? <LoadingSkeleton lines={5} /> : null}
      {summary ? <HomeHeroCard hero={summary.hero} canCreateClient={canCreateClient} /> : null}

      {summary?.kpis?.length ? (
        <div className="grid gap-4 xl:grid-cols-4 md:grid-cols-2">
          {summary.kpis.map((card) => <KpiCard key={card.key} card={card} />)}
        </div>
      ) : null}

      {productionQuery.isLoading && !production ? <LoadingSkeleton lines={6} /> : null}
      {production ? (
        <ProductionLineChart data={production} window={window} onWindowChange={setWindow} />
      ) : null}

      {(summaryQuery.isError || productionQuery.isError) && !summary && !production ? (
        <EmptyState
          title="Homepage data could not load"
          description="The homepage only renders from dashboard APIs. Verify the Sprint 4 contract publish step and generated client wiring before continuing."
        />
      ) : null}
    </div>
  )
}
