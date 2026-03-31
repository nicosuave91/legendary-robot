import { AppBadge } from '@/components/ui'

const variantMap = {
  uploaded: 'neutral',
  validation_queued: 'info',
  validating: 'info',
  ready_to_commit: 'success',
  validation_failed: 'warning',
  commit_queued: 'info',
  committing: 'info',
  committed: 'success',
  commit_failed: 'danger'
} as const

export function ImportStatusBadge({ status }: { status: keyof typeof variantMap | string }) {
  const variant = variantMap[status as keyof typeof variantMap] ?? 'neutral'
  return <AppBadge variant={variant}>{status.replaceAll('_', ' ')}</AppBadge>
}
