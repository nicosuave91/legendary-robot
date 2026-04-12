import { useMemo, useState } from 'react'
import { AppButton, AppCard, AppCardBody, AppCardHeader } from '@/components/ui'
import type { DashboardProductionResponse } from '@/lib/api/generated/client'

type ProductionLineChartProps = {
  data: DashboardProductionResponse
  window: '7d' | '30d' | '90d'
  onWindowChange: (window: '7d' | '30d' | '90d') => void
}

type SeriesPoint = DashboardProductionResponse['series'][number]['points'][number]

const chartColors = [
  'var(--color-primary)',
  'var(--color-secondary)',
  'var(--color-accent)',
]

const svgWidth = 960
const svgHeight = 360

const chartMargin = {
  top: 28,
  right: 24,
  bottom: 52,
  left: 60,
}

function clamp(value: number, min: number, max: number) {
  return Math.min(Math.max(value, min), max)
}

function niceAxisMax(rawMax: number) {
  if (rawMax <= 4) {
    return 4
  }

  const magnitude = 10 ** Math.floor(Math.log10(rawMax))
  const residual = rawMax / magnitude

  if (residual <= 1) {
    return magnitude
  }

  if (residual <= 2) {
    return 2 * magnitude
  }

  if (residual <= 5) {
    return 5 * magnitude
  }

  return 10 * magnitude
}

function buildYTicks(axisMax: number) {
  return Array.from({ length: 5 }, (_, index) => {
    const value = (axisMax / 4) * (4 - index)
    return {
      value,
      label: Number.isInteger(value) ? value.toString() : value.toFixed(1),
    }
  })
}

function buildTickIndexes(length: number, desiredCount: number) {
  if (length <= 1) {
    return [0]
  }

  const indexes = new Set<number>([0, length - 1])
  const step = (length - 1) / Math.max(desiredCount - 1, 1)

  for (let index = 0; index < desiredCount; index++) {
    indexes.add(Math.round(index * step))
  }

  return Array.from(indexes).sort((left, right) => left - right)
}

function formatTickDate(date: string, window: '7d' | '30d' | '90d') {
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: window === '90d' ? 'numeric' : 'numeric',
  }).format(new Date(`${date}T12:00:00Z`))
}

