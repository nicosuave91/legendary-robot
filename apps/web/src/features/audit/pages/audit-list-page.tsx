import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import {
  AppCard,
  AppCardBody,
  AppCardHeader,
  AppBadge,
  EmptyState,
  LoadingSkeleton,
  PageCanvas,
  PageHeader,
} from '@/components/ui'
import { AuditFilterBar } from '@/features/audit/components/audit-filter-bar'
import { AuditResultsTable } from '@/features/audit/components/audit-results-table'
import { auditApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'

export function AuditListPage() {
  const [filters, setFilters] = useState({ action: '', subjectType: '', q: '' })

  const query = useQuery({
    queryKey: queryKeys.audit.list(filters),
    queryFn: () =>
      auditApi.list({
        ...(filters.action ? { action: filters.action } : {}),
        ...(filters.subjectType ? { subjectType: filters.subjectType } : {}),
        ...(filters.q ? { q: filters.q } : {}),
      }),
  })

  const items = query.data?.data.items ?? []

  return (
    <PageCanvas>
      <PageHeader
        variant="audit"
        eyebrow="Evidence & investigation"
        title="Audit"
        description="Search immutable, tenant-scoped evidence for workflows, imports, communications, and other sensitive actions."
        status={
          <>
            <AppBadge variant="neutral">Action {filters.action || 'all'}</AppBadge>
            <AppBadge variant="neutral">Subject {filters.subjectType || 'all'}</AppBadge>
            <AppBadge variant="info">{items.length} visible results</AppBadge>
          </>
        }
        filters={<AuditFilterBar filters={filters} onChange={setFilters} />}
      />

      <AppCard>
        <AppCardHeader>
          <div className="heading-md">Evidence results</div>
          <div className="body-sm text-text-muted">
            Audit remains append-only. Searching narrows the current tenant view without mutating evidence.
          </div>
        </AppCardHeader>
        <AppCardBody className="space-y-4">
          {query.isLoading ? <LoadingSkeleton lines={8} /> : null}
          {!query.isLoading && items.length === 0 ? (
            <EmptyState
              compact
              title="No audit entries match the current filters"
              description="Try searching by action, subject type, or correlation ID."
            />
          ) : null}
          {items.length ? <AuditResultsTable items={items} /> : null}
        </AppCardBody>
      </AppCard>
    </PageCanvas>
  )
}
