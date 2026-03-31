import { ReactNode } from 'react'
import { cn } from '@/lib/utils/cn'

type PageHeaderProps = {
  title: string
  description?: string
  actions?: ReactNode
  className?: string
}

export function PageHeader({ title, description, actions, className }: PageHeaderProps) {
  return (
    <div className={cn('mb-6 flex items-start justify-between gap-4', className)}>
      <div className="space-y-2">
        <h1 className="display-md text-text">{title}</h1>
        {description ? <p className="body-md text-text-muted">{description}</p> : null}
      </div>
      {actions ? <div className="flex items-center gap-2">{actions}</div> : null}
    </div>
  )
}
