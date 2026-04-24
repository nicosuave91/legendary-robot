import type { HTMLAttributes, ReactNode } from 'react'
import { AppBadge } from '@/components/ui/badge/badge'
import { cn } from '@/lib/utils/cn'

type BadgeVariant = 'neutral' | 'info' | 'success' | 'warning' | 'danger'
export type StatusTone = BadgeVariant
export type StatusBadgeShape = 'circle' | 'square' | 'triangle' | 'diamond' | 'dash'
export type StatusBadgeSize = 'xs' | 'sm' | 'md'

export type StatusBadgeStatus =
  | 'draft'
  | 'published'
  | 'retired'
  | 'pending'
  | 'queued'
  | 'running'
  | 'waiting'
  | 'scheduled'
  | 'cancelled'
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
  | 'uploaded'
  | 'validation_queued'
  | 'validating'
  | 'ready_to_commit'
  | 'validation_failed'
  | 'commit_queued'
  | 'committing'
  | 'committed'
  | 'commit_failed'
  | string

const statusMap: Record<string, { label: string; variant: BadgeVariant; shape?: StatusBadgeShape }> = {
  draft: { label: 'Draft', variant: 'info', shape: 'dash' },
  published: { label: 'Published', variant: 'success', shape: 'circle' },
  retired: { label: 'Retired', variant: 'warning', shape: 'dash' },

  pending: { label: 'Pending', variant: 'info', shape: 'circle' },
  queued: { label: 'Queued', variant: 'info', shape: 'circle' },
  running: { label: 'Running', variant: 'info', shape: 'circle' },
  waiting: { label: 'Waiting', variant: 'warning', shape: 'triangle' },
  scheduled: { label: 'Scheduled', variant: 'info', shape: 'circle' },
  cancelled: { label: 'Cancelled', variant: 'warning', shape: 'dash' },

  success: { label: 'Success', variant: 'success', shape: 'circle' },
  complete: { label: 'Complete', variant: 'success', shape: 'circle' },
  completed: { label: 'Completed', variant: 'success', shape: 'circle' },

  warning: { label: 'Warning', variant: 'warning', shape: 'triangle' },
  blocked: { label: 'Blocked', variant: 'warning', shape: 'triangle' },
  skipped: { label: 'Skipped', variant: 'neutral', shape: 'dash' },

  failed: { label: 'Failed', variant: 'danger', shape: 'square' },
  rejected: { label: 'Rejected', variant: 'danger', shape: 'square' },
  danger: { label: 'Danger', variant: 'danger', shape: 'square' },

  active: { label: 'Active', variant: 'success', shape: 'circle' },
  inactive: { label: 'Inactive', variant: 'neutral', shape: 'dash' },

  lead: { label: 'Lead', variant: 'neutral', shape: 'circle' },
  qualified: { label: 'Qualified', variant: 'info', shape: 'diamond' },
  applied: { label: 'Applied', variant: 'info', shape: 'diamond' },

  clean: { label: 'Clean', variant: 'success', shape: 'circle' },
  quarantined: { label: 'Quarantined', variant: 'danger', shape: 'square' },

  uploaded: { label: 'Uploaded', variant: 'neutral', shape: 'circle' },
  validation_queued: { label: 'Validation queued', variant: 'info', shape: 'circle' },
  validating: { label: 'Validating', variant: 'info', shape: 'circle' },
  ready_to_commit: { label: 'Ready to commit', variant: 'success', shape: 'circle' },
  validation_failed: { label: 'Validation failed', variant: 'warning', shape: 'triangle' },
  commit_queued: { label: 'Commit queued', variant: 'info', shape: 'circle' },
  committing: { label: 'Committing', variant: 'info', shape: 'circle' },
  committed: { label: 'Committed', variant: 'success', shape: 'circle' },
  commit_failed: { label: 'Commit failed', variant: 'danger', shape: 'square' },
}

const shapeClasses: Record<StatusBadgeShape, string> = {
  circle: 'rounded-full',
  square: 'rounded-[3px]',
  triangle: 'rounded-[2px] rotate-45',
  diamond: 'rounded-[2px] rotate-45',
  dash: 'rounded-[2px] w-2.5 h-0.5',
}

const sizeClasses: Record<StatusBadgeSize, string> = {
  xs: 'px-2 py-0.5 text-[11px]',
  sm: 'px-2.5 py-1 text-xs',
  md: 'px-3 py-1.5 text-sm',
}

function fallbackLabel(status: string) {
  return status
    .replaceAll('_', ' ')
    .replace(/\b\w/g, (character) => character.toUpperCase())
}

type AppStatusBadgeProps = HTMLAttributes<HTMLSpanElement> & {
  tone?: StatusTone
  variant?: StatusTone
  label?: ReactNode
  icon?: ReactNode
  children?: ReactNode
  shape?: StatusBadgeShape
  size?: StatusBadgeSize
}

export function AppStatusBadge({
  className,
  tone,
  variant,
  label,
  icon,
  children,
  shape = 'circle',
  size = 'sm',
  ...props
}: AppStatusBadgeProps) {
  return (
    <AppBadge
      variant={variant ?? tone ?? 'neutral'}
      className={cn('gap-1.5', sizeClasses[size], className)}
      {...props}
    >
      {icon ?? (
        <span
          aria-hidden="true"
          className={cn('inline-block h-1.5 w-1.5 shrink-0 bg-current', shapeClasses[shape])}
        />
      )}
      {label ?? children}
    </AppBadge>
  )
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
  const config =
    statusMap[normalized] ?? {
      label: fallbackLabel(normalized),
      variant: 'neutral' as BadgeVariant,
      shape: 'circle' as StatusBadgeShape,
    }

  return (
    <AppStatusBadge tone={variant ?? config.variant} shape={config.shape} label={label ?? config.label} />
  )
}
