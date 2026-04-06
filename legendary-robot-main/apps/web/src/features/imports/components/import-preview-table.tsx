import { AppTable } from '@/components/ui'

type ImportPreviewRow = {
  id: string
  rowNumber: number
  rowStatus: string
  normalizedPayload: Record<string, unknown>
  targetSubjectType?: string | null
  targetSubjectId?: string | null
}

export function ImportPreviewTable({ rows }: { rows: ImportPreviewRow[] }) {
  return (
    <AppTable>
      <thead className="bg-muted text-left text-xs uppercase tracking-[0.12em] text-text-muted">
        <tr>
          <th className="px-4 py-3">Row</th>
          <th className="px-4 py-3">Display</th>
          <th className="px-4 py-3">Email</th>
          <th className="px-4 py-3">Phone</th>
          <th className="px-4 py-3">Status</th>
        </tr>
      </thead>
      <tbody className="divide-y divide-border">
        {rows.map((row) => (
          <tr key={row.id}>
            <td className="px-4 py-3 text-text">{row.rowNumber}</td>
            <td className="px-4 py-3 text-text">{String(row.normalizedPayload.displayName ?? '—')}</td>
            <td className="px-4 py-3 text-text-muted">{String(row.normalizedPayload.primaryEmail ?? '—')}</td>
            <td className="px-4 py-3 text-text-muted">{String(row.normalizedPayload.primaryPhone ?? '—')}</td>
            <td className="px-4 py-3 text-text-muted">{row.rowStatus.replaceAll('_', ' ')}</td>
          </tr>
        ))}
      </tbody>
    </AppTable>
  )
}
