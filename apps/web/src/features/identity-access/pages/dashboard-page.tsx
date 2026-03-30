import { PageHeader, AppButton, AppCard, AppCardBody, AppCardHeader, EmptyState } from '@/components/ui'
import { ShellFrame } from '@/components/shell/shell-frame'

export function DashboardPage() {
  return (
    <div>
      <PageHeader
        title="Platform foundation"
        description="This route demonstrates the Sprint 1 shell, token system, wrappers, and protected route scaffold."
        actions={<AppButton>Primary action</AppButton>}
      />

      <div className="grid gap-6 xl:grid-cols-2">
        <ShellFrame>
          <AppCard>
            <AppCardHeader>
              <div className="heading-md">Shared shell scaffold</div>
              <div className="body-sm text-text-muted">
                Left navigation, sticky header, search placeholder, notification tray, toasts, and route guards are active.
              </div>
            </AppCardHeader>
            <AppCardBody>
              <EmptyState
                title="Business widgets intentionally deferred"
                description="Homepage analytics, KPI cards, calendar workflows, and pipeline intelligence belong to later epics."
              />
            </AppCardBody>
          </AppCard>
        </ShellFrame>

        <ShellFrame>
          <AppCard>
            <AppCardHeader>
              <div className="heading-md">Auth bootstrap</div>
              <div className="body-sm text-text-muted">
                The browser reads session state from the versioned API contract instead of owning business truth locally.
              </div>
            </AppCardHeader>
            <AppCardBody>
              <ul className="body-md list-disc space-y-2 pl-5 text-text-muted">
                <li>Generated client in use</li>
                <li>Protected routes enforced</li>
                <li>Permissions snapshot reserved for future policy-aware routing</li>
                <li>Onboarding redirection reserved for Sprint 2</li>
              </ul>
            </AppCardBody>
          </AppCard>
        </ShellFrame>
      </div>
    </div>
  )
}
