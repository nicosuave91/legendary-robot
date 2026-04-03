import { AppBadge } from '@/components/ui'

export function WorkflowStatusBadge({ status }: { status?: string | null }) {
  if (status === 'completed') return <AppBadge variant="success">Completed</AppBadge>
  if (status === 'running') return <AppBadge variant="info">Running</AppBadge>
  if (status === 'waiting') return <AppBadge variant="warning">Waiting</AppBadge>
  if (status === 'failed') return <AppBadge variant="danger">Failed</AppBadge>
  if (status === 'published') return <AppBadge variant="success">Published</AppBadge>
  if (status === 'retired') return <AppBadge variant="warning">Retired</AppBadge>
  return <AppBadge variant="info">Draft</AppBadge>
}
