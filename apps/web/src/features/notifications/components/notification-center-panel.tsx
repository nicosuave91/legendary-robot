import { AppDrawer, AppDrawerContent } from '@/components/ui'
import { NotificationFeedList } from '@/features/notifications/components/notification-feed-list'

type NotificationCenterPanelProps = {
  open: boolean
  onOpenChange: (open: boolean) => void
  items: Array<{
    id: string
    title: string
    body?: string | null
    tone: string
    emittedAt?: string | null
    actionUrl?: string | null
    isRead: boolean
    isDismissed: boolean
  }>
  onRead: (notificationId: string) => void
  onDismiss: (notificationId: string, surface: string) => void
}

export function NotificationCenterPanel({ open, onOpenChange, items, onRead, onDismiss }: NotificationCenterPanelProps) {
  return (
    <AppDrawer open={open} onOpenChange={onOpenChange}>
      <AppDrawerContent className="max-w-[420px] p-0">
        <div className="flex h-full flex-col bg-surface">
          <div className="border-b border-border px-5 py-4">
            <div className="heading-md">Notifications</div>
          </div>
          <div className="flex-1 overflow-y-auto p-5">
            <NotificationFeedList items={items} onRead={onRead} onDismiss={onDismiss} surface="header_center" />
          </div>
        </div>
      </AppDrawerContent>
    </AppDrawer>
  )
}
