import { render, screen } from '@testing-library/react'
import { MonthCalendar } from '@/features/calendar-tasks/components/month-calendar'

describe('month calendar', () => {
  it('renders a visible event-presence marker and first event title', () => {
    render(
      <MonthCalendar
        month={new Date('2026-03-01T00:00:00')}
        selectedDate="2026-03-15"
        today="2026-03-31"
        onMonthChange={() => undefined}
        onSelectDate={() => undefined}
        onOpenEvent={() => undefined}
        events={[
          { id: '1', title: 'Borrower review', description: null, eventType: 'appointment', status: 'scheduled', startsAt: '2026-03-15T10:00:00Z', endsAt: null, isAllDay: false, location: null, client: null, owner: null, taskSummary: { total: 0, open: 0, completed: 0, blocked: 0, skipped: 0 } },
        ]}
      />,
    )

    expect(screen.getByTitle('Borrower review')).toBeInTheDocument()
  })
})
