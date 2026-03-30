import { PageHeader, AppCard, AppCardBody, AppCardHeader, EmptyState } from '@/components/ui'

export function SettingsProfilePage() {
  return (
    <div>
      <PageHeader
        title="My Profile"
        description="Governed settings surface placeholder. Full profile management remains outside Sprint 1."
      />
      <AppCard>
        <AppCardHeader>
          <div className="heading-md">Profile foundation</div>
          <div className="body-sm text-text-muted">
            Route, shell placement, and authorization posture are established now so later sprints do not restructure navigation.
          </div>
        </AppCardHeader>
        <AppCardBody>
          <EmptyState
            title="Profile editing deferred"
            description="This screen is present as a route scaffold only."
          />
        </AppCardBody>
      </AppCard>
    </div>
  )
}
