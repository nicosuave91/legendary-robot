import { StatusBadge } from '@/components/ui'

export function RuleStatusBadge({ status }: { status?: string | null }) {
  return <StatusBadge status={status ?? 'draft'} />
}
