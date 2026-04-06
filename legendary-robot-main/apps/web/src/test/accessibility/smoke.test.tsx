import { render, screen } from '@testing-library/react'
import { EmptyState } from '@/components/ui'

describe('shared empty state', () => {
  it('renders accessible baseline text', () => {
    render(<EmptyState title="Nothing here" description="Deferred until a later sprint." />)
    expect(screen.getByText('Nothing here')).toBeInTheDocument()
    expect(screen.getByText('Deferred until a later sprint.')).toBeInTheDocument()
  })
})
