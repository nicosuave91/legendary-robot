import { Link } from 'react-router-dom'
import { AppButton, AppCard, AppCardBody, AppCardHeader } from '@/components/ui'
import type { DashboardSummaryResponse } from '@/lib/api/generated/client'

type HomeHeroCardProps = {
  hero: DashboardSummaryResponse['hero']
  canCreateClient: boolean
}

export function HomeHeroCard({ hero, canCreateClient }: HomeHeroCardProps) {
  return (
    <AppCard>
      <AppCardHeader>
        <div className="label-sm uppercase tracking-[0.14em] text-text-muted">Homepage cockpit</div>
        <div className="heading-lg mt-1 text-text">{hero.greeting}, {hero.userDisplayName}</div>
        <div className="body-sm text-text-muted">{hero.subtitle}</div>
      </AppCardHeader>
      <AppCardBody>
        <div className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
          <div className="grid gap-3 sm:grid-cols-3">
            <div>
              <div className="label-sm uppercase tracking-[0.12em] text-text-muted">Tenant</div>
              <div className="body-md mt-1 text-text">{hero.tenantName}</div>
            </div>
            <div>
              <div className="label-sm uppercase tracking-[0.12em] text-text-muted">Industry</div>
              <div className="body-md mt-1 text-text">{hero.selectedIndustry ?? 'Not assigned'}</div>
            </div>
            <div>
              <div className="label-sm uppercase tracking-[0.12em] text-text-muted">Config version</div>
              <div className="body-md mt-1 text-text">{hero.selectedIndustryConfigVersion ?? 'Not assigned'}</div>
            </div>
          </div>

          <div className="flex flex-wrap gap-3">
            <AppButton asChild>
              <Link to="/app/clients">View clients</Link>
            </AppButton>
            {canCreateClient ? (
              <AppButton asChild variant="secondary">
                <Link to="/app/clients/new">Create client</Link>
              </AppButton>
            ) : null}
          </div>
        </div>
      </AppCardBody>
    </AppCard>
  )
}
