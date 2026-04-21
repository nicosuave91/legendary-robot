import { StatusBadge } from '@/components/ui'
import type { DeliveryStatusProjection } from '@/lib/api/generated/client'

export function CommunicationStatusBadge({ status }: { status: DeliveryStatusProjection }) {
  return <StatusBadge status={status.lifecycle} label={status.displayLabel} variant={status.tone} />
}
