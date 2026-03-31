import { AppBadge } from '@/components/ui'

type ClientStatusBadgeProps = {
  status: 'lead' | 'qualified' | 'applied' | 'active' | 'inactive'
}

export function ClientStatusBadge({ status }: ClientStatusBadgeProps) {
  const variant =
    status === 'active'
      ? 'success'
      : status === 'inactive'
        ? 'warning'
        : status === 'qualified' || status === 'applied'
          ? 'info'
          : 'neutral'

  return <AppBadge variant={variant}>{status.replaceAll('_', ' ')}</AppBadge>
}