function formatTooltipDate(date: string) {
  return new Intl.DateTimeFormat('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  }).format(new Date(`${date}T12:00:00Z`))
}

function toSmoothPath(points: Array<{ x: number; y: number }>) {
  if (points.length === 0) {
    return ''
  }

  if (points.length === 1) {
    return `M ${points[0].x.toFixed(2)} ${points[0].y.toFixed(2)}`
  }

  let path = `M ${points[0].x.toFixed(2)} ${points[0].y.toFixed(2)}`

  for (let index = 0; index < points.length - 1; index++) {
    const previousPoint = points[index - 1] ?? points[index]
    const currentPoint = points[index]
    const nextPoint = points[index + 1]
    const followingPoint = points[index + 2] ?? nextPoint

    const controlPoint1X = currentPoint.x + (nextPoint.x - previousPoint.x) / 6
    const controlPoint1Y = currentPoint.y + (nextPoint.y - previousPoint.y) / 6
    const controlPoint2X = nextPoint.x - (followingPoint.x - currentPoint.x) / 6
    const controlPoint2Y = nextPoint.y - (followingPoint.y - currentPoint.y) / 6

    path += ` C ${controlPoint1X.toFixed(2)} ${controlPoint1Y.toFixed(2)}, ${controlPoint2X.toFixed(2)} ${controlPoint2Y.toFixed(2)}, ${nextPoint.x.toFixed(2)} ${nextPoint.y.toFixed(2)}`
  }

  return path
}

export function ProductionLineChart({
  data,
  window,
  onWindowChange,
}: ProductionLineChartProps) {
  const [hoveredIndex, setHoveredIndex] = useState<number | null>(null)

  const referencePoints = data.series[0]?.points ?? []
  const allValues = data.series.flatMap((series) => series.points.map((point) => point.value))
  const rawMax = Math.max(...allValues, 0)
  const activeBucketCount = referencePoints.reduce((count, _, index) => {
    const total = data.series.reduce(
      (seriesTotal, series) => seriesTotal + (series.points[index]?.value ?? 0),
      0,
    )

    return count + (total > 0 ? 1 : 0)
  }, 0)

  const hasData = rawMax > 0
  const hasTrendData = activeBucketCount >= 3

  const chartModel = useMemo(() => {
    const axisMax = niceAxisMax(rawMax || 1)
    const plotWidth = svgWidth - chartMargin.left - chartMargin.right
    const plotHeight = svgHeight - chartMargin.top - chartMargin.bottom
    const tickIndexes = buildTickIndexes(
      referencePoints.length,
      window === '7d' ? 7 : window === '30d' ? 6 : 7,
    )

    const series = data.series.map((series, index) => {
      const coordinates = series.points.map((point, pointIndex) => {
        const x =
          referencePoints.length > 1
            ? chartMargin.left + (pointIndex / (referencePoints.length - 1)) * plotWidth
            : chartMargin.left + plotWidth / 2

        const y =
          chartMargin.top +
          plotHeight -
          ((point.value ?? 0) / axisMax) * plotHeight

        return {
          ...point,
          x,
          y,
        }
      })

      return {
        ...series,
        color: chartColors[index % chartColors.length],
        coordinates,
      }
    })

    const hoverPoints = referencePoints.map((point, index) => ({
      bucketDate: point.bucketDate,
      x: series[0]?.coordinates[index]?.x ?? chartMargin.left,
      values: series.map((series) => ({
        label: series.label,
        color: series.color,
        value: series.points[index]?.value ?? 0,
      })),
    }))

    return {
      axisMax,
      plotWidth,
      plotHeight,
      tickIndexes,
      hoverPoints,
      series,
      yTicks: buildYTicks(axisMax),
    }
  }, [data.series, rawMax, referencePoints, window])

  const hoveredPoint =
    hoveredIndex === null ? null : chartModel.hoverPoints[hoveredIndex] ?? null

  return (
    <AppCard>
      <AppCardHeader>
        <div className="flex flex-col gap-4">
          <div className="flex flex-wrap items-start justify-between gap-3">
            <div>
              <div className="heading-md">Production</div>
              <div className="mt-1 body-sm text-text-muted">
                Daily client, note, and document activity across the selected range.
              </div>
            </div>

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
          </div>

          <div className="flex flex-wrap items-center gap-3">
            {data.series.map((series, index) => (
              <div
                key={series.key}
                className="inline-flex items-center gap-2 rounded-full border border-border bg-surface px-3 py-1.5 text-xs"
              >
                <span
                  className="inline-block h-2.5 w-2.5 rounded-full"
                  style={{ backgroundColor: chartColors[index % chartColors.length] }}
                />
                <span className="text-text">{series.label}</span>
                <span className="text-text-muted">
                  {series.points.reduce((total, point) => total + point.value, 0)} total
                </span>
              </div>
            ))}
          </div>
        </div>
      </AppCardHeader>

      <AppCardBody>
        {!hasData ? (
          <div className="rounded-xl border border-dashed border-border bg-muted/20 px-6 py-10 text-center">
            <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-muted text-text-muted">
              ∅
            </div>
            <div className="text-lg font-semibold text-text">No production data yet</div>
            <p className="mx-auto mt-2 max-w-2xl text-sm text-text-muted">
              This chart begins plotting activity after clients are created, notes are added,
              or documents are uploaded. Once the selected range contains production activity,
              you can use hover to inspect each day and compare the trend lines.
            </p>
          </div>
        ) : !hasTrendData ? (
          <div className="rounded-xl border border-dashed border-border bg-muted/20 px-6 py-10 text-center">
            <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-muted text-text-muted">
              ↗
            </div>
            <div className="text-lg font-semibold text-text">Trend data is still building</div>
            <p className="mx-auto mt-2 max-w-2xl text-sm text-text-muted">
              We need activity on at least three different days before this surface renders a
              reliable trend. That prevents a single-day spike from appearing as a misleading
              triangle. Continue working in the workspace or load the seeded records to populate
              the production history.
            </p>
          </div>
        ) : (
          <div className="relative rounded-xl border border-border bg-muted/20 p-4">
            <div className="mb-2 flex items-center justify-between text-xs text-text-muted">
              <span>Y-axis · Daily activity</span>
              <span>X-axis · Date</span>
            </div>

            <svg
              viewBox={`0 0 ${svgWidth} ${svgHeight}`}
              className="h-[320px] w-full"
              role="img"
              aria-label="Production chart with daily activity values over time"
            >
              <line
                x1={chartMargin.left}
                y1={chartMargin.top}
                x2={chartMargin.left}
                y2={svgHeight - chartMargin.bottom}
                stroke="var(--color-border)"
              />
              <line
                x1={chartMargin.left}
                y1={svgHeight - chartMargin.bottom}
                x2={svgWidth - chartMargin.right}
                y2={svgHeight - chartMargin.bottom}
                stroke="var(--color-border)"
              />

              {chartModel.yTicks.map((tick) => {
                const y =
                  chartMargin.top +
                  ((chartModel.axisMax - tick.value) / chartModel.axisMax) *
                    chartModel.plotHeight

                return (
                  <g key={tick.label}>
                    <line
                      x1={chartMargin.left}
                      y1={y}
                      x2={svgWidth - chartMargin.right}
                      y2={y}
                      stroke="var(--color-border)"
                      strokeDasharray="4 4"
                    />
                    <text
                      x={chartMargin.left - 12}
                      y={y + 4}
                      textAnchor="end"
                      fontSize="11"
                      fill="var(--color-text-muted)"
                    >
                      {tick.label}
                    </text>
                  </g>
                )
              })}

              {chartModel.tickIndexes.map((index) => {
                const point = referencePoints[index]
                if (!point) {
                  return null
                }

                const x =
                  referencePoints.length > 1
                    ? chartMargin.left +
                      (index / (referencePoints.length - 1)) * chartModel.plotWidth
                    : chartMargin.left + chartModel.plotWidth / 2

                return (
                  <g key={`${point.bucketDate}-${index}`}>
                    <line
                      x1={x}
                      y1={svgHeight - chartMargin.bottom}
                      x2={x}
                      y2={svgHeight - chartMargin.bottom + 6}
                      stroke="var(--color-border)"
                    />
                    <text
                      x={x}
                      y={svgHeight - chartMargin.bottom + 20}
                      textAnchor="middle"
                      fontSize="11"
                      fill="var(--color-text-muted)"
                    >
                      {formatTickDate(point.bucketDate, window)}
                    </text>
                  </g>
                )
              })}

              {hoveredPoint ? (
                <line
                  x1={hoveredPoint.x}
                  y1={chartMargin.top}
                  x2={hoveredPoint.x}
                  y2={svgHeight - chartMargin.bottom}
                  stroke="var(--color-border)"
                  strokeDasharray="6 4"
                />
              ) : null}

              {chartModel.series.map((series) => (
                <g key={series.key}>
                  <path
                    d={toSmoothPath(series.coordinates)}
                    fill="none"
                    stroke={series.color}
                    strokeWidth="3"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                  />
                  {hoveredIndex !== null && series.coordinates[hoveredIndex] ? (
                    <circle
                      cx={series.coordinates[hoveredIndex].x}
                      cy={series.coordinates[hoveredIndex].y}
                      r="5"
                      fill={series.color}
                      stroke="var(--color-surface)"
                      strokeWidth="2"
                    />
                  ) : null}
                </g>
              ))}

              <rect
                x={chartMargin.left}
                y={chartMargin.top}
                width={chartModel.plotWidth}
                height={chartModel.plotHeight}
                fill="transparent"
                onMouseMove={(event) => {
                  const bounds = event.currentTarget.getBoundingClientRect()
                  const relativeX = clamp(
                    (event.clientX - bounds.left) / bounds.width,
                    0,
                    1,
                  )

                  const index = Math.round(relativeX * (referencePoints.length - 1))
                  setHoveredIndex(index)
                }}
                onMouseLeave={() => setHoveredIndex(null)}
              />
            </svg>

            {hoveredPoint ? (
              <div
                className="pointer-events-none absolute top-4 z-10 w-52 rounded-lg border border-border bg-surface px-3 py-2 shadow-lg"
                style={{
                  left: `${(hoveredPoint.x / svgWidth) * 100}%`,
                  transform:
                    hoveredPoint.x > svgWidth * 0.72
                      ? 'translateX(-100%)'
                      : 'translateX(0)',
                }}
              >
                <div className="text-xs font-medium text-text">
                  {formatTooltipDate(hoveredPoint.bucketDate)}
                </div>
                <div className="mt-2 space-y-1.5">
                  {hoveredPoint.values.map((value) => (
                    <div
                      key={value.label}
                      className="flex items-center justify-between gap-3 text-xs"
                    >
                      <div className="flex items-center gap-2 text-text-muted">
                        <span
                          className="inline-block h-2.5 w-2.5 rounded-full"
                          style={{ backgroundColor: value.color }}
                        />
                        <span>{value.label}</span>
                      </div>
                      <span className="font-medium text-text">{value.value}</span>
                    </div>
                  ))}
                </div>
              </div>
            ) : null}
          </div>
        )}
      </AppCardBody>
    </AppCard>
  )
}
