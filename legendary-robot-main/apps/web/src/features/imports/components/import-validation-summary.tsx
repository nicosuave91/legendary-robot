import { AppCard, AppCardBody, AppCardHeader } from '@/components/ui'

type ImportValidationSummaryProps = {
  rowCount: number
  validRowCount: number
  invalidRowCount: number
  committedRowCount: number
  warningCount?: number
}

export function ImportValidationSummary({
  rowCount,
  validRowCount,
  invalidRowCount,
  committedRowCount,
  warningCount = 0,
}: ImportValidationSummaryProps) {
  return (
    <AppCard>
      <AppCardHeader>
        <div className="heading-md">Validation summary</div>
        <div className="body-sm text-text-muted">The server owns readiness and commit eligibility. React only renders the returned projection.</div>
      </AppCardHeader>
      <AppCardBody>
        <div className="grid gap-4 md:grid-cols-5">
          <Metric label="Rows" value={rowCount} />
          <Metric label="Valid" value={validRowCount} />
          <Metric label="Errors" value={invalidRowCount} />
          <Metric label="Warnings" value={warningCount} />
          <Metric label="Committed" value={committedRowCount} />
        </div>
      </AppCardBody>
    </AppCard>
  )
}

function Metric({ label, value }: { label: string; value: number }) {
  return (
    <div className="rounded-lg border border-border bg-muted p-4">
      <div className="label-sm uppercase tracking-[0.12em] text-text-muted">{label}</div>
      <div className="heading-lg mt-2 text-text">{value}</div>
    </div>
  )
}
