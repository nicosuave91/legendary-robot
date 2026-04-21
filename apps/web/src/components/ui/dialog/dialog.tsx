import type { ComponentProps, HTMLAttributes } from 'react'
import * as DialogPrimitive from '@radix-ui/react-dialog'
import { cn } from '@/lib/utils/cn'

export const AppDialog = DialogPrimitive.Root
export const AppDialogTrigger = DialogPrimitive.Trigger
export const AppDialogClose = DialogPrimitive.Close
export const AppDialogTitle = DialogPrimitive.Title
export const AppDialogDescription = DialogPrimitive.Description

export function AppDialogContent({
  className,
  ...props
}: ComponentProps<typeof DialogPrimitive.Content>) {
  return (
    <DialogPrimitive.Portal>
      <DialogPrimitive.Overlay className="fixed inset-0 z-40 bg-slate-900/40" />
      <DialogPrimitive.Content
        className={cn(
          'fixed left-1/2 top-1/2 z-50 flex max-h-[calc(100dvh-2rem)] w-[calc(100vw-2rem)] max-w-lg -translate-x-1/2 -translate-y-1/2 flex-col overflow-hidden rounded-lg border border-border bg-surface shadow-lg outline-none',
          className,
        )}
        {...props}
      />
    </DialogPrimitive.Portal>
  )
}

export function AppDialogHeader({
  className,
  ...props
}: HTMLAttributes<HTMLDivElement>) {
  return <div className={cn('shrink-0 border-b border-border px-5 py-4', className)} {...props} />
}

export function AppDialogBody({
  className,
  ...props
}: HTMLAttributes<HTMLDivElement>) {
  return <div className={cn('min-h-0 flex-1 overflow-y-auto px-5 py-5', className)} {...props} />
}

export function AppDialogFooter({
  className,
  ...props
}: HTMLAttributes<HTMLDivElement>) {
  return <div className={cn('shrink-0 border-t border-border bg-surface px-5 py-4', className)} {...props} />
}
