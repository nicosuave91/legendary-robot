import { ChevronLeft, ChevronRight } from 'lucide-react'
import { AppBadge, AppButton, AppCard, AppCardBody, AppCardHeader } from '@/components/ui'
import { buildMonthGrid, dateKey, monthLabel, sameDay } from '@/features/calendar-tasks/calendar-utils'
import type { CalendarEventSummary } from '@/lib/api/generated/client'
import { cn } from '@/lib/utils/cn'

type MonthCalendarProps = {
  month: Date
  selectedDate: string
  today: string
  events: CalendarEventSummary[]
  onMonthChange: (next: Date) => void
  onSelectDate: (date: string) => void
  onOpenEvent: (eventId: string) => void
}

export function MonthCalendar({ month, selectedDate, today, events, onMonthChange, onSelectDate, onOpenEvent }: MonthCalendarProps) {
  const cells = buildMonthGrid(month)
  const eventsByDate = events.reduce<Record<string, CalendarEventSummary[]>>((carry, event) => {
    if (!event.startsAt) return carry
    const key = dateKey(new Date(event.startsAt))
    carry[key] = [...(carry[key] ?? []), event]
    return carry
  }, {})

  return (
    <AppCard>
      <AppCardHeader>
        <div className="flex items-center justify-between gap-3">
          <div>
            <div className="heading-md">Operational calendar</div>
            <div className="body-sm text-text-muted">Selected-day drilldown and event chips render from canonical calendar APIs.</div>
          </div>
          <div className="flex items-center gap-2">
            <AppButton type="button" variant="secondary" onClick={() => onMonthChange(new Date(month.getFullYear(), month.getMonth() - 1, 1))}><ChevronLeft size={16} /></AppButton>
            <div className="label-sm min-w-40 text-center uppercase tracking-[0.12em] text-text-muted">{monthLabel(month)}</div>
            <AppButton type="button" variant="secondary" onClick={() => onMonthChange(new Date(month.getFullYear(), month.getMonth() + 1, 1))}><ChevronRight size={16} /></AppButton>
          </div>
        </div>
      </AppCardHeader>
      <AppCardBody>
        <div className="grid grid-cols-7 gap-2 text-center text-xs font-semibold uppercase tracking-[0.12em] text-text-muted">
          {['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].map((label) => <div key={label}>{label}</div>)}
        </div>
        <div className="mt-3 grid grid-cols-7 gap-2">
          {cells.map((cell) => {
            const key = dateKey(cell)
            const items = eventsByDate[key] ?? []
            const isCurrentMonth = cell.getMonth() === month.getMonth()
            const isSelected = sameDay(key, selectedDate)
            const isToday = sameDay(key, today)

            return (
              <button
                key={key}
                type="button"
                onClick={() => onSelectDate(key)}
                className={cn(
                  'min-h-28 rounded-lg border border-border bg-surface p-2 text-left shadow-xs transition motion-base',
                  !isCurrentMonth && 'opacity-60',
                  isToday && 'ring-1 ring-primary/50',
                  isSelected && 'border-primary bg-primary/5'
                )}
              >
                <div className="flex items-center justify-between gap-2">
                  <span className={cn('inline-flex h-7 w-7 items-center justify-center rounded-full text-sm font-semibold', isSelected && 'bg-primary text-white')}>
                    {cell.getDate()}
                  </span>
                  {items.length ? <AppBadge variant="neutral">{items.length}</AppBadge> : null}
                </div>
                <div className="mt-2 space-y-1">
                  {items.slice(0, 2).map((event) => (
                    <button
                      key={event.id}
                      type="button"
                      onClick={(evt) => {
                        evt.stopPropagation()
                        onOpenEvent(event.id)
                      }}
                      className="block w-full truncate rounded-md bg-muted px-2 py-1 text-left text-xs font-medium text-text"
                    >
                      {event.title}
                    </button>
                  ))}
                  {items.length > 2 ? <div className="text-xs font-medium text-text-muted">+{items.length - 2} more</div> : null}
                </div>
              </button>
            )
          })}
        </div>
      </AppCardBody>
    </AppCard>
  )
}
