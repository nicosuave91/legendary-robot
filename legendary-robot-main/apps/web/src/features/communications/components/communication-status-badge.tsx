import { AppBadge } from '@/components/ui'
import type { DeliveryStatusProjection } from '@/lib/api/generated/client'

const toneMap: Record<DeliveryStatusProjection['tone'], 'neutral' | 'success' | 'warning' | 'danger'> = {
  neutral: 'neutral',
  success: 'success',
  warning: 'warning',
  danger: 'danger'
}

export function CommunicationStatusBadge({ status }: { status: DeliveryStatusProjection }) {
  return <AppBadge variant={toneMap[status.tone]}>{status.displayLabel}</AppBadge>
}
