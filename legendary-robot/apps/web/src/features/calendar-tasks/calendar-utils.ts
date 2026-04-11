export function dateKey(value: Date | string) {
  const date = typeof value === 'string' ? new Date(`${value}T00:00:00`) : value
  const year = date.getFullYear()
  const month = `${date.getMonth() + 1}`.padStart(2, '0')
  const day = `${date.getDate()}`.padStart(2, '0')
  return `${year}-${month}-${day}`
}

export function parseDateKey(value: string) {
  return new Date(`${value}T00:00:00`)
}

export function sameDay(a: Date | string, b: Date | string) {
  return dateKey(a) === dateKey(b)
}

export function addDays(date: Date, days: number) {
  const next = new Date(date)
  next.setDate(next.getDate() + days)
  return next
}

export function startOfMonth(date: Date) {
  return new Date(date.getFullYear(), date.getMonth(), 1)
}

export function endOfMonth(date: Date) {
  return new Date(date.getFullYear(), date.getMonth() + 1, 0)
}

export function startOfWeek(date: Date) {
  const weekday = date.getDay()
  return addDays(date, -weekday)
}

export function endOfWeek(date: Date) {
  return addDays(startOfWeek(date), 6)
}

export function buildMonthGrid(month: Date) {
  const start = startOfWeek(startOfMonth(month))
  return Array.from({ length: 42 }, (_, index) => addDays(start, index))
}

export function monthRange(month: Date) {
  const start = startOfWeek(startOfMonth(month))
  const end = endOfWeek(endOfMonth(month))
  return {
    startDate: dateKey(start),
    endDate: dateKey(end),
  }
}

export function monthLabel(month: Date) {
  return month.toLocaleDateString(undefined, { month: 'long', year: 'numeric' })
}

export function dayLabel(value: string) {
  return parseDateKey(value).toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric' })
}

export function toLocalDateTimeInputValue(value: Date) {
  const year = value.getFullYear()
  const month = `${value.getMonth() + 1}`.padStart(2, '0')
  const day = `${value.getDate()}`.padStart(2, '0')
  const hours = `${value.getHours()}`.padStart(2, '0')
  const minutes = `${value.getMinutes()}`.padStart(2, '0')
  return `${year}-${month}-${day}T${hours}:${minutes}`
}

export function formatTimeRange(startsAt?: string | null, endsAt?: string | null, isAllDay?: boolean) {
  if (isAllDay) return 'All day'
  if (!startsAt) return 'Time not set'

  const start = new Date(startsAt)
  const startLabel = start.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' })
  if (!endsAt) return startLabel
  const end = new Date(endsAt)
  const endLabel = end.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' })
  return `${startLabel} – ${endLabel}`
}
