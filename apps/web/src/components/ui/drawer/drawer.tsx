import type { ComponentProps } from 'react'
import * as DialogPrimitive from '@radix-ui/react-dialog'
import { cn } from '@/lib/utils/cn'

export const AppDrawer = DialogPrimitive.Root
export const AppDrawerTrigger = DialogPrimitive.Trigger
export const AppDrawerClose = DialogPrimitive.Close

export function AppDrawerContent({
  className,
  ...props
}: ComponentProps<typeof DialogPrimitive.Content>) {
  return (
    <DialogPrimitive.Portal>
      <DialogPrimitive.Overlay className="fixed inset-0 bg-slate-900/30" />
      <DialogPrimitive.Content
        className={cn(
          'fixed right-0 top-0 h-full w-full max-w-md border-l border-border bg-surface p-6 shadow-lg',
          className
        )}
        {...props}
      />
    </DialogPrimitive.Portal>
  )
}
