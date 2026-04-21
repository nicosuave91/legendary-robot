import { StatusBadge } from '@/components/ui'

type ClientStatusBadgeProps = {
  status: 'lead' | 'qualified' | 'applied' | 'active' | 'inactive'
}

export function ClientStatusBadge({ status }: ClientStatusBadgeProps) {
  return <StatusBadge status={status} />
}
