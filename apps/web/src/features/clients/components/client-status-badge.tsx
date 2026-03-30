import { AppBadge } from '@/components/ui'

type ClientStatusBadgeProps = {
  status: 'lead' | 'active' | 'inactive'
}

export function ClientStatusBadge({ status }: ClientStatusBadgeProps) {
  const variant = status === 'active' ? 'success' : status === 'inactive' ? 'warning' : 'info'
  return <AppBadge variant={variant}>{status}</AppBadge>
}
