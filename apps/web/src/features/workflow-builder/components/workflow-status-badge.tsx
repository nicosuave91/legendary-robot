import { StatusBadge } from '@/components/ui'

export function WorkflowStatusBadge({ status }: { status?: string | null }) {
  return <StatusBadge status={status ?? 'draft'} />
}
