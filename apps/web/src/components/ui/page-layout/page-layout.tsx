import type { HTMLAttributes } from 'react'
import { cn } from '@/lib/utils/cn'

export function PageCanvas({
  className,
  ...props
}: HTMLAttributes<HTMLDivElement>) {
  return <div className={cn('space-y-5', className)} {...props} />
}

export function PageGrid({
  className,
  layout = 'workspace',
  ...props
}: HTMLAttributes<HTMLDivElement> & {
  layout?: 'cockpit' | 'workspace' | 'settings' | 'governance' | 'audit'
}) {
  const layoutClasses = {
    cockpit:
      'grid gap-5 xl:grid-cols-[minmax(0,1.32fr)_minmax(320px,0.92fr)]',
    workspace:
      'grid gap-5 xl:grid-cols-[minmax(0,1.22fr)_minmax(320px,0.78fr)]',
    settings:
      'grid gap-5 xl:grid-cols-[minmax(0,1.24fr)_minmax(320px,0.76fr)]',
    governance:
      'grid gap-5 xl:grid-cols-[minmax(0,1.26fr)_minmax(360px,0.84fr)]',
    audit: 'grid gap-5',
  } as const

  return (
    <div className={cn(layoutClasses[layout], className)} {...props} />
  )
}
