import { EmptyState } from '@/components/ui'
import type { ApplicationStatusHistoryItem } from '@/lib/api/generated/client'

export function ApplicationStatusTimeline({ items }: { items: ApplicationStatusHistoryItem[] }) {
  if (!items.length) {
    return <EmptyState title="No status history yet" description="Status transitions will create append-only history here." />
  }

  return (
    <div className="space-y-3">
      {items.map((item) => (
        <div key={item.id} className="rounded-lg border border-border bg-muted p-4">
          <div className="font-medium text-text">{item.toStatus}</div>
          <div className="text-xs text-text-muted">{item.occurredAt ? new Date(item.occurredAt).toLocaleString() : '—'}{item.actorDisplayName ? ` • ${item.actorDisplayName}` : ''}</div>
          {item.reason ? <div className="body-sm mt-1 text-text-muted">{item.reason}</div> : null}
        </div>
      ))}
    </div>
  )
}
