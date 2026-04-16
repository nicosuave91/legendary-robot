import type { ReactNode } from 'react'
import { cn } from '@/lib/utils/cn'

type EmptyStateProps = {
  title: string
  description: string
  actions?: ReactNode
  icon?: ReactNode
  compact?: boolean
  className?: string
}

export function EmptyState({
  title,
  description,
  actions,
  icon,
  compact = false,
  className,
}: EmptyStateProps) {
  return (
    <div
      className={cn(
        'rounded-xl border border-dashed border-border bg-surface text-center shadow-xs',
        compact ? 'px-5 py-6' : 'px-6 py-8',
        className,
      )}
    >
      <div className="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-muted text-text-muted">
        {icon ?? '∅'}
      </div>
      <h2 className="heading-lg text-text">{title}</h2>
      <p className="mx-auto mt-2 max-w-md body-md text-text-muted">
        {description}
      </p>
      {actions ? <div className="mt-4 flex justify-center">{actions}</div> : null}
    </div>
  )
}
