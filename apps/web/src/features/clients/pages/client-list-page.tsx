import { useMemo } from 'react'
import { useNavigate, useSearchParams } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import {
  AppBadge,
  AppButton,
  AppCard,
  AppCardBody,
  AppCardHeader,
  EmptyState,
  LoadingSkeleton,
  PageCanvas,
  PageHeader,
} from '@/components/ui'
import { ClientFilters } from '@/features/clients/components/client-filters'
import { ClientTable } from '@/features/clients/components/client-table'
import { clientsApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import { useAuth } from '@/lib/auth/auth-hooks'
import { hasPermission } from '@/lib/auth/permission-map'

const validStatuses = ['lead', 'qualified', 'applied', 'active', 'inactive'] as const
const validSorts = ['display_name', 'created_at', 'updated_at', 'last_activity_at'] as const
const validDirections = ['asc', 'desc'] as const

type ClientListFilters = {
  search?: string
  status?: 'lead' | 'qualified' | 'applied' | 'active' | 'inactive'
  sort?: 'display_name' | 'created_at' | 'updated_at' | 'last_activity_at'
  direction?: 'asc' | 'desc'
  page?: number
  perPage?: number
}

export function ClientListPage() {
  const navigate = useNavigate()
  const { data: auth } = useAuth()
  const [searchParams, setSearchParams] = useSearchParams()

  const filters = useMemo<ClientListFilters>(() => {
    const status = searchParams.get('status')
    const sort = searchParams.get('sort')
    const direction = searchParams.get('direction')

    return {
      search: searchParams.get('search') ?? undefined,
      status: validStatuses.includes((status ?? '') as typeof validStatuses[number])
        ? (status as ClientListFilters['status'])
        : undefined,
      sort: validSorts.includes((sort ?? '') as typeof validSorts[number])
        ? (sort as ClientListFilters['sort'])
        : 'updated_at',
      direction: validDirections.includes(
        (direction ?? '') as typeof validDirections[number],
      )
        ? (direction as ClientListFilters['direction'])
        : 'desc',
      page: Number(searchParams.get('page') ?? '1'),
      perPage: Number(searchParams.get('perPage') ?? '20'),
    }
  }, [searchParams])

  const clientsQuery = useQuery({
    queryKey: queryKeys.clients.list(filters),
    queryFn: () => clientsApi.list(filters),
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
    <PageCanvas>
      <PageHeader
        variant="workspace"
        eyebrow="Client workspace"
        title="Clients"
        description="Search, sort, and move into the governed client record without leaving the primary working surface."
        status={
          <>
            <AppBadge variant="neutral">Sort {filters.sort ?? 'updated_at'}</AppBadge>
            <AppBadge variant="neutral">Direction {filters.direction ?? 'desc'}</AppBadge>
            {payload ? <AppBadge variant="info">{payload.pagination.total} total records</AppBadge> : null}
          </>
        }
        actions={
          canCreateClient ? (
            <AppButton type="button" onClick={() => navigate('/app/clients/new')}>
              New client
            </AppButton>
          ) : null
        }
        filters={
          <ClientFilters
            defaultSearch={searchParams.get('search') ?? ''}
            defaultStatus={searchParams.get('status') ?? ''}
            onApply={({ search, status }) =>
              updateParams({ search, status, page: 1 })
            }
            onReset={() => setSearchParams(new URLSearchParams())}
          />
        }
      />

      {clientsQuery.isLoading && !payload ? <LoadingSkeleton lines={6} /> : null}

      {payload?.items?.length ? (
        <AppCard>
          <AppCardHeader>
            <div className="flex flex-wrap items-center justify-between gap-3">
              <div>
                <div className="heading-md">Active results</div>
                <div className="body-sm text-text-muted">
                  Governed client records for the active tenant scope.
                </div>
              </div>
              <div className="text-xs text-text-muted">
                Page {payload.pagination.page} of {payload.pagination.totalPages}
              </div>
            </div>
          </AppCardHeader>
          <AppCardBody className="space-y-4 px-0 py-0 sm:px-0">
            <ClientTable
              items={payload.items}
              sort={
                payload.appliedFilters.sort as
                  | 'display_name'
                  | 'created_at'
                  | 'updated_at'
                  | 'last_activity_at'
              }
              direction={payload.appliedFilters.direction}
              onSort={(sort) => {
                const nextDirection =
                  payload.appliedFilters.sort === sort &&
                  payload.appliedFilters.direction === 'asc'
                    ? 'desc'
                    : 'asc'
                updateParams({ sort, direction: nextDirection })
              }}
              onSelectClient={(clientId) => navigate(`/app/clients/${clientId}/overview`)}
            />

            <div className="flex items-center justify-between gap-3 px-4 pb-4 sm:px-5">
              <div className="body-sm text-text-muted">
                Showing page {payload.pagination.page} of {payload.pagination.totalPages}
              </div>
              <div className="flex gap-2">
                <AppButton
                  type="button"
                  variant="secondary"
                  disabled={payload.pagination.page <= 1}
                  onClick={() => updateParams({ page: payload.pagination.page - 1 })}
                >
                  Previous
                </AppButton>
                <AppButton
                  type="button"
                  variant="secondary"
                  disabled={payload.pagination.page >= payload.pagination.totalPages}
                  onClick={() => updateParams({ page: payload.pagination.page + 1 })}
                >
                  Next
                </AppButton>
              </div>
            </div>
          </AppCardBody>
        </AppCard>
      ) : null}

      {!clientsQuery.isLoading && !payload?.items?.length ? (
        <EmptyState
          compact
          title="No clients found"
          description={
            filters.search || filters.status
              ? 'Try adjusting the current search or status filter.'
              : 'Create the first client record to establish the client workspace baseline.'
          }
        />
      ) : null}
    </PageCanvas>
  )
}
