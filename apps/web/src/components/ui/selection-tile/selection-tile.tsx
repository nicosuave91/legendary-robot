import type { ButtonHTMLAttributes, ReactNode } from 'react'
import { cn } from '@/lib/utils/cn'

export function AppSelectionTile({
  className,
  title,
  description,
  ...props
}: ButtonHTMLAttributes<HTMLButtonElement> & {
  title: ReactNode
  description?: ReactNode
}) {
  return (
    <button
      type="button"
      className={cn(
        'w-full rounded-lg border border-border bg-surface p-4 text-left transition motion-base hover:border-ring hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/20',
        className
      )}
      {...props}
    >
      <div className="heading-md">{title}</div>
      {description ? <div className="body-sm mt-1 text-text-muted">{description}</div> : null}
    </button>
  )
}
