import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { AppCard, AppCardBody, AppCardHeader, LoadingSkeleton } from '@/components/ui'
import { NotificationFeedList } from '@/features/notifications/components/notification-feed-list'
import { notificationsApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'

export function NotificationTray() {
  const queryClient = useQueryClient()
  const feedQuery = useQuery({
    queryKey: queryKeys.notifications.feed({}),
    queryFn: () => notificationsApi.list()
  })

  const dismissMutation = useMutation({
    mutationFn: ({ notificationId, surface }: { notificationId: string; surface: string }) => notificationsApi.dismiss(notificationId, { surface }),
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: queryKeys.notifications.all })
    }
  })

  const readMutation = useMutation({
    mutationFn: (notificationId: string) => notificationsApi.read(notificationId),
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: queryKeys.notifications.all })
    }
  })

  const items = feedQuery.data?.data.items ?? []

  return (
    <aside className="hidden w-[360px] border-l border-border bg-background p-4 xl:block">
      <AppCard>
        <AppCardHeader>
          <div className="heading-md">Notification tray</div>
          <div className="body-sm text-text-muted">Persistent operational feedback surfaces here without deleting source-event truth.</div>
        </AppCardHeader>
        <AppCardBody>
          {feedQuery.isLoading ? <LoadingSkeleton lines={6} /> : null}
          {!feedQuery.isLoading ? (
            <NotificationFeedList
              items={items}
              onRead={(notificationId) => readMutation.mutate(notificationId)}
              onDismiss={(notificationId, surface) => dismissMutation.mutate({ notificationId, surface })}
              surface="tray"
            />
          ) : null}
        </AppCardBody>
      </AppCard>
    </aside>
  )
}
