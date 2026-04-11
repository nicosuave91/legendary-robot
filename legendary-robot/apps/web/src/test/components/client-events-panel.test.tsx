import { vi } from 'vitest'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { MemoryRouter } from 'react-router-dom'
import { render, screen } from '@testing-library/react'
import { ClientEventsPanel } from '@/features/calendar-tasks/components/client-events-panel'
import { calendarApi } from '@/lib/api/client'

vi.mock('@/lib/api/client', async () => {
  const actual = await vi.importActual<typeof import('@/lib/api/client')>('@/lib/api/client')
  return {
    ...actual,
    calendarApi: {
      ...actual.calendarApi,
      clientEvents: vi.fn(),
    },
  }
})

vi.mock('@/features/calendar-tasks/components/event-detail-drawer', () => ({
  EventDetailDrawer: () => null,
}))

describe('client events panel', () => {
  it('renders empty guidance when the client has no linked events', async () => {
    vi.mocked(calendarApi.clientEvents).mockResolvedValue({
      data: { items: [] },
      meta: { apiVersion: 'v1', correlationId: 'corr-test' },
    } as never)

    const client = new QueryClient()

    render(
      <QueryClientProvider client={client}>
        <MemoryRouter>
          <ClientEventsPanel clientId="client-1" />
        </MemoryRouter>
      </QueryClientProvider>,
    )

    expect(await screen.findByText('No linked events yet')).toBeInTheDocument()
  })
})
