import { StatusBadge } from '@/components/ui'
import type { DeliveryStatusProjection } from '@/lib/api/generated/client'

const toneMap = {
  neutral: 'neutral',
  success: 'success',
  warning: 'warning',
  danger: 'danger',
  info: 'info',
} as const

type ToneKey = keyof typeof toneMap

function badgeVariantForTone(tone: DeliveryStatusProjection['tone']) {
  return toneMap[String(tone) as ToneKey] ?? 'neutral'
}

export function CommunicationStatusBadge({ status }: { status: DeliveryStatusProjection }) {
  return (
    <StatusBadge
      status={status.lifecycle}
      label={status.displayLabel}
      variant={badgeVariantForTone(status.tone)}
    />
  )
}
