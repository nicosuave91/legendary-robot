import { vi } from 'vitest'
import { fireEvent, render, screen } from '@testing-library/react'
import { NotificationFeedList } from '@/features/notifications/components/notification-feed-list'

describe('notification feed list', () => {
  it('renders empty state when there are no notifications', () => {
    render(<NotificationFeedList items={[]} surface="tray" />)

    expect(screen.getByText('No notifications')).toBeInTheDocument()
    expect(screen.getByText('You are all caught up.')).toBeInTheDocument()
  })

  it('supports mark read and dismiss actions when the notification is actionable', () => {
    const onRead = vi.fn()
    const onDismiss = vi.fn()

    render(
      <NotificationFeedList
        surface="header_center"
        onRead={onRead}
        onDismiss={onDismiss}
        items={[
          {
            id: 'notif-1',
            title: 'Import validation completed',
            body: 'Row-level errors are available for review.',
            tone: 'warning',
            emittedAt: '2026-03-31T12:00:00Z',
            actionUrl: '/imports/import-1',
            isRead: false,
            isDismissed: false,
          },
        ]}
      />,
    )

    fireEvent.click(screen.getByRole('button', { name: 'Mark read' }))
    fireEvent.click(screen.getByRole('button', { name: 'Dismiss' }))

    expect(onRead).toHaveBeenCalledWith('notif-1')
    expect(onDismiss).toHaveBeenCalledWith('notif-1', 'header_center')
    expect(screen.getByRole('link', { name: 'Open' })).toHaveAttribute('href', '/imports/import-1')
  })
})
