import { PageHeader, EmptyState } from '@/components/ui'

export function PlaceholderPage({ title }: { title: string }) {
  return (
    <div>
      <PageHeader
        title={title}
        description="Reserved route placeholder. Business behavior is intentionally deferred beyond Sprint 1."
      />
      <EmptyState
        title={`${title} is not implemented yet`}
        description="The foundation keeps the route, shell, and boundary alignment ready for the next sprint."
      />
    </div>
  )
}
