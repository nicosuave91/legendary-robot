import { AppTable } from '@/components/ui'

type AuditItem = {
  id: number
  action: string
  subjectType: string
  subjectId?: string | null
  correlationId?: string | null
  occurredAt?: string | null
  afterSummary?: Record<string, unknown>
}

export function AuditResultsTable({ items }: { items: AuditItem[] }) {
  return (
    <AppTable>
      <thead className="bg-muted text-left text-xs uppercase tracking-[0.12em] text-text-muted">
        <tr>
          <th className="px-4 py-3">Action</th>
          <th className="px-4 py-3">Subject</th>
          <th className="px-4 py-3">Correlation</th>
          <th className="px-4 py-3">Occurred</th>
          <th className="px-4 py-3">After</th>
        </tr>
      </thead>
      <tbody className="divide-y divide-border">
        {items.map((item) => (
          <tr key={item.id}>
            <td className="px-4 py-3 text-text">{item.action}</td>
            <td className="px-4 py-3 text-text-muted">{item.subjectType}{item.subjectId ? ` • ${item.subjectId}` : ''}</td>
            <td className="px-4 py-3 text-text-muted">{item.correlationId ?? '—'}</td>
            <td className="px-4 py-3 text-text-muted">{item.occurredAt ? new Date(item.occurredAt).toLocaleString() : '—'}</td>
            <td className="px-4 py-3 text-text-muted"><pre className="whitespace-pre-wrap text-xs">{JSON.stringify(item.afterSummary ?? {}, null, 2)}</pre></td>
          </tr>
        ))}
      </tbody>
    </AppTable>
  )
}
