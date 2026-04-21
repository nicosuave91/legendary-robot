import type { ReactNode } from 'react'
import { cn } from '@/lib/utils/cn'

type EmptyStateDensity = 'compact' | 'default'
type EmptyStateTone = 'neutral' | 'info' | 'warning'

type EmptyStateProps = {
  title: string
  description: string
  action?: ReactNode
  secondaryAction?: ReactNode
  icon?: ReactNode
  density?: EmptyStateDensity
  tone?: EmptyStateTone
  className?: string
}

const toneClasses: Record<EmptyStateTone, string> = {
  neutral: 'border-border bg-surface',
  info: 'border-info/35 bg-info/5',
  warning: 'border-warning/35 bg-warning/5',
}

const densityClasses: Record<EmptyStateDensity, string> = {
  compact: 'px-4 py-5',
  default: 'px-5 py-7',
}

export function EmptyState({
  title,
  description,
  action,
  secondaryAction,
  icon = '∅',
  density = 'default',
  tone = 'neutral',
  className,
}: EmptyStateProps) {
  return (
    <div
      className={cn(
        'rounded-lg border border-dashed text-center',
        toneClasses[tone],
        densityClasses[density],
        className,
      )}
    >
      <div className="mx-auto mb-3 flex h-9 w-9 items-center justify-center rounded-md bg-muted text-text-muted">
        {icon}
      </div>
      <h2 className="heading-md text-text">{title}</h2>
      <p className="mx-auto mt-1.5 max-w-md body-sm text-text-muted">
        {description}
      </p>
      {(action || secondaryAction) ? (
        <div className="mt-4 flex flex-wrap items-center justify-center gap-2">
          {secondaryAction}
          {action}
        </div>
      ) : null}
    </div>
  )
}
