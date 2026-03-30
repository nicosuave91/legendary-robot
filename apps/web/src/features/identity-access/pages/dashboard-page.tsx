import { PageHeader, AppCard, AppCardBody, AppCardHeader, EmptyState } from '@/components/ui'
import { ShellFrame } from '@/components/shell/shell-frame'
import { useAuth } from '@/lib/auth/auth-hooks'

export function DashboardPage() {
  const { data } = useAuth()

  return (
    <div>
      <PageHeader
        title="Identity and onboarding foundation"
        description="Sprint 2 keeps authority on the server while the shell reflects authenticated context, permissions, and onboarding completion."
      />

      <div className="grid gap-6 xl:grid-cols-2">
        <ShellFrame>
          <AppCard>
            <AppCardHeader>
              <div className="heading-md">Current auth context</div>
              <div className="body-sm text-text-muted">
                Routed from <code>/api/v1/auth/me</code> through generated clients.
              </div>
            </AppCardHeader>
            <AppCardBody>
              <dl className="grid gap-3 text-sm text-text-muted">
                <div>
                  <dt className="label-sm uppercase tracking-[0.12em]">User</dt>
                  <dd className="body-md mt-1 text-text">{data?.user.displayName}</dd>
                </div>
                <div>
                  <dt className="label-sm uppercase tracking-[0.12em]">Tenant</dt>
                  <dd className="body-md mt-1 text-text">{data?.tenant.name}</dd>
                </div>
                <div>
                  <dt className="label-sm uppercase tracking-[0.12em]">Roles</dt>
                  <dd className="body-md mt-1 text-text">{data?.roles.join(', ')}</dd>
                </div>
                <div>
                  <dt className="label-sm uppercase tracking-[0.12em]">Onboarding state</dt>
                  <dd className="body-md mt-1 text-text">{data?.onboardingState}</dd>
                </div>
              </dl>
            </AppCardBody>
          </AppCard>
        </ShellFrame>

        <ShellFrame>
          <AppCard>
            <AppCardHeader>
              <div className="heading-md">Phase 2 boundaries preserved</div>
              <div className="body-sm text-text-muted">
                This dashboard stays intentionally light so later modules do not leak into Sprint 2.
              </div>
            </AppCardHeader>
            <AppCardBody>
              <EmptyState
                title="Later domain widgets remain deferred"
                description="Homepage analytics, KPI cards, calendar workflows, and client operations stay out of scope until the foundation gate is fully closed."
              />
            </AppCardBody>
          </AppCard>
        </ShellFrame>
      </div>
    </div>
  )
}
