import { ArrowRight, Minus, TrendingDown, TrendingUp } from 'lucide-react'
import { Link } from 'react-router-dom'
import { AppCard, AppCardBody } from '@/components/ui'
import type { DashboardKpiCard } from '@/lib/api/generated/client'

type KpiCardProps = {
  card: DashboardKpiCard
}



function DeltaIcon({ direction }: { direction: DashboardKpiCard['delta']['direction'] }) {
  if (direction === 'up') return <TrendingUp size={14} />
  if (direction === 'down') return <TrendingDown size={14} />
  return <Minus size={14} />
}

export function KpiCard({ card }: KpiCardProps) {
  return (
    <AppCard>
      <AppCardBody>
        <Link to={card.href} className="block space-y-3">
          <div className="flex items-start justify-between gap-3">
            <div>
              <div className="label-sm uppercase tracking-[0.12em] text-text-muted">{card.label}</div>
              <div className="display-sm mt-2 text-text">{card.value}</div>
            </div>
            <ArrowRight size={16} className="mt-1 text-text-muted" />
          </div>
          <div className="body-sm text-text-muted">{card.description}</div>
          <div className="inline-flex items-center gap-2 rounded-full bg-muted px-3 py-1 text-xs font-medium text-text-muted">
            <DeltaIcon direction={card.delta.direction} />
            <span>{card.delta.label}</span>
          </div>
        </Link>
      </AppCardBody>
    </AppCard>
  )
}


