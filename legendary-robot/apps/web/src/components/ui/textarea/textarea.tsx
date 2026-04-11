import type { TextareaHTMLAttributes } from 'react'
import { forwardRef } from 'react'
import { cn } from '@/lib/utils/cn'

export const AppTextarea = forwardRef<HTMLTextAreaElement, TextareaHTMLAttributes<HTMLTextAreaElement>>(
  ({ className, ...props }, ref) => {
    return (
      <textarea
        ref={ref}
        className={cn(
          'min-h-28 w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text shadow-xs outline-none transition motion-base placeholder:text-text-muted focus:border-ring focus:ring-2 focus:ring-ring/20',
          className
        )}
        {...props}
      />
    )
  }
)

AppTextarea.displayName = 'AppTextarea'
