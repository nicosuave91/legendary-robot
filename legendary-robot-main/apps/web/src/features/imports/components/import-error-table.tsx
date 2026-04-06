import { AppTable } from '@/components/ui'

type ImportErrorItem = {
  id: string
  rowNumber: number
  fieldName?: string | null
  errorCode: string
  severity: string
  message: string
}

export function ImportErrorTable({ errors }: { errors: ImportErrorItem[] }) {
  return (
    <AppTable>
      <thead className="bg-muted text-left text-xs uppercase tracking-[0.12em] text-text-muted">
        <tr>
          <th className="px-4 py-3">Row</th>
          <th className="px-4 py-3">Field</th>
          <th className="px-4 py-3">Code</th>
          <th className="px-4 py-3">Severity</th>
          <th className="px-4 py-3">Message</th>
        </tr>
      </thead>
      <tbody className="divide-y divide-border">
        {errors.map((error) => (
          <tr key={error.id}>
            <td className="px-4 py-3 text-text">{error.rowNumber}</td>
            <td className="px-4 py-3 text-text-muted">{error.fieldName ?? '—'}</td>
            <td className="px-4 py-3 text-text-muted">{error.errorCode}</td>
            <td className="px-4 py-3 text-text-muted">{error.severity}</td>
            <td className="px-4 py-3 text-text">{error.message}</td>
          </tr>
        ))}
      </tbody>
    </AppTable>
  )
}
