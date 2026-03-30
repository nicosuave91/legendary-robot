import { PageHeader, AppCard, AppCardBody, AppCardHeader } from '@/components/ui'
import { useAuth } from '@/lib/auth/auth-hooks'

export function SettingsProfilePage() {
  const { data } = useAuth()

  return (
    <div>
      <PageHeader
        title="My Profile"
        description="Profile authority remains server-owned. This view reflects the authenticated bootstrap while richer profile editing stays intentionally narrow."
      />
      <AppCard>
        <AppCardHeader>
          <div className="heading-md">Authenticated profile summary</div>
          <div className="body-sm text-text-muted">
            Full profile maintenance will expand in the tenant governance sprint.
          </div>
        </AppCardHeader>
        <AppCardBody>
          <dl className="grid gap-4 text-sm text-text-muted sm:grid-cols-2">
            <div>
              <dt className="label-sm uppercase tracking-[0.12em]">Display name</dt>
              <dd className="body-md mt-1 text-text">{data?.user.displayName}</dd>
            </div>
            <div>
              <dt className="label-sm uppercase tracking-[0.12em]">Email</dt>
              <dd className="body-md mt-1 text-text">{data?.user.email}</dd>
            </div>
            <div>
              <dt className="label-sm uppercase tracking-[0.12em]">Roles</dt>
              <dd className="body-md mt-1 text-text">{data?.roles.join(', ')}</dd>
            </div>
            <div>
              <dt className="label-sm uppercase tracking-[0.12em]">Selected industry</dt>
              <dd className="body-md mt-1 text-text">{data?.selectedIndustry ?? 'Not assigned'}</dd>
            </div>
          </dl>
        </AppCardBody>
      </AppCard>
    </div>
  )
}
