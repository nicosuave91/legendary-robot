import type { InputHTMLAttributes } from 'react'
import { forwardRef } from 'react'
import { cn } from '@/lib/utils/cn'

export const InputBase = forwardRef<
  HTMLInputElement,
  InputHTMLAttributes<HTMLInputElement>
>(({ className, ...props }, ref) => {
  return (
    <input
      ref={ref}
      className={cn(
        'h-9 w-full rounded-md border border-border bg-surface px-3 text-sm text-text shadow-xs outline-none transition motion-base placeholder:text-text-muted focus:border-ring focus:ring-2 focus:ring-ring/20',
        className,
      )}
      {...props}
    />
  )
})

InputBase.displayName = 'InputBase'
