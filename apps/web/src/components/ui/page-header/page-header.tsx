import type { ReactNode } from 'react'
import { cn } from '@/lib/utils/cn'

type PageHeaderVariant =
  | 'cockpit'
  | 'workspace'
  | 'settings'
  | 'governance'
  | 'audit'

type PageHeaderProps = {
  title: string
  description?: string
  eyebrow?: string
  actions?: ReactNode
  secondaryActions?: ReactNode
  statusSummary?: ReactNode
  filterRegion?: ReactNode
  className?: string
  variant?: PageHeaderVariant
}

const wrapperClasses: Record<PageHeaderVariant, string> = {
  cockpit: 'rounded-xl border border-border bg-surface shadow-sm',
  workspace: 'rounded-xl border border-border bg-surface shadow-sm',
  settings: 'rounded-xl border border-border bg-surface shadow-sm',
  governance: 'rounded-xl border border-border bg-surface shadow-sm',
  audit: 'rounded-xl border border-border bg-surface shadow-sm',
}

const titleClasses: Record<PageHeaderVariant, string> = {
  cockpit: 'text-[30px] font-semibold leading-[1.05] tracking-[-0.02em] text-text',
  workspace: 'display-lg text-text',
  settings: 'display-md text-text',
  governance: 'display-lg text-text',
  audit: 'display-lg text-text',
}

export function PageHeader({
  title,
  description,
  eyebrow,
  actions,
  secondaryActions,
  statusSummary,
  filterRegion,
  className,
  variant = 'workspace',
}: PageHeaderProps) {
  return (
    <section className={cn(wrapperClasses[variant], className)}>
      <div className="px-5 py-4 xl:px-6">
        <div className="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
          <div className="min-w-0 space-y-2">
            {eyebrow ? (
              <div className="label-sm uppercase tracking-[0.18em] text-text-muted">
                {eyebrow}
              </div>
            ) : null}
            <h1 className={titleClasses[variant]}>{title}</h1>
            {description ? (
              <p className="max-w-3xl body-md text-text-muted">{description}</p>
            ) : null}
          </div>

          {(statusSummary || actions || secondaryActions) ? (
            <div className="flex w-full flex-col gap-3 xl:w-auto xl:min-w-[260px] xl:items-end">
              {statusSummary ? (
                <div className="flex w-full flex-wrap gap-2 xl:justify-end">
                  {statusSummary}
                </div>
              ) : null}
              {(actions || secondaryActions) ? (
                <div className="flex w-full flex-wrap gap-2 xl:w-auto xl:justify-end">
                  {secondaryActions}
                  {actions}
                </div>
              ) : null}
            </div>
          ) : null}
        </div>

        {filterRegion ? (
          <div className="mt-4 border-t border-border/80 pt-4">{filterRegion}</div>
        ) : null}
      </div>
    </section>
  )
}
