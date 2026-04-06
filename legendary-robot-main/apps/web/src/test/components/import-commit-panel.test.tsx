import { vi } from 'vitest'
import { fireEvent, render, screen } from '@testing-library/react'
import { ImportCommitPanel } from '@/features/imports/components/import-commit-panel'

describe('import commit panel', () => {
  it('prevents commit until the server says the import can commit', () => {
    render(
      <ImportCommitPanel
        canCommit={false}
        status="validated_with_errors"
        onValidate={vi.fn()}
        onCommit={vi.fn()}
      />,
    )

    expect(screen.getByRole('button', { name: 'Commit validated rows' })).toBeDisabled()
  })

  it('invokes validation and commit callbacks when actions are available', () => {
    const onValidate = vi.fn()
    const onCommit = vi.fn()

    render(
      <ImportCommitPanel
        canCommit
        status="validated"
        onValidate={onValidate}
        onCommit={onCommit}
      />,
    )

    fireEvent.click(screen.getByRole('button', { name: 'Validate staged file' }))
    fireEvent.click(screen.getByRole('button', { name: 'Commit validated rows' }))

    expect(onValidate).toHaveBeenCalledTimes(1)
    expect(onCommit).toHaveBeenCalledTimes(1)
  })
})
