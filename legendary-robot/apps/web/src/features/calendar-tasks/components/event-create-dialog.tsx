import { useEffect, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { AppButton, AppDialog, AppDialogContent, AppInput, AppSelect, AppTextarea } from '@/components/ui'
import { toLocalDateTimeInputValue } from '@/features/calendar-tasks/calendar-utils'
import { calendarApi, clientsApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import { useToast } from '@/components/shell/toast-host'

type EventCreateDialogProps = {
  open: boolean
  onOpenChange: (open: boolean) => void
  selectedDate: string
  initialClientId?: string | null
}

export function EventCreateDialog({
  open,
  onOpenChange,
  selectedDate,
  initialClientId = null,
}: EventCreateDialogProps) {
  const queryClient = useQueryClient()
  const { notify } = useToast()
  const [title, setTitle] = useState('')
  const [description, setDescription] = useState('')
  const [eventType, setEventType] = useState<'appointment' | 'follow_up' | 'document_review' | 'call' | 'deadline' | 'task_batch'>('appointment')
  const [startsAt, setStartsAt] = useState(toLocalDateTimeInputValue(new Date(`${selectedDate}T09:00:00`)))
  const [endsAt, setEndsAt] = useState(toLocalDateTimeInputValue(new Date(`${selectedDate}T09:30:00`)))
  const [clientId, setClientId] = useState(initialClientId ?? '')
  const [taskTitle, setTaskTitle] = useState('')

  useEffect(() => {
    setStartsAt(toLocalDateTimeInputValue(new Date(`${selectedDate}T09:00:00`)))
    setEndsAt(toLocalDateTimeInputValue(new Date(`${selectedDate}T09:30:00`)))
  }, [selectedDate])

  useEffect(() => {
    setClientId(initialClientId ?? '')
  }, [initialClientId, open])

  const clientsQuery = useQuery({
    queryKey: queryKeys.clients.list({ perPage: 50 }),
    queryFn: () => clientsApi.list({ perPage: 50 }),
  })

  const createMutation = useMutation({
    mutationFn: () => calendarApi.create({
      title,
      description,
      eventType,
      startsAt: new Date(startsAt).toISOString(),
      endsAt: endsAt ? new Date(endsAt).toISOString() : undefined,
      clientId: clientId || undefined,
      tasks: taskTitle ? [{ title: taskTitle, isRequired: true, sortOrder: 0 }] : undefined,
    }),
    onSuccess: async () => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.calendar.all }),
        queryClient.invalidateQueries({ queryKey: queryKeys.clients.all }),
        ...(clientId ? [queryClient.invalidateQueries({ queryKey: queryKeys.clients.detail(clientId) })] : []),
      ])
      notify({ title: 'Event created', description: 'The new governed event is now available from homepage, calendar, and linked client surfaces.', tone: 'success' })
      setTitle('')
      setDescription('')
      setClientId(initialClientId ?? '')
      setTaskTitle('')
      onOpenChange(false)
    },
  })

  return (
    <AppDialog open={open} onOpenChange={onOpenChange}>
      <AppDialogContent>
        <div className="space-y-4">
          <div>
            <div className="heading-md">Create event</div>
            <div className="body-sm text-text-muted">Use the generated contract-backed API to add a governed event and optional initial task.</div>
          </div>
          <div className="space-y-2"><label className="label-sm text-text">Title</label><AppInput value={title} onChange={(event) => setTitle(event.currentTarget.value)} /></div>
          <div className="space-y-2"><label className="label-sm text-text">Description</label><AppTextarea value={description} onChange={(event) => setDescription(event.currentTarget.value)} /></div>
          <div className="grid gap-4 md:grid-cols-2">
            <div className="space-y-2"><label className="label-sm text-text">Event type</label><AppSelect value={eventType} onChange={(event) => setEventType(event.currentTarget.value as typeof eventType)}><option value="appointment">Appointment</option><option value="follow_up">Follow up</option><option value="document_review">Document review</option><option value="call">Call</option><option value="deadline">Deadline</option><option value="task_batch">Task batch</option></AppSelect></div>
            <div className="space-y-2">
              <label className="label-sm text-text">Linked client</label>
              <AppSelect value={clientId} onChange={(event) => setClientId(event.currentTarget.value)}>
                <option value="">No client</option>
                {clientsQuery.data?.data.items.map((client) => <option key={client.id} value={client.id}>{client.displayName}</option>)}
              </AppSelect>
            </div>
          </div>
          <div className="grid gap-4 md:grid-cols-2">
            <div className="space-y-2"><label className="label-sm text-text">Starts at</label><AppInput type="datetime-local" value={startsAt} onChange={(event) => setStartsAt(event.currentTarget.value)} /></div>
            <div className="space-y-2"><label className="label-sm text-text">Ends at</label><AppInput type="datetime-local" value={endsAt} onChange={(event) => setEndsAt(event.currentTarget.value)} /></div>
          </div>
          <div className="space-y-2"><label className="label-sm text-text">Optional first task</label><AppInput value={taskTitle} onChange={(event) => setTaskTitle(event.currentTarget.value)} placeholder="Confirm intake packet completeness" /></div>
          <div className="flex justify-end gap-2">
            <AppButton type="button" variant="secondary" onClick={() => onOpenChange(false)}>Cancel</AppButton>
            <AppButton type="button" onClick={() => createMutation.mutate()} disabled={createMutation.isPending || title.trim().length < 2}>{createMutation.isPending ? 'Creating…' : 'Create event'}</AppButton>
          </div>
        </div>
      </AppDialogContent>
    </AppDialog>
  )
}
