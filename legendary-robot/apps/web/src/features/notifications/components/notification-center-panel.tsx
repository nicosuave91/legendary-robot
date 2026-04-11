import { X } from 'lucide-react'
import { AppButton, AppDrawerClose, LoadingSkeleton } from '@/components/ui'
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
  isLoading?: boolean
  onRead: (notificationId: string) => void
  onDismiss: (notificationId: string, surface: string) => void
}

export function NotificationCenterPanel({ items, isLoading = false, onRead, onDismiss }: NotificationCenterPanelProps) {
  return (
    <div className="flex h-full flex-col bg-surface">
      <div className="flex items-start justify-between gap-4 border-b border-border px-6 py-5">
        <div>
          <div className="heading-md">Notifications</div>
          <div className="body-sm text-text-muted">Important updates that need attention.</div>
        </div>
        <AppDrawerClose asChild>
          <AppButton type="button" variant="ghost" size="sm" aria-label="Close notifications">
            <X size={16} />
          </AppButton>
        </AppDrawerClose>
      </div>

      <div className="flex-1 overflow-y-auto px-6 py-5">
        {isLoading ? <LoadingSkeleton lines={6} /> : null}
        {!isLoading ? (
          <NotificationFeedList items={items} onRead={onRead} onDismiss={onDismiss} surface="drawer" />
        ) : null}
      </div>
    </div>
  )
}
