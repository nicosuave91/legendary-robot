import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import {
  AppBadge,
  AppCard,
  AppCardBody,
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
    <PageCanvas density="compact">
      <PageHeader
        variant="audit"
        eyebrow="Investigation"
        title="Audit"
        description="Search append-only tenant-scoped evidence for imports, notifications, workflows, communications, and other sensitive actions."
        statusSummary={<AppBadge variant="neutral">{items.length} results</AppBadge>}
        filterRegion={<AuditFilterBar filters={filters} onChange={setFilters} />}
      />

      <AppCard>
        <AppCardBody density="compact" className="space-y-4">
          {query.isLoading ? <LoadingSkeleton lines={8} /> : null}
          {!query.isLoading && items.length === 0 ? (
            <EmptyState
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
