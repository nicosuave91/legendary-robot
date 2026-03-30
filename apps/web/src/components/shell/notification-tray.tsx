import { AppCard, AppCardBody, AppCardHeader, EmptyState } from '@/components/ui'

export function NotificationTray() {
  return (
    <aside className="hidden w-[320px] border-l border-border bg-background p-4 xl:block">
      <AppCard>
        <AppCardHeader>
          <div className="heading-md">Notification tray</div>
          <div className="body-sm text-text-muted">Sprint 1 scaffold only</div>
        </AppCardHeader>
        <AppCardBody>
          <EmptyState
            title="No notifications yet"
            description="Persistent business notifications are intentionally out of scope for Sprint 1."
          />
        </AppCardBody>
      </AppCard>
    </aside>
  )
}
