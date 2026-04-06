import { AppBadge } from '@/components/ui'

export function RuleStatusBadge({ status }: { status?: string | null }) {
  if (status === 'published') return <AppBadge variant="success">Published</AppBadge>
  if (status === 'retired') return <AppBadge variant="warning">Retired</AppBadge>
  return <AppBadge variant="info">Draft</AppBadge>
}
