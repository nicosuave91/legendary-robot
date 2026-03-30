import { useMemo } from 'react'
import { useNavigate, useSearchParams } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import { PageHeader, AppButton, EmptyState, LoadingSkeleton } from '@/components/ui'
import { ClientFilters } from '@/features/clients/components/client-filters'
import { ClientTable } from '@/features/clients/components/client-table'
import { clientsApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import { useAuth } from '@/lib/auth/auth-hooks'
import { hasPermission } from '@/lib/auth/permission-map'

export function ClientListPage() {
  const navigate = useNavigate()
  const { data: auth } = useAuth()
  const [searchParams, setSearchParams] = useSearchParams()

  const filters = useMemo(() => ({
    search: searchParams.get('search') ?? undefined,
    status: searchParams.get('status') ?? undefined,
    sort: searchParams.get('sort') ?? 'updated_at',
    direction: searchParams.get('direction') ?? 'desc',
    page: Number(searchParams.get('page') ?? '1'),
    perPage: Number(searchParams.get('perPage') ?? '20')
  }), [searchParams])

  const clientsQuery = useQuery({
    queryKey: queryKeys.clients.list(filters),
    queryFn: () => clientsApi.list(filters)
  })

  const payload = clientsQuery.data?.data
  const canCreateClient = hasPermission(auth?.permissions ?? [], 'clients.create')

  const updateParams = (next: Record<string, string | number | undefined>) => {
    const merged = new URLSearchParams(searchParams)
    Object.entries(next).forEach(([key, value]) => {
      if (value === undefined || value === '' || Number.isNaN(value)) {
        merged.delete(key)
      } else {
        merged.set(key, String(value))
      }
    })
    setSearchParams(merged)
  }

  return (
    <div className="space-y-6">
      <PageHeader
        title="Clients"
        description="Tenant-safe search, sorting, and pagination now land users in a governed client workspace instead of disconnected record pages."
        actions={canCreateClient ? <AppButton type="button" onClick={() => navigate('/app/clients/new')}>New client</AppButton> : null}
      />

      <ClientFilters
        defaultSearch={searchParams.get('search') ?? ''}
        defaultStatus={searchParams.get('status') ?? ''}
        onApply={({ search, status }) => updateParams({ search, status, page: 1 })}
        onReset={() => setSearchParams(new URLSearchParams())}
      />

      {clientsQuery.isLoading && !payload ? <LoadingSkeleton lines={6} /> : null}
      {payload?.items?.length ? (
        <div className="space-y-4">
          <ClientTable
            items={payload.items}
            sort={(payload.appliedFilters.sort as 'display_name' | 'created_at' | 'updated_at' | 'last_activity_at')}
            direction={payload.appliedFilters.direction}
            onSort={(sort) => {
              const nextDirection = payload.appliedFilters.sort === sort && payload.appliedFilters.direction === 'asc' ? 'desc' : 'asc'
              updateParams({ sort, direction: nextDirection })
            }}
            onSelectClient={(clientId) => navigate(`/app/clients/${clientId}/overview`)}
          />

          <div className="flex items-center justify-between gap-3">
            <div className="body-sm text-text-muted">Showing page {payload.pagination.page} of {payload.pagination.totalPages}</div>
            <div className="flex gap-2">
              <AppButton type="button" variant="secondary" disabled={payload.pagination.page <= 1} onClick={() => updateParams({ page: payload.pagination.page - 1 })}>Previous</AppButton>
              <AppButton type="button" variant="secondary" disabled={payload.pagination.page >= payload.pagination.totalPages} onClick={() => updateParams({ page: payload.pagination.page + 1 })}>Next</AppButton>
            </div>
          </div>
        </div>
      ) : null}

      {!clientsQuery.isLoading && !payload?.items?.length ? (
        <EmptyState
          title="No clients found"
          description={filters.search || filters.status ? 'Try adjusting the current search or status filter.' : 'Create the first client record to establish the Sprint 4 workspace baseline.'}
        />
      ) : null}
    </div>
  )
}
