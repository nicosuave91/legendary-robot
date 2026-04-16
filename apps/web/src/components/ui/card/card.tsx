import type { ComponentProps, HTMLAttributes } from 'react'
import { CardBase } from '@/components/ui/card/card.base'
import { cn } from '@/lib/utils/cn'

export function AppCard(props: ComponentProps<typeof CardBase>) {
  return <CardBase {...props} />
}

export function AppCardHeader({
  className,
  ...props
}: HTMLAttributes<HTMLDivElement>) {
  return (
    <div
      className={cn('border-b border-border px-4 py-3.5 sm:px-5', className)}
      {...props}
    />
  )
}

export function AppCardBody({
  className,
  ...props
}: HTMLAttributes<HTMLDivElement>) {
  return <div className={cn('px-4 py-4 sm:px-5', className)} {...props} />
}
