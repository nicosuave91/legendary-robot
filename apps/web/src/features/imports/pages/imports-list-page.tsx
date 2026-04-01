import { useMemo, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { AppCard, AppCardBody, AppCardHeader, AppSelect, EmptyState, LoadingSkeleton, PageHeader } from '@/components/ui'
import { ImportUploadDialog } from '@/features/imports/components/import-upload-dialog'
import { ImportStatusBadge } from '@/features/imports/components/import-status-badge'
import { importsApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import { useToast } from '@/components/shell/toast-host'

type ImportListItem = {
  id: string
  status: string
  originalFilename: string
  importType: string
  fileFormat: string
  parserVersion?: string | null
  rowCount: number
  validRowCount: number
  invalidRowCount: number
  committedRowCount: number
}

type ImportListEnvelopeLike = {
  data: {
    items: ImportListItem[]
  }
}

type ImportCreateEnvelopeLike = {
  data: {
    import: {
      id: string
    }
  }
}

export function ImportsListPage() {
  const [statusFilter, setStatusFilter] = useState('')
  const navigate = useNavigate()
  const queryClient = useQueryClient()
  const { notify } = useToast()

  const filters = useMemo(() => (statusFilter ? { status: statusFilter } : {}), [statusFilter])

  const listQuery = useQuery({
    queryKey: queryKeys.imports.list(filters),
    queryFn: () => importsApi.list(filters)
  })

  const uploadMutation = useMutation({
    mutationFn: async ({ importType, file }: { importType: string; file: File }) => {
      const body = new FormData()
      body.set('importType', importType)
      body.set('file', file)
      return importsApi.create(body)
    },
    onSuccess: async (response) => {
      const envelope = response as ImportCreateEnvelopeLike
      await queryClient.invalidateQueries({ queryKey: queryKeys.imports.all })
      notify({ title: 'Import staged', description: 'The file is stored in governed staging and ready for validation.', tone: 'success' })
      navigate(`/app/imports/${envelope.data.import.id}`)
    },
    onError: (error) => {
      notify({ title: 'Import upload failed', description: error instanceof Error ? error.message : 'The file could not be staged.', tone: 'danger' })
    }
  })

  const items = ((listQuery.data as ImportListEnvelopeLike | undefined)?.data.items) ?? []
  const statusCounts = items.reduce<Record<string, number>>((acc, item) => {
    acc[item.status] = (acc[item.status] ?? 0) + 1
    return acc
  }, {})

  return (
    <div className="space-y-6">
      <PageHeader
        title="Imports"
        description="Governed imports ledger. Files stage first, validate second, and commit only through an explicit server-authoritative step."
      />

      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="grid gap-3 sm:grid-cols-4">
          <Metric label="Uploaded" value={statusCounts.uploaded ?? 0} />
          <Metric label="Ready" value={statusCounts.ready_to_commit ?? 0} />
          <Metric label="Committed" value={statusCounts.committed ?? 0} />
          <Metric label="Blocked" value={(statusCounts.validation_failed ?? 0) + (statusCounts.commit_failed ?? 0)} />
        </div>
        <div className="flex items-center gap-3">
          <AppSelect value={statusFilter} onChange={(event) => setStatusFilter(event.currentTarget.value)}>
            <option value="">All statuses</option>
            <option value="uploaded">Uploaded</option>
            <option value="ready_to_commit">Ready to commit</option>
            <option value="validation_failed">Validation failed</option>
            <option value="committed">Committed</option>
          </AppSelect>
          <ImportUploadDialog isPending={uploadMutation.isPending} onSubmit={(payload) => uploadMutation.mutate(payload)} />
        </div>
      </div>

      <AppCard>
        <AppCardHeader>
          <div className="heading-md">Import ledger</div>
          <div className="body-sm text-text-muted">Review staged files, validation outcomes, and commit lineage from one tenant-scoped surface.</div>
        </AppCardHeader>
        <AppCardBody>
          {listQuery.isLoading ? <LoadingSkeleton lines={8} /> : null}
          {!listQuery.isLoading && items.length === 0 ? (
            <EmptyState title="No imports yet" description="Upload a CSV to create the first staged import run for this tenant." />
          ) : null}
          <div className="space-y-3">
            {items.map((item) => (
              <Link key={item.id} to={`/app/imports/${item.id}`} className="block rounded-lg border border-border bg-muted p-4 transition hover:border-primary/40 hover:bg-surface">
                <div className="flex flex-wrap items-start justify-between gap-3">
                  <div>
                    <div className="heading-md text-text">{item.originalFilename}</div>
                    <div className="body-sm text-text-muted">{item.importType} • {item.fileFormat} • parser {item.parserVersion ?? '—'}</div>
                  </div>
                  <ImportStatusBadge status={item.status} />
                </div>
                <div className="mt-3 grid gap-3 text-xs text-text-muted sm:grid-cols-4">
                  <div>Rows: {item.rowCount}</div>
                  <div>Valid: {item.validRowCount}</div>
                  <div>Errors: {item.invalidRowCount}</div>
                  <div>Committed: {item.committedRowCount}</div>
                </div>
              </Link>
            ))}
          </div>
        </AppCardBody>
      </AppCard>
    </div>
  )
}

function Metric({ label, value }: { label: string; value: number }) {
  return (
    <div className="rounded-lg border border-border bg-surface px-4 py-3">
      <div className="label-sm uppercase tracking-[0.12em] text-text-muted">{label}</div>
      <div className="heading-md mt-2 text-text">{value}</div>
    </div>
  )
}
