import type { HTMLAttributes } from 'react'
import { cn } from '@/lib/utils/cn'

type PageCanvasProps = HTMLAttributes<HTMLDivElement> & {
  density?: 'standard' | 'compact'
}

type PageSplitProps = HTMLAttributes<HTMLDivElement> & {
  variant?: 'cockpit' | 'workspace' | 'governance' | 'audit'
}

export function PageCanvas({
  className,
  density = 'standard',
  ...props
}: PageCanvasProps) {
  return (
    <div
      className={cn(
        'mx-auto flex w-full max-w-[1600px] flex-col',
        density === 'compact' ? 'gap-4' : 'gap-5',
        className,
      )}
      {...props}
    />
  )
}

export function PageSplit({
  className,
  variant = 'workspace',
  ...props
}: PageSplitProps) {
  return (
    <div
      className={cn(
        'grid gap-5',
        variant === 'cockpit' &&
          'items-start xl:grid-cols-[minmax(0,1.7fr)_minmax(340px,0.8fr)]',
        variant === 'workspace' &&
          'items-start xl:grid-cols-[minmax(0,1.75fr)_minmax(300px,0.8fr)]',
        variant === 'governance' &&
          'items-start xl:grid-cols-[minmax(0,1.25fr)_420px]',
        variant === 'audit' && 'grid-cols-1',
        className,
      )}
      {...props}
    />
  )
}
