import type { ComponentProps } from 'react'
import * as DialogPrimitive from '@radix-ui/react-dialog'
import { cn } from '@/lib/utils/cn'

export const AppDialog = DialogPrimitive.Root
export const AppDialogTrigger = DialogPrimitive.Trigger
export const AppDialogClose = DialogPrimitive.Close

export function AppDialogContent({
  className,
  ...props
}: ComponentProps<typeof DialogPrimitive.Content>) {
  return (
    <DialogPrimitive.Portal>
      <DialogPrimitive.Overlay className="fixed inset-0 bg-slate-900/40" />
      <DialogPrimitive.Content
        className={cn(
          'fixed left-1/2 top-1/2 w-full max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-lg border border-border bg-surface p-6 shadow-lg',
          className
        )}
        {...props}
      />
    </DialogPrimitive.Portal>
  )
}
