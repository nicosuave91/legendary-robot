import type { ComponentProps, HTMLAttributes } from 'react'
import * as DialogPrimitive from '@radix-ui/react-dialog'
import { cn } from '@/lib/utils/cn'

export const AppDrawer = DialogPrimitive.Root
export const AppDrawerTrigger = DialogPrimitive.Trigger
export const AppDrawerClose = DialogPrimitive.Close
export const AppDrawerTitle = DialogPrimitive.Title
export const AppDrawerDescription = DialogPrimitive.Description

const widthClasses = {
  sm: 'max-w-sm',
  md: 'max-w-md',
  lg: 'max-w-2xl',
  xl: 'max-w-4xl',
} as const

type AppDrawerContentProps = ComponentProps<typeof DialogPrimitive.Content> & {
  width?: keyof typeof widthClasses
}

export function AppDrawerContent({
  className,
  width = 'md',
  ...props
}: AppDrawerContentProps) {
  return (
    <DialogPrimitive.Portal>
      <DialogPrimitive.Overlay className="fixed inset-0 z-40 bg-slate-900/30" />
      <DialogPrimitive.Content
        className={cn(
          'fixed inset-y-0 right-0 z-50 flex h-dvh w-full flex-col border-l border-border bg-surface shadow-lg outline-none',
          widthClasses[width],
          className,
        )}
        {...props}
      />
    </DialogPrimitive.Portal>
  )
}

export function AppDrawerHeader({
  className,
  ...props
}: HTMLAttributes<HTMLDivElement>) {
  return (
    <div
      className={cn('shrink-0 border-b border-border px-5 py-4', className)}
      {...props}
    />
  )
}

export function AppDrawerBody({
  className,
  ...props
}: HTMLAttributes<HTMLDivElement>) {
  return <div className={cn('min-h-0 flex-1 overflow-y-auto px-5 py-5', className)} {...props} />
}

export function AppDrawerFooter({
  className,
  ...props
}: HTMLAttributes<HTMLDivElement>) {
  return (
    <div
      className={cn('shrink-0 border-t border-border bg-surface px-5 py-4', className)}
      {...props}
    />
  )
}
