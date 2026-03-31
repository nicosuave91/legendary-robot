import { AppBadge, EmptyState } from '@/components/ui'
import type { ApplicationRuleNote } from '@/lib/api/generated/client'

export function ApplicationRuleNoteList({ items }: { items: ApplicationRuleNote[] }) {
  if (!items.length) {
    return <EmptyState title="No rule notes yet" description="Rule evaluations will appear here as immutable, timestamped evidence when they apply." />
  }

  return (
    <div className="space-y-3">
      {items.map((note) => (
        <div key={note.id} className="rounded-lg border border-border bg-muted p-4">
          <div className="flex flex-wrap items-center justify-between gap-3">
            <div className="flex items-center gap-2">
              <AppBadge variant={note.outcome === 'blocking' ? 'danger' : note.outcome === 'warning' ? 'warning' : 'info'}>{note.outcome}</AppBadge>
              <div className="font-medium text-text">{note.title}</div>
            </div>
            <div className="text-xs text-text-muted">{note.appliedAt ? new Date(note.appliedAt).toLocaleString() : '—'}</div>
          </div>
          <div className="body-sm mt-2 text-text-muted">{note.body}</div>
          <div className="mt-2 text-xs text-text-muted">{note.ruleKey} • {note.ruleVersion} • view-only</div>
        </div>
      ))}
    </div>
  )
}
