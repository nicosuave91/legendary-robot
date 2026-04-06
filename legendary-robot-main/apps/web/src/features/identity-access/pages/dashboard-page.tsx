import { PageHeader, AppCard, AppCardBody, AppCardHeader, EmptyState } from '@/components/ui'
import { ShellFrame } from '@/components/shell/shell-frame'
import { useAuth } from '@/lib/auth/auth-hooks'

export function DashboardPage() {
  const { data } = useAuth()

  return (
    <div>
      <PageHeader
        title="Tenant governance foundation"
        description="Sprint 3 carries forward server-owned auth and onboarding while adding tenant settings, versioned industry configuration, and branding controls."
      />

      <div className="grid gap-6 xl:grid-cols-2">
        <ShellFrame>
          <AppCard>
            <AppCardHeader>
              <div className="heading-md">Current auth + capability context</div>
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
                  <dt className="label-sm uppercase tracking-[0.12em]">Selected industry</dt>
                  <dd className="body-md mt-1 text-text">
                    {data?.selectedIndustry ? `${data.selectedIndustry} • ${data.selectedIndustryConfigVersion}` : 'Not assigned'}
                  </dd>
                </div>
                <div>
                  <dt className="label-sm uppercase tracking-[0.12em]">Resolved capabilities</dt>
                  <dd className="body-md mt-1 text-text">{data?.capabilities.join(', ') || 'No runtime capabilities yet'}</dd>
                </div>
              </dl>
            </AppCardBody>
          </AppCard>
        </ShellFrame>

        <ShellFrame>
          <AppCard>
            <AppCardHeader>
              <div className="heading-md">Phase 3 gate alignment</div>
              <div className="body-sm text-text-muted">
                Industry-specific runtime behavior now has to resolve from a versioned tenant configuration instead of page flags.
              </div>
            </AppCardHeader>
            <AppCardBody>
              <EmptyState
                title="Later domain widgets remain deferred"
                description="Homepage analytics, client workspace, communications, and workflow domains remain sequenced for later phases."
              />
            </AppCardBody>
          </AppCard>
        </ShellFrame>
      </div>
    </div>
  )
}
