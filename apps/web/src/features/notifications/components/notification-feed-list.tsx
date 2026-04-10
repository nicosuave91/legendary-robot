import { AppButton, AppCard, AppCardBody, EmptyState } from '@/components/ui'

type NotificationItem = {
  id: string
  title: string
  body?: string | null
  tone: string
  emittedAt?: string | null
  actionUrl?: string | null
  isRead: boolean
  isDismissed: boolean
}

type NotificationFeedListProps = {
  items: NotificationItem[]
  emptyTitle?: string
  emptyDescription?: string
  onRead?: (notificationId: string) => void
  onDismiss?: (notificationId: string, surface: string) => void
  surface: 'header_center' | 'tray' | 'drawer'
}

export function NotificationFeedList({
  items,
  emptyTitle = 'No notifications',
  emptyDescription = 'You are all caught up.',
  onRead,
  onDismiss,
  surface,
}: NotificationFeedListProps) {
  if (items.length === 0) {
    return <EmptyState title={emptyTitle} description={emptyDescription} />
  }

  return (
    <div className="space-y-3">
      {items.map((item) => (
        <AppCard key={item.id}>
          <AppCardBody>
            <div className="flex items-start justify-between gap-3">
              <div>
                <div className="font-semibold text-text">{item.title}</div>
                <div className="mt-1 text-xs font-medium uppercase tracking-[0.12em] text-text-muted">
                  {item.emittedAt ? new Date(item.emittedAt).toLocaleString() : 'Recent update'}
                </div>
              </div>
              <div className="text-[11px] font-medium uppercase tracking-[0.12em] text-text-muted">{item.tone}</div>
            </div>

            {item.body ? <div className="body-sm mt-3 text-text-muted">{item.body}</div> : null}

            <div className="mt-4 flex flex-wrap gap-2">
              {!item.isRead ? (
                <AppButton type="button" size="sm" variant="secondary" onClick={() => onRead?.(item.id)}>
                  Mark read
                </AppButton>
              ) : null}
              {!item.isDismissed ? (
                <AppButton type="button" size="sm" variant="ghost" onClick={() => onDismiss?.(item.id, surface)}>
                  Dismiss
                </AppButton>
              ) : null}
              {item.actionUrl ? (
                <AppButton type="button" size="sm" variant="ghost" asChild>
                  <a href={item.actionUrl}>Open</a>
                </AppButton>
              ) : null}
            </div>
          </AppCardBody>
        </AppCard>
      ))}
    </div>
  )
}
