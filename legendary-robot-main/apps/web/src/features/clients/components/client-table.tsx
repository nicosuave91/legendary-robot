import { AppTable } from '@/components/ui'
import type { ClientListItem } from '@/lib/api/generated/client'
import { ClientStatusBadge } from '@/features/clients/components/client-status-badge'

type SortKey = 'display_name' | 'created_at' | 'updated_at' | 'last_activity_at'

type ClientTableProps = {
  items: ClientListItem[]
  sort: SortKey
  direction: 'asc' | 'desc'
  onSort: (sort: SortKey) => void
  onSelectClient: (clientId: string) => void
}

function SortableHeader({ label, column, sort, direction, onSort }: { label: string, column: SortKey, sort: SortKey, direction: 'asc' | 'desc', onSort: (sort: SortKey) => void }) {
  return (
    <button type="button" className="inline-flex items-center gap-2 font-medium text-text" onClick={() => onSort(column)}>
      <span>{label}</span>
      {sort === column ? <span className="text-text-muted">{direction === 'asc' ? '↑' : '↓'}</span> : null}
    </button>
  )
}

export function ClientTable({ items, sort, direction, onSort, onSelectClient }: ClientTableProps) {
  return (
    <AppTable>
      <thead className="bg-muted/40 text-left text-text-muted">
        <tr>
          <th className="px-4 py-3"><SortableHeader label="Client" column="display_name" sort={sort} direction={direction} onSort={onSort} /></th>
          <th className="px-4 py-3">Status</th>
          <th className="px-4 py-3">Email</th>
          <th className="px-4 py-3">Phone</th>
          <th className="px-4 py-3">Notes</th>
          <th className="px-4 py-3">Documents</th>
          <th className="px-4 py-3"><SortableHeader label="Updated" column="updated_at" sort={sort} direction={direction} onSort={onSort} /></th>
        </tr>
      </thead>
      <tbody className="divide-y divide-border bg-surface">
        {items.map((item) => (
          <tr key={item.id} className="cursor-pointer hover:bg-muted/30" onClick={() => onSelectClient(item.id)}>
            <td className="px-4 py-3">
              <div className="font-medium text-text">{item.displayName}</div>
              <div className="text-xs text-text-muted">{[item.city, item.stateCode].filter(Boolean).join(', ') || 'No location yet'}</div>
            </td>
            <td className="px-4 py-3"><ClientStatusBadge status={item.status} /></td>
            <td className="px-4 py-3 text-text-muted">{item.primaryEmail ?? '—'}</td>
            <td className="px-4 py-3 text-text-muted">{item.primaryPhone ?? '—'}</td>
            <td className="px-4 py-3 text-text-muted">{item.notesCount}</td>
            <td className="px-4 py-3 text-text-muted">{item.documentsCount}</td>
            <td className="px-4 py-3 text-text-muted">{item.updatedAt ? new Date(item.updatedAt).toLocaleDateString() : '—'}</td>
          </tr>
        ))}
      </tbody>
    </AppTable>
  )
}
