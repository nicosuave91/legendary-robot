import type { ComponentProps } from 'react'
import * as TabsPrimitive from '@radix-ui/react-tabs'
import { cn } from '@/lib/utils/cn'

export const AppTabs = TabsPrimitive.Root

export function AppTabsList({
  className,
  ...props
}: ComponentProps<typeof TabsPrimitive.List>) {
  return (
    <TabsPrimitive.List
      className={cn('inline-flex gap-2 rounded-lg bg-muted p-1', className)}
      {...props}
    />
  )
}

export function AppTabsTrigger({
  className,
  ...props
}: ComponentProps<typeof TabsPrimitive.Trigger>) {
  return (
    <TabsPrimitive.Trigger
      className={cn(
        'rounded-md px-3 py-2 text-sm font-medium text-text-muted data-[state=active]:bg-surface data-[state=active]:text-text',
        className
      )}
      {...props}
    />
  )
}

export const AppTabsContent = TabsPrimitive.Content
