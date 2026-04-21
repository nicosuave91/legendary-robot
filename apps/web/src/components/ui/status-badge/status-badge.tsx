import { AppBadge } from '@/components/ui/badge/badge'

type BadgeVariant = 'neutral' | 'info' | 'success' | 'warning' | 'danger'

export type StatusBadgeStatus =
  | 'draft'
  | 'published'
  | 'retired'
  | 'pending'
  | 'queued'
  | 'running'
  | 'waiting'
  | 'success'
  | 'complete'
  | 'completed'
  | 'warning'
  | 'blocked'
  | 'skipped'
  | 'failed'
  | 'rejected'
  | 'danger'
  | 'active'
  | 'inactive'
  | 'lead'
  | 'qualified'
  | 'applied'
  | 'clean'
  | 'quarantined'
  | string

const statusMap: Record<string, { label: string; variant: BadgeVariant }> = {
  draft: { label: 'Draft', variant: 'info' },
  published: { label: 'Published', variant: 'success' },
  retired: { label: 'Retired', variant: 'warning' },
  pending: { label: 'Pending', variant: 'info' },
  queued: { label: 'Queued', variant: 'info' },
  running: { label: 'Running', variant: 'info' },
  waiting: { label: 'Waiting', variant: 'warning' },
  success: { label: 'Success', variant: 'success' },
  complete: { label: 'Complete', variant: 'success' },
  completed: { label: 'Completed', variant: 'success' },
  warning: { label: 'Warning', variant: 'warning' },
  blocked: { label: 'Blocked', variant: 'warning' },
  skipped: { label: 'Skipped', variant: 'neutral' },
  failed: { label: 'Failed', variant: 'danger' },
  rejected: { label: 'Rejected', variant: 'danger' },
  danger: { label: 'Danger', variant: 'danger' },
  active: { label: 'Active', variant: 'success' },
  inactive: { label: 'Inactive', variant: 'warning' },
  lead: { label: 'Lead', variant: 'neutral' },
  qualified: { label: 'Qualified', variant: 'info' },
  applied: { label: 'Applied', variant: 'info' },
  clean: { label: 'Clean', variant: 'success' },
  quarantined: { label: 'Quarantined', variant: 'danger' },
  uploaded: { label: 'Uploaded', variant: 'neutral' },
  validation_queued: { label: 'Validation queued', variant: 'info' },
  validating: { label: 'Validating', variant: 'info' },
  ready_to_commit: { label: 'Ready to commit', variant: 'success' },
  validation_failed: { label: 'Validation failed', variant: 'warning' },
  commit_queued: { label: 'Commit queued', variant: 'info' },
  committing: { label: 'Committing', variant: 'info' },
  committed: { label: 'Committed', variant: 'success' },
  commit_failed: { label: 'Commit failed', variant: 'danger' },
}

function fallbackLabel(status: string) {
  return status
    .replaceAll('_', ' ')
    .replace(/\b\w/g, (character) => character.toUpperCase())
}

export function StatusBadge({
  status,
  label,
  variant,
}: {
  status?: StatusBadgeStatus | null
  label?: string
  variant?: BadgeVariant
}) {
  const normalized = String(status ?? 'draft').toLowerCase()
  const config = statusMap[normalized] ?? { label: fallbackLabel(normalized), variant: 'neutral' as BadgeVariant }

  return <AppBadge variant={variant ?? config.variant}>{label ?? config.label}</AppBadge>
}
