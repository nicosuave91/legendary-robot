import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { AppBadge, AppButton, AppDrawer, AppDrawerContent, EmptyState, LoadingSkeleton } from '@/components/ui'
import { dateKey, formatTimeRange } from '@/features/calendar-tasks/calendar-utils'
import { calendarApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import { useToast } from '@/components/shell/toast-host'
import { useNavigate } from 'react-router-dom'

type EventDetailDrawerProps = {
  eventId?: string | null
  open: boolean
  onOpenChange: (open: boolean) => void
}

export function EventDetailDrawer({ eventId, open, onOpenChange }: EventDetailDrawerProps) {
  const navigate = useNavigate()
  const queryClient = useQueryClient()
  const { notify } = useToast()

  const eventQuery = useQuery({
    enabled: open && Boolean(eventId),
    queryKey: queryKeys.calendar.event(eventId ?? ''),
    queryFn: () => calendarApi.get(eventId ?? ''),
  })

  const payload = eventQuery.data?.data

  const taskMutation = useMutation({
    mutationFn: async ({ taskId, targetStatus }: { taskId: string; targetStatus: 'open' | 'completed' | 'skipped' | 'blocked' }) => {
      const blockedReason = targetStatus === 'blocked' ? window.prompt('Enter a blocked reason') ?? '' : undefined
      const reason = targetStatus === 'open' && payload ? 'Task reopened from event detail.' : undefined
      return calendarApi.updateTaskStatus(taskId, { targetStatus, blockedReason, reason })
    },
    onSuccess: async (_, variables) => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.calendar.all }),
        queryClient.invalidateQueries({ queryKey: queryKeys.clients.all }),
      ])
      notify({ title: 'Task updated', description: `Task status changed to ${variables.targetStatus}. History stayed durable on the server.`, tone: 'success' })
    },
  })

  return (
    <AppDrawer open={open} onOpenChange={onOpenChange}>
      <AppDrawerContent className="overflow-y-auto">
        {!payload && eventQuery.isLoading ? <LoadingSkeleton lines={8} /> : null}
        {!payload && !eventQuery.isLoading ? <EmptyState title="Event detail unavailable" description="The selected event could not be resolved from the calendar API." /> : null}
        {payload ? (
          <div className="space-y-6">
            <div>
              <div className="label-sm uppercase tracking-[0.12em] text-text-muted">Event detail</div>
              <div className="heading-lg mt-1 text-text">{payload.title}</div>
              <div className="body-sm mt-1 text-text-muted">{formatTimeRange(payload.startsAt, payload.endsAt, payload.isAllDay)}</div>
              <div className="mt-3 flex flex-wrap gap-2">
                <AppBadge variant="neutral">{payload.eventType.replace(/_/g, ' ')}</AppBadge>
                <AppBadge variant="info">{payload.taskSummary.open} open</AppBadge>
                <AppBadge variant="success">{payload.taskSummary.completed} done</AppBadge>
                {payload.taskSummary.blocked ? <AppBadge variant="warning">{payload.taskSummary.blocked} blocked</AppBadge> : null}
              </div>
              {payload.description ? <div className="body-sm mt-3 text-text-muted">{payload.description}</div> : null}
            </div>

            {payload.client ? (
              <div className="rounded-lg border border-border bg-muted p-4">
                <div className="font-medium text-text">Linked file</div>
                <div className="body-sm mt-1 text-text-muted">{payload.client.displayName}</div>
                <div className="mt-3 flex flex-wrap gap-2">
                  <AppButton type="button" onClick={() => navigate(`/app/clients/${payload.client?.id}/events`)}>Open file</AppButton>
                  <AppButton type="button" variant="secondary" onClick={() => navigate(`/app/calendar?date=${payload.startsAt ? dateKey(new Date(payload.startsAt)) : ''}&eventId=${payload.id}`)}>Open in calendar</AppButton>
                </div>
              </div>
            ) : null}

            <div>
              <div className="heading-md">Required tasks</div>
              <div className="mt-3 space-y-4">
                {payload.tasks.map((task) => (
                  <div key={task.id} className="rounded-lg border border-border bg-surface p-4 shadow-xs">
                    <div className="flex flex-wrap items-start justify-between gap-3">
                      <div>
                        <div className="flex flex-wrap items-center gap-2">
                          <div className="font-medium text-text">{task.title}</div>
                          <AppBadge variant={task.status === 'completed' ? 'success' : task.status === 'blocked' ? 'warning' : 'neutral'}>{task.status}</AppBadge>
                          {task.isRequired ? <AppBadge variant="info">Required</AppBadge> : null}
                        </div>
                        {task.description ? <div className="body-sm mt-1 text-text-muted">{task.description}</div> : null}
                        {task.blockedReason ? <div className="body-sm mt-1 text-warning">Blocked: {task.blockedReason}</div> : null}
                      </div>
                      <div className="flex flex-wrap gap-2">
                        {task.availableActions.map((action) => (
                          <AppButton key={action} type="button" variant={action === 'completed' ? 'secondary' : 'ghost'} onClick={() => taskMutation.mutate({ taskId: task.id, targetStatus: action as 'open' | 'completed' | 'skipped' | 'blocked' })} disabled={taskMutation.isPending}>
                            {action === 'open' ? 'Reopen' : action.charAt(0).toUpperCase() + action.slice(1)}
                          </AppButton>
                        ))}
                      </div>
                    </div>
                    {task.history.length ? (
                      <div className="mt-3 space-y-2 border-t border-border pt-3">
                        <div className="label-sm uppercase tracking-[0.12em] text-text-muted">History</div>
                        {task.history.map((entry) => (
                          <div key={entry.id} className="body-sm text-text-muted">{entry.actorDisplayName} moved {entry.fromStatus ?? 'new'} → {entry.toStatus} on {entry.occurredAt ? new Date(entry.occurredAt).toLocaleString() : '—'}{entry.reason ? ` · ${entry.reason}` : ''}</div>
                        ))}
                      </div>
                    ) : null}
                  </div>
                ))}
              </div>
            </div>
          </div>
        ) : null}
      </AppDrawerContent>
    </AppDrawer>
  )
}
