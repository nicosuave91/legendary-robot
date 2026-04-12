import { Link } from 'react-router-dom'
import { AppButton, AppCard, AppCardBody } from '@/components/ui'
import { useAuth } from '@/lib/auth/auth-hooks'
import type { DashboardSummaryResponse } from '@/lib/api/generated/client'

type HomeHeroCardProps = {
  hero: DashboardSummaryResponse['hero']
  canCreateClient: boolean
}

function formatToday() {
  return new Intl.DateTimeFormat('en-US', {
    weekday: 'long',
    month: 'long',
    day: 'numeric',
  }).format(new Date())
}

export function HomeHeroCard({ hero, canCreateClient }: HomeHeroCardProps) {
  const { data } = useAuth()

  const greeting = hero.greeting?.trim() || 'Good morning'
  const displayName = data?.user.displayName ?? hero.userDisplayName
  const workspaceName = data?.tenant.name ?? hero.tenantName
  const meta = [formatToday(), workspaceName].filter(Boolean).join(' · ')

  return (
    <AppCard>
      <AppCardBody className="py-2">
        <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
          <div className="min-w-0">
            <div className="truncate text-[18px] font-semibold leading-5 text-text">
              {greeting}, {displayName}
            </div>
            <div className="mt-0.5 truncate text-xs leading-4 text-text-muted">
              {meta}
            </div>
          </div>

          <div className="flex flex-wrap gap-2 lg:justify-end">
            {canCreateClient ? (
              <AppButton asChild size="sm">
                <Link to="/app/clients/new">New client</Link>
              </AppButton>
            ) : null}
            <AppButton asChild variant="secondary" size="sm">
              <Link to="/app/clients">View all clients</Link>
            </AppButton>
          </div>
        </div>
      </AppCardBody>
    </AppCard>
  )
}
