import { EmptyState } from '@/components/ui'

export function WorkspacePlaceholderPanel({ title }: { title: string }) {
  return (
    <EmptyState
      title={`${title} arrives in a later phase`}
      description="This tab is intentionally scaffold-only in Sprint 4 so the client workspace surface exists without pulling future calendar, communications, or applications logic into the current sprint."
    />
  )
}
