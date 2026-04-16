import { AppButton, AppInput } from '@/components/ui'

type AuditFilters = {
  action: string
  subjectType: string
  q: string
}

type AuditFilterBarProps = {
  filters: AuditFilters
  onChange: (filters: AuditFilters) => void
}

export function AuditFilterBar({ filters, onChange }: AuditFilterBarProps) {
  return (
    <div className="grid gap-2.5 md:grid-cols-[1fr_1fr_1.4fr_auto]">
      <AppInput
        value={filters.action}
        onChange={(event) => onChange({ ...filters, action: event.currentTarget.value })}
        placeholder="Filter by action"
      />
      <AppInput
        value={filters.subjectType}
        onChange={(event) =>
          onChange({ ...filters, subjectType: event.currentTarget.value })
        }
        placeholder="Filter by subject type"
      />
      <AppInput
        value={filters.q}
        onChange={(event) => onChange({ ...filters, q: event.currentTarget.value })}
        placeholder="Search correlation ID or subject"
      />
      <AppButton
        type="button"
        size="sm"
        variant="secondary"
        onClick={() => onChange({ action: '', subjectType: '', q: '' })}
      >
        Clear
      </AppButton>
    </div>
  )
}
