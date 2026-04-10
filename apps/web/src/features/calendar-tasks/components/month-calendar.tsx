import { ChevronLeft, ChevronRight } from 'lucide-react'
import { AppButton, AppCard, AppCardBody, AppCardHeader } from '@/components/ui'
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

const eventToneClasses = [
  'border-l-primary bg-primary/5',
  'border-l-secondary bg-secondary/10',
  'border-l-accent bg-accent/10',
]

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
          <div className="heading-md">Calendar</div>
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
            const firstEvent = items[0]

            return (
              <button
                key={key}
                type="button"
                onClick={() => onSelectDate(key)}
                className={cn(
                  'min-h-[56px] rounded-lg border border-border bg-surface p-2 text-left shadow-xs transition motion-base',
                  !isCurrentMonth && 'opacity-60',
                  isSelected && 'border-primary ring-1 ring-primary/35',
                )}
              >
                <div className="flex items-start justify-between gap-2">
                  <div className="flex items-center gap-2">
                    <span className={cn(
                      'inline-flex h-7 w-7 items-center justify-center rounded-full text-sm font-semibold text-text-muted',
                      isToday && 'bg-primary text-white',
                    )}>
                      {cell.getDate()}
                    </span>
                    {items.length ? <span className="inline-block h-2.5 w-2.5 rounded-full bg-primary" /> : null}
                  </div>
                  {items.length > 1 ? <span className="text-[11px] font-medium text-text-muted">{items.length}</span> : null}
                </div>
                {firstEvent ? (
                  <button
                    type="button"
                    onClick={(evt) => {
                      evt.stopPropagation()
                      onOpenEvent(firstEvent.id)
                    }}
                    title={firstEvent.title}
                    className={cn(
                      'mt-2 block w-full truncate rounded-md border-l-2 px-2 py-1 text-left text-[11px] font-medium text-text',
                      eventToneClasses[0],
                    )}
                  >
                    {firstEvent.title}
                  </button>
                ) : null}
              </button>
            )
          })}
        </div>
      </AppCardBody>
    </AppCard>
  )
}
