import { vi } from 'vitest'
import { fireEvent, render, screen } from '@testing-library/react'
import { AuditFilterBar } from '@/features/audit/components/audit-filter-bar'

describe('audit filter bar', () => {
  it('clears audit filters back to empty values', () => {
    const onChange = vi.fn()

    render(
      <AuditFilterBar
        filters={{
          action: 'import.commit',
          subjectType: 'import',
          q: 'corr-123',
        }}
        onChange={onChange}
      />,
    )

    fireEvent.click(screen.getByRole('button', { name: 'Clear' }))

    expect(onChange).toHaveBeenCalledWith({
      action: '',
      subjectType: '',
      q: '',
    })
  })
})
