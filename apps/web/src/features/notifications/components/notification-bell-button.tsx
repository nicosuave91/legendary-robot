import { Bell } from 'lucide-react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  AppBadge,
  AppButton,
  AppDrawer,
  AppDrawerContent,
  AppDrawerTrigger,
} from '@/components/ui'
import { NotificationCenterPanel } from '@/features/notifications/components/notification-center-panel'
import { notificationsApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'

export function NotificationBellButton() {
  const queryClient = useQueryClient()

  const feedQuery = useQuery({
    queryKey: queryKeys.notifications.feed({}),
    queryFn: () => notificationsApi.list(),
    refetchInterval: 30000,
  })

  const dismissMutation = useMutation({
    mutationFn: ({
      notificationId,
      surface,
    }: {
      notificationId: string
      surface: string
    }) => notificationsApi.dismiss(notificationId, { surface }),
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: queryKeys.notifications.all })
    },
  })

  const readMutation = useMutation({
    mutationFn: (notificationId: string) => notificationsApi.read(notificationId),
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: queryKeys.notifications.all })
    },
  })

  const items = feedQuery.data?.data.items ?? []
  const unread = feedQuery.data?.data.meta.unread ?? 0

  return (
    <AppDrawer>
      <AppDrawerTrigger asChild>
        <AppButton
          type="button"
          variant="secondary"
          aria-label={`Notifications (${unread} unread)`}
        >
          <span className="relative inline-flex">
            <Bell size={16} />
            <AppBadge
              variant={unread > 0 ? 'info' : 'neutral'}
              className="absolute -right-2 -top-2 inline-flex h-5 min-w-[20px] items-center justify-center px-1.5 py-0 text-[10px] font-semibold leading-none"
            >
              {unread}
            </AppBadge>
          </span>
          Notifications
        </AppButton>
      </AppDrawerTrigger>
      <AppDrawerContent className="max-w-[420px] p-0">
        <NotificationCenterPanel
          items={items}
          isLoading={feedQuery.isLoading}
          onRead={(notificationId) => readMutation.mutate(notificationId)}
          onDismiss={(notificationId, surface) =>
            dismissMutation.mutate({ notificationId, surface })
          }
        />
      </AppDrawerContent>
    </AppDrawer>
  )
}
