import { Link } from 'react-router-dom'
import { AppButton, AppCard, AppCardBody } from '@/components/ui'
import type { DashboardSummaryResponse } from '@/lib/api/generated/client'

type HomeHeroCardProps = {
  hero: DashboardSummaryResponse['hero']
  canCreateClient: boolean
}

export function HomeHeroCard({ hero, canCreateClient }: HomeHeroCardProps) {
  const meta = [hero.tenantName, hero.selectedIndustry].filter(Boolean).join(' · ')

  return (
    <AppCard>
      <AppCardBody>
        <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div className="min-w-0">
            <div className="text-base font-medium text-text">
              {hero.greeting}, {hero.userDisplayName}
            </div>
            <div className="mt-1 truncate text-sm text-text-muted">{meta || hero.tenantName}</div>
          </div>

          <div className="flex flex-wrap gap-3">
            {canCreateClient ? (
              <AppButton asChild>
                <Link to="/app/clients/new">Create client</Link>
              </AppButton>
            ) : null}
            <AppButton asChild variant="secondary">
              <Link to="/app/clients">View clients</Link>
            </AppButton>
          </div>
        </div>
      </AppCardBody>
    </AppCard>
  )
}
