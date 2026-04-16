import type { ReactNode } from 'react'
import { cn } from '@/lib/utils/cn'

export type PageHeaderVariant =
  | 'cockpit'
  | 'workspace'
  | 'settings'
  | 'governance'
  | 'audit'

type PageHeaderProps = {
  title: string
  description?: string
  actions?: ReactNode
  className?: string
  variant?: PageHeaderVariant
  eyebrow?: string
  breadcrumb?: string
  status?: ReactNode
  filters?: ReactNode
}

const variantClasses: Record<PageHeaderVariant, string> = {
  cockpit: 'border-primary/20 bg-surface shadow-xs',
  workspace: 'border-border bg-surface shadow-xs',
  settings: 'border-border bg-surface/95 shadow-xs',
  governance: 'border-warning/30 bg-surface shadow-xs',
  audit: 'border-danger/30 bg-surface shadow-xs',
}

const eyebrowClasses: Record<PageHeaderVariant, string> = {
  cockpit: 'text-primary',
  workspace: 'text-text-muted',
  settings: 'text-text-muted',
  governance: 'text-warning',
  audit: 'text-danger',
}

export function PageHeader({
  title,
  description,
  actions,
  className,
  variant = 'workspace',
  eyebrow,
  breadcrumb,
  status,
  filters,
}: PageHeaderProps) {
  return (
    <div className={cn('overflow-hidden rounded-2xl border', variantClasses[variant], className)}>
      <div className="flex flex-col gap-4 p-5 lg:flex-row lg:items-start lg:justify-between">
        <div className="min-w-0 space-y-3">
          {eyebrow || breadcrumb ? (
            <div className="flex flex-wrap items-center gap-2 text-xs uppercase tracking-[0.16em]">
              {eyebrow ? (
                <span className={cn('font-semibold', eyebrowClasses[variant])}>
                  {eyebrow}
                </span>
              ) : null}
              {eyebrow && breadcrumb ? (
                <span className="text-text-muted">/</span>
              ) : null}
              {breadcrumb ? (
                <span className="text-text-muted">{breadcrumb}</span>
              ) : null}
            </div>
          ) : null}

          <div className="space-y-2">
            <h1 className="display-md text-text">{title}</h1>
            {description ? (
              <p className="max-w-3xl body-md text-text-muted">{description}</p>
            ) : null}
          </div>

          {status ? <div className="flex flex-wrap items-center gap-2">{status}</div> : null}
        </div>

        {actions ? (
          <div className="flex shrink-0 flex-wrap items-center gap-2">
            {actions}
          </div>
        ) : null}
      </div>

      {filters ? (
        <div className="border-t border-border bg-muted/35 px-5 py-3">{filters}</div>
      ) : null}
    </div>
  )
}
