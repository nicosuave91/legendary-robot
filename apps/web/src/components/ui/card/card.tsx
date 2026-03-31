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
  return <div className={cn('border-b border-border px-5 py-4', className)} {...props} />
}

export function AppCardBody({
  className,
  ...props
}: HTMLAttributes<HTMLDivElement>) {
  return <div className={cn('px-5 py-4', className)} {...props} />
}
