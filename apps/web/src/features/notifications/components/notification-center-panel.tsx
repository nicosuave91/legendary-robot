import { NotificationFeedList } from '@/features/notifications/components/notification-feed-list'

type NotificationCenterPanelProps = {
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

export function NotificationCenterPanel({ items, onRead, onDismiss }: NotificationCenterPanelProps) {
  return (
    <div className="absolute right-0 top-12 z-20 w-[380px] max-w-[calc(100vw-2rem)] rounded-lg border border-border bg-background p-4 shadow-lg">
      <div className="mb-4">
        <div className="heading-md">Notification center</div>
        <div className="body-sm text-text-muted">Dismissal hides the current user surface but preserves the source event.</div>
      </div>
      <NotificationFeedList items={items} onRead={onRead} onDismiss={onDismiss} surface="header_center" />
    </div>
  )
}
