import type { TableHTMLAttributes } from 'react'
import { cn } from '@/lib/utils/cn'

export function AppTable({
  className,
  ...props
}: TableHTMLAttributes<HTMLTableElement>) {
  return (
    <div className="overflow-hidden rounded-lg border border-border bg-surface shadow-sm">
      <table className={cn('min-w-full divide-y divide-border text-sm', className)} {...props} />
    </div>
  )
}
