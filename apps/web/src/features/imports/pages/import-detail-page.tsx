import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { Link, useParams } from 'react-router-dom'
import { AppCard, AppCardBody, AppCardHeader, AppTabs, AppTabsContent, AppTabsList, AppTabsTrigger, EmptyState, LoadingSkeleton, PageHeader } from '@/components/ui'
import { ImportCommitPanel } from '@/features/imports/components/import-commit-panel'
import { ImportErrorTable } from '@/features/imports/components/import-error-table'
import { ImportPreviewTable } from '@/features/imports/components/import-preview-table'
import { ImportStatusBadge } from '@/features/imports/components/import-status-badge'
import { ImportValidationSummary } from '@/features/imports/components/import-validation-summary'
import { importsApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import { useToast } from '@/components/shell/toast-host'

export function ImportDetailPage() {
  const { importId = '' } = useParams()
  const queryClient = useQueryClient()
  const { notify } = useToast()

  const detailQuery = useQuery({
    queryKey: queryKeys.imports.detail(importId),
    queryFn: () => importsApi.get(importId),
    enabled: Boolean(importId),
    refetchInterval: (query) => {
      const status = query.state.data?.data.import.status
      return ['validation_queued', 'validating', 'commit_queued', 'committing'].includes(status ?? '') ? 2000 : false
    }
  })

  const errorsQuery = useQuery({
    queryKey: queryKeys.imports.errors(importId),
    queryFn: () => importsApi.errors(importId),
    enabled: Boolean(importId)
  })

  const validateMutation = useMutation({
    mutationFn: async () => importsApi.validate(importId),
    onSuccess: async () => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.imports.detail(importId) }),
        queryClient.invalidateQueries({ queryKey: queryKeys.imports.all })
      ])
      notify({ title: 'Validation queued', description: 'The server is parsing and validating staged rows.', tone: 'info' })
    },
    onError: (error) => notify({ title: 'Validation failed to queue', description: error instanceof Error ? error.message : 'The server rejected the validation request.', tone: 'danger' })
  })

  const commitMutation = useMutation({
    mutationFn: async () => importsApi.commit(importId),
    onSuccess: async () => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.imports.detail(importId) }),
        queryClient.invalidateQueries({ queryKey: queryKeys.imports.all }),
        queryClient.invalidateQueries({ queryKey: queryKeys.notifications.all })
      ])
      notify({ title: 'Commit queued', description: 'Validated rows are being committed through governed service paths.', tone: 'info' })
    },
    onError: (error) => notify({ title: 'Commit request failed', description: error instanceof Error ? error.message : 'The server rejected the commit request.', tone: 'danger' })
  })

  if (detailQuery.isLoading) {
    return <LoadingSkeleton lines={12} />
  }

  const payload = detailQuery.data?.data.import
  if (!payload) {
    return <EmptyState title="Import not found" description="The requested import could not be loaded for this tenant." />
  }

  const errors = errorsQuery.data?.data.items ?? payload.previewErrors

  return (
    <div className="space-y-6">
      <PageHeader
        title="Import detail"
        description="Validation stays in staging until the server marks the file ready. Commit remains an explicit, auditable operation."
      />
      <div className="flex flex-wrap items-center justify-between gap-3">
        <Link to="/app/imports" className="body-sm text-primary hover:underline">
          ← Back to imports
        </Link>
        <ImportStatusBadge status={payload.status} />
      </div>

      <div className="grid gap-6 xl:grid-cols-[minmax(0,1.4fr)_380px]">
        <div className="space-y-6">
          <AppCard>
            <AppCardHeader>
              <div className="heading-md">{payload.originalFilename}</div>
              <div className="body-sm text-text-muted">{payload.importType} • {payload.fileFormat} • storage {payload.storageReference}</div>
            </AppCardHeader>
            <AppCardBody>
              <div className="grid gap-4 md:grid-cols-3 text-sm">
                <div>
                  <div className="label-sm uppercase tracking-[0.12em] text-text-muted">Uploaded</div>
                  <div className="mt-1 text-text">{payload.uploadedAt ? new Date(payload.uploadedAt).toLocaleString() : '—'}</div>
                </div>
                <div>
                  <div className="label-sm uppercase tracking-[0.12em] text-text-muted">Validated</div>
                  <div className="mt-1 text-text">{payload.validatedAt ? new Date(payload.validatedAt).toLocaleString() : 'Not yet'}</div>
                </div>
                <div>
                  <div className="label-sm uppercase tracking-[0.12em] text-text-muted">Committed</div>
                  <div className="mt-1 text-text">{payload.committedAt ? new Date(payload.committedAt).toLocaleString() : 'Not yet'}</div>
                </div>
              </div>
            </AppCardBody>
          </AppCard>

          <ImportValidationSummary
            rowCount={payload.rowCount}
            validRowCount={payload.validRowCount}
            invalidRowCount={payload.invalidRowCount}
            committedRowCount={payload.committedRowCount}
            warningCount={payload.summary.warningCount}
          />

          <AppTabs defaultValue="preview" className="space-y-4">
            <AppTabsList>
              <AppTabsTrigger value="preview">Preview rows</AppTabsTrigger>
              <AppTabsTrigger value="errors">Errors</AppTabsTrigger>
            </AppTabsList>
            <AppTabsContent value="preview">
              {payload.previewRows.length ? (
                <ImportPreviewTable rows={payload.previewRows} />
              ) : (
                <EmptyState title="No preview rows yet" description="Validate the staged file to populate normalized preview data." />
              )}
            </AppTabsContent>
            <AppTabsContent value="errors">
              {errors.length ? (
                <ImportErrorTable errors={errors} />
              ) : (
                <EmptyState title="No row-level errors" description="This import currently has no row errors in the returned server projection." />
              )}
            </AppTabsContent>
          </AppTabs>
        </div>

        <div className="space-y-6">
          <ImportCommitPanel
            canCommit={payload.canCommit}
            status={payload.status}
            onValidate={() => validateMutation.mutate()}
            onCommit={() => commitMutation.mutate()}
            validatePending={validateMutation.isPending}
            commitPending={commitMutation.isPending}
          />

          <AppCard>
            <AppCardHeader>
              <div className="heading-md">Diagnostics</div>
            </AppCardHeader>
            <AppCardBody>
              <div className="space-y-2 body-sm text-text-muted">
                <p>Latest correlation ID: <span className="font-medium text-text">{payload.latestCorrelationId ?? '—'}</span></p>
                <p>Failure summary: <span className="font-medium text-text">{payload.latestFailureSummary ? JSON.stringify(payload.latestFailureSummary) : 'None'}</span></p>
              </div>
            </AppCardBody>
          </AppCard>
        </div>
      </div>
    </div>
  )
}
