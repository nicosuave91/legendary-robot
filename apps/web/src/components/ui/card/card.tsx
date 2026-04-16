import type { ComponentProps, HTMLAttributes } from 'react'
import { cn } from '@/lib/utils/cn'
import { CardBase } from '@/components/ui/card/card.base'

type CardTone = 'primary' | 'secondary' | 'inset' | 'emphasis'
type CardDensity = 'compact' | 'default' | 'comfortable'

const cardToneClasses: Record<CardTone, string> = {
  primary: 'border-border bg-surface shadow-sm',
  secondary: 'border-border/80 bg-background/70 shadow-sm',
  inset: 'border-border/70 bg-muted/35 shadow-none',
  emphasis: 'border-border bg-surface shadow-md',
}

const sectionPadding: Record<CardDensity, string> = {
  compact: 'px-4 py-3',
  default: 'px-5 py-4',
  comfortable: 'px-6 py-5',
}

type AppCardProps = ComponentProps<typeof CardBase> & {
  tone?: CardTone
}

type AppCardSectionProps = HTMLAttributes<HTMLDivElement> & {
  density?: CardDensity
}

export function AppCard({
  className,
  tone = 'primary',
  ...props
}: AppCardProps) {
  return <CardBase className={cn(cardToneClasses[tone], className)} {...props} />
}

export function AppCardHeader({
  className,
  density = 'default',
  ...props
}: AppCardSectionProps) {
  return (
    <div
      className={cn(
        'border-b border-border',
        sectionPadding[density],
        className,
      )}
      {...props}
    />
  )
}

export function AppCardBody({
  className,
  density = 'default',
  ...props
}: AppCardSectionProps) {
  return <div className={cn(sectionPadding[density], className)} {...props} />
}
