import { useState } from 'react'
import { AppButton, AppInput, AppSelect } from '@/components/ui'

type ClientFiltersProps = {
  defaultSearch: string
  defaultStatus: string
  onApply: (filters: { search: string; status: string }) => void
  onReset: () => void
}

export function ClientFilters({
  defaultSearch,
  defaultStatus,
  onApply,
  onReset,
}: ClientFiltersProps) {
  const [search, setSearch] = useState(defaultSearch)
  const [status, setStatus] = useState(defaultStatus)

  return (
    <form
      className="grid gap-3 lg:grid-cols-[minmax(0,1fr)_180px_auto]"
      onSubmit={(event) => {
        event.preventDefault()
        onApply({ search, status })
      }}
    >
      <AppInput
        placeholder="Search by name, email, or phone"
        value={search}
        onChange={(event) => setSearch(event.currentTarget.value)}
      />
      <AppSelect value={status} onChange={(event) => setStatus(event.currentTarget.value)}>
        <option value="">All statuses</option>
        <option value="lead">Lead</option>
        <option value="qualified">Qualified</option>
        <option value="applied">Applied</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </AppSelect>
      <div className="flex gap-2">
        <AppButton type="submit" size="sm">
          Apply
        </AppButton>
        <AppButton
          type="button"
          variant="secondary"
          size="sm"
          onClick={() => {
            setSearch('')
            setStatus('')
            onReset()
          }}
        >
          Reset
        </AppButton>
      </div>
    </form>
  )
}
