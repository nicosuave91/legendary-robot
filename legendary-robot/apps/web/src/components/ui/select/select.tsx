import type { SelectHTMLAttributes } from 'react'
import { forwardRef } from 'react'
import { cn } from '@/lib/utils/cn'

export const AppSelect = forwardRef<HTMLSelectElement, SelectHTMLAttributes<HTMLSelectElement>>(
  ({ className, children, ...props }, ref) => {
    return (
      <select
        ref={ref}
        className={cn(
          'h-10 w-full rounded-md border border-border bg-surface px-3 text-sm text-text shadow-xs outline-none transition motion-base focus:border-ring focus:ring-2 focus:ring-ring/20',
          className
        )}
        {...props}
      >
        {children}
      </select>
    )
  }
)

AppSelect.displayName = 'AppSelect'
