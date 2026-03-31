import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { AppCard, AppCardBody, AppCardHeader, EmptyState, LoadingSkeleton, PageHeader } from '@/components/ui'
import { AuditFilterBar } from '@/features/audit/components/audit-filter-bar'
import { AuditResultsTable } from '@/features/audit/components/audit-results-table'
import { auditApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'

export function AuditListPage() {
  const [filters, setFilters] = useState({ action: '', subjectType: '', q: '' })

  const query = useQuery({
    queryKey: queryKeys.audit.list(filters),
    queryFn: () => auditApi.list({
      ...(filters.action ? { action: filters.action } : {}),
      ...(filters.subjectType ? { subjectType: filters.subjectType } : {}),
      ...(filters.q ? { q: filters.q } : {}),
    })
  })

  const items = query.data?.data.items ?? []

  return (
    <div className="space-y-6">
      <PageHeader
        title="Audit"
        description="Sprint 9 replaces the placeholder with a filterable, tenant-scoped audit baseline for imports, notifications, and other sensitive actions."
      />
      <AppCard>
        <AppCardHeader>
          <div className="heading-md">Search audit evidence</div>
          <div className="body-sm text-text-muted">Audit remains append-only. Search narrows the current tenant view without mutating evidence.</div>
        </AppCardHeader>
        <AppCardBody className="space-y-4">
          <AuditFilterBar filters={filters} onChange={setFilters} />
          {query.isLoading ? <LoadingSkeleton lines={8} /> : null}
          {!query.isLoading && items.length === 0 ? (
            <EmptyState title="No audit entries match the current filters" description="Try searching by action, subject type, or correlation ID." />
          ) : null}
          {items.length ? <AuditResultsTable items={items} /> : null}
        </AppCardBody>
      </AppCard>
    </div>
  )
}
