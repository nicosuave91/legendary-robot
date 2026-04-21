import { StatusBadge } from '@/components/ui'

export function ImportStatusBadge({ status }: { status: string }) {
  return <StatusBadge status={status} />
}
