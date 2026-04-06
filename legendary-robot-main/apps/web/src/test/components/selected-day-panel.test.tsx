import { render, screen } from '@testing-library/react'
import { MemoryRouter } from 'react-router-dom'
import { SelectedDayPanel } from '@/features/calendar-tasks/components/selected-day-panel'

describe('selected day panel', () => {
  it('renders empty guidance when there are no events for the selected day', () => {
    render(
      <MemoryRouter>
        <SelectedDayPanel
          isLoading={false}
          data={{
            selectedDate: '2026-03-31',
            isToday: true,
            summary: { eventCount: 0, openTaskCount: 0, completedTaskCount: 0, blockedTaskCount: 0, skippedTaskCount: 0 },
            events: [],
          }}
          onOpenEvent={() => undefined}
        />
      </MemoryRouter>,
    )

    expect(screen.getByText('No events scheduled')).toBeInTheDocument()
  })
})
