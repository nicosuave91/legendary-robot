import { useMutation, useQueryClient } from '@tanstack/react-query'
import { AppBadge, AppButton, AppCard, AppCardBody, AppCardHeader, EmptyState } from '@/components/ui'
import { applicationsApi } from '@/lib/api/client'
import { ApiError } from '@/lib/api/http'
import { queryKeys } from '@/lib/api/query-keys'
import { useToast } from '@/components/shell/toast-host'
import { ApplicationRuleNoteList } from '@/features/applications/components/application-rule-note-list'
import { ApplicationStatusTimeline } from '@/features/applications/components/application-status-timeline'

type TransitionIssue = {
  code: string
  message: string
}

type ApplicationStatusCode = 'draft' | 'submitted' | 'in_review' | 'approved' | 'declined' | 'withdrawn'

type ApplicationDetailPayload = {
  application: {
    id: string
    applicationNumber: string
    productType: string
    currentStatus: {
      label: string
      tone: string
      changedAt?: string | null
    }
    ownerDisplayName?: string | null
    externalReference?: string | null
    amountRequested?: number | string | null
    submittedAt?: string | null
    availableStatusTransitions: Array<{
      code: ApplicationStatusCode
      label: string
    }>
  }
  statusHistory: Array<{
    id: string
    toStatus: string
    occurredAt?: string | null
    actorDisplayName?: string | null
    reason?: string | null
  }>
  ruleNotes: Array<{
    id: string
    outcome: 'blocking' | 'warning' | 'info'
    title: string
    body: string
    ruleKey: string
    ruleVersion: string
    appliedAt?: string | null
  }>
}

type Props = {
  clientId: string
  payload?: ApplicationDetailPayload
}

function toneToVariant(tone: string) {
  return tone === 'success' ? 'success' : tone === 'warning' ? 'warning' : tone === 'danger' ? 'danger' : tone === 'info' ? 'info' : 'neutral'
}

export function ApplicationDetailPanel({ clientId, payload }: Props) {
  const queryClient = useQueryClient()
  const { notify } = useToast()

  const transitionMutation = useMutation({
    mutationFn: (body: { applicationId: string, targetStatus: ApplicationStatusCode }) =>
      applicationsApi.transitionStatus(clientId, body.applicationId, { targetStatus: body.targetStatus }),
    onSuccess: async (response) => {
      const data = (response as { data: { warnings: Array<{ message: string }> } }).data
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.applications.list(clientId) }),
        queryClient.invalidateQueries({ queryKey: queryKeys.clients.detail(clientId) })
      ])
      notify({
        title: data.warnings.length ? 'Application updated with warnings' : 'Application updated',
        description: data.warnings.length
          ? data.warnings.map((warning) => warning.message).join(' ')
          : 'Status history and rule evidence refreshed from the server.',
        tone: data.warnings.length ? 'warning' : 'success'
      })
    },
    onError: (error) => {
      if (error instanceof ApiError && typeof error.payload === 'object' && error.payload && 'data' in error.payload) {
        const blockingIssues = (((error.payload as { data?: { blockingIssues?: TransitionIssue[] } }).data?.blockingIssues) ?? [])
        notify({
          title: 'Status transition blocked',
          description: blockingIssues.map((issue) => issue.message).join(' ') || 'The application transition was rejected by server rules.',
          tone: 'danger'
        })
        return
      }

      notify({
        title: 'Status transition failed',
        description: 'The request could not be completed.',
        tone: 'danger'
      })
    }
  })

  if (!payload) {
    return <EmptyState title="Select an application" description="Choose an application from the list to review its summary, timeline, and immutable rule notes." />
  }

  const application = payload.application

  return (
    <div className="space-y-4">
      <AppCard>
        <AppCardHeader>
          <div className="heading-md">Application summary</div>
          <div className="body-sm text-text-muted">Application state moves through governed status transitions with append-only history and rule evidence.</div>
        </AppCardHeader>
        <AppCardBody>
          <div className="flex flex-wrap items-start justify-between gap-4">
            <div className="space-y-2">
              <div className="heading-md text-text">{application.applicationNumber}</div>
              <div className="body-sm text-text-muted">{application.productType}</div>
              <div className="flex flex-wrap items-center gap-2">
                <AppBadge variant={toneToVariant(application.currentStatus.tone)}>{application.currentStatus.label}</AppBadge>
                <span className="text-xs text-text-muted">Updated {application.currentStatus.changedAt ? new Date(application.currentStatus.changedAt).toLocaleString() : '—'}</span>
              </div>
            </div>

            <div className="grid gap-2 text-sm text-text-muted">
              <div>Owner: {application.ownerDisplayName ?? 'Unassigned'}</div>
              <div>External ref: {application.externalReference ?? '—'}</div>
              <div>Amount requested: {application.amountRequested ?? '—'}</div>
              <div>Submitted at: {application.submittedAt ? new Date(application.submittedAt).toLocaleString() : 'Draft only'}</div>
            </div>
          </div>

          <div className="mt-4">
            <div className="label-sm uppercase tracking-[0.12em] text-text-muted">Available status transitions</div>
            <div className="mt-2 flex flex-wrap gap-2">
              {application.availableStatusTransitions.length ? application.availableStatusTransitions.map((transition) => (
                <AppButton
                  key={transition.code}
                  type="button"
                  variant="secondary"
                  disabled={transitionMutation.isPending}
                  onClick={() => {
                    void transitionMutation.mutateAsync({ applicationId: application.id, targetStatus: transition.code })
                  }}
                >
                  {transition.label}
                </AppButton>
              )) : <span className="body-sm text-text-muted">No further transitions available.</span>}
            </div>
          </div>
        </AppCardBody>
      </AppCard>

      <AppCard>
        <AppCardHeader>
          <div className="heading-md">Status history</div>
        </AppCardHeader>
        <AppCardBody>
          <ApplicationStatusTimeline items={payload.statusHistory} />
        </AppCardBody>
      </AppCard>

      <AppCard>
        <AppCardHeader>
          <div className="heading-md">Rule notes</div>
        </AppCardHeader>
        <AppCardBody>
          <ApplicationRuleNoteList items={payload.ruleNotes} />
        </AppCardBody>
      </AppCard>
    </div>
  )
}
