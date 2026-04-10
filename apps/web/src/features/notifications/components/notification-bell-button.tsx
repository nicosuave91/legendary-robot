import { useState } from 'react'
import { Bell } from 'lucide-react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { AppBadge, AppButton } from '@/components/ui'
import { NotificationCenterPanel } from '@/features/notifications/components/notification-center-panel'
import { notificationsApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'

export function NotificationBellButton() {
  const [open, setOpen] = useState(false)
  const queryClient = useQueryClient()

  const feedQuery = useQuery({
    queryKey: queryKeys.notifications.feed({}),
    queryFn: () => notificationsApi.list(),
    refetchInterval: 30000
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
  const unread = feedQuery.data?.data.meta.unread ?? 0

  return (
    <>
      <AppButton type="button" variant="secondary" aria-label="Notifications" onClick={() => setOpen(true)}>
        <Bell size={16} />
        Notifications
        {unread ? <AppBadge variant="info">{unread}</AppBadge> : null}
      </AppButton>
      <NotificationCenterPanel
        open={open}
        onOpenChange={setOpen}
        items={items}
        onRead={(notificationId) => readMutation.mutate(notificationId)}
        onDismiss={(notificationId, surface) => dismissMutation.mutate({ notificationId, surface })}
      />
    </>
  )
}
