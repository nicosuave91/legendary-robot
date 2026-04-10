import { AppCard, AppCardBody, AppCardHeader, AppButton, EmptyState } from '@/components/ui'
import type { DashboardProductionResponse } from '@/lib/api/generated/client'

type ProductionLineChartProps = {
  data: DashboardProductionResponse
  window: '7d' | '30d' | '90d'
  onWindowChange: (window: '7d' | '30d' | '90d') => void
}

const chartColors = ['var(--color-primary)', 'var(--color-secondary)', 'var(--color-accent)']

function pointsToPath(values: number[], width: number, height: number) {
  const maxValue = Math.max(...values, 1)
  const stepX = values.length > 1 ? width / (values.length - 1) : width

  return values.map((value, index) => {
    const x = index * stepX
    const y = height - (value / maxValue) * height
    return `${index === 0 ? 'M' : 'L'} ${x.toFixed(2)} ${y.toFixed(2)}`
  }).join(' ')
}

export function ProductionLineChart({ data, window, onWindowChange }: ProductionLineChartProps) {
  const allValues = data.series.flatMap((series) => series.points.map((point) => point.value))
  const hasData = allValues.some((value) => value > 0)

  return (
    <AppCard>
      <AppCardHeader>
        <div className="flex flex-wrap items-center justify-between gap-4">
          <div className="heading-md">Production trend</div>
          <div className="flex flex-wrap items-center gap-3">
            <div className="flex gap-2">
              {(['7d', '30d', '90d'] as const).map((nextWindow) => (
                <AppButton
                  key={nextWindow}
                  type="button"
                  size="sm"
                  variant={window === nextWindow ? 'primary' : 'secondary'}
                  onClick={() => onWindowChange(nextWindow)}
                >
                  {nextWindow}
                </AppButton>
              ))}
            </div>
            <div className="flex flex-wrap gap-3">
              {data.series.map((series, index) => (
                <div key={series.key} className="inline-flex items-center gap-2 text-xs text-text-muted">
                  <span className="inline-block h-[2px] w-4 rounded-full" style={{ backgroundColor: chartColors[index] }} />
                  <span>{series.label}</span>
                </div>
              ))}
            </div>
          </div>
        </div>
      </AppCardHeader>
      <AppCardBody>
        {!hasData ? (
          <EmptyState
            title="No activity yet"
            description="Activity over the selected window will appear here."
          />
        ) : (
          <svg viewBox="0 0 640 180" className="h-44 w-full overflow-visible rounded-lg border border-border bg-muted/30 p-3" role="img" aria-label="Production trend chart">
            {Array.from({ length: 4 }).map((_, index) => {
              const y = 20 + index * 40
              return <line key={index} x1="0" y1={y} x2="640" y2={y} stroke="var(--color-border)" strokeDasharray="4 4" />
            })}
            {data.series.map((series, index) => {
              const values = series.points.map((point) => point.value)
              return (
                <path
                  key={series.key}
                  d={pointsToPath(values, 620, 140)}
                  transform="translate(10 20)"
                  fill="none"
                  stroke={chartColors[index]}
                  strokeWidth="3"
                  strokeLinecap="round"
                />
              )
            })}
          </svg>
        )}
      </AppCardBody>
    </AppCard>
  )
}
