import { useEffect, useMemo, useState } from 'react'
import { useInfiniteQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { AppBadge, AppButton, AppCard, AppCardBody, AppCardHeader, AppInput, AppTabs, AppTabsContent, AppTabsList, AppTabsTrigger, AppTextarea, EmptyState, LoadingSkeleton } from '@/components/ui'
import { useToast } from '@/components/shell/toast-host'
import { CommunicationStatusBadge } from '@/features/communications/components/communication-status-badge'
import { communicationsApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import type { CommunicationTimelineItem } from '@/lib/api/client'

type FilterState = {
  channel: 'all' | 'sms' | 'email' | 'voice'
  status: 'all' | 'pending' | 'failed'
}

type Props = {
  clientId: string
  fallbackEmail?: string | null
  fallbackPhone?: string | null
  initialComposer?: 'sms' | 'email' | 'call'
}

export function ClientCommunicationsPanel({ clientId, fallbackEmail, fallbackPhone, initialComposer = 'sms' }: Props) {
  const queryClient = useQueryClient()
  const { notify } = useToast()
  const [filters, setFilters] = useState<FilterState>({ channel: 'all', status: 'all' })
  const [composerTab, setComposerTab] = useState<'sms' | 'email' | 'call'>(initialComposer)
  const [smsBody, setSmsBody] = useState('')
  const [smsFiles, setSmsFiles] = useState<File[]>([])
  const [emailTo, setEmailTo] = useState(fallbackEmail ?? '')
  const [emailCc, setEmailCc] = useState('')
  const [emailBcc, setEmailBcc] = useState('')
  const [emailSubject, setEmailSubject] = useState('')
  const [emailBody, setEmailBody] = useState('')
  const [emailFiles, setEmailFiles] = useState<File[]>([])
  const [callPurpose, setCallPurpose] = useState('')
  const [retryingItemId, setRetryingItemId] = useState<string | null>(null)

  useEffect(() => {
    setComposerTab(initialComposer)
  }, [initialComposer])

  const timelineQuery = useInfiniteQuery({
    queryKey: queryKeys.communications.clientTimeline(clientId, filters),
    queryFn: ({ pageParam }) => communicationsApi.list(clientId, {
      ...filters,
      limit: 20,
      cursor: typeof pageParam === 'string' ? pageParam : undefined,
    }),
    initialPageParam: undefined as string | undefined,
    getNextPageParam: (lastPage) => lastPage.data.paging.hasMore ? lastPage.data.paging.nextCursor : undefined,
    refetchInterval: (query) => query.state.data?.pages[0]?.data.refresh.hasPendingRecentItems ? 5000 : false,
  })

  const onMutationSuccess = async (title: string, description: string) => {
    await queryClient.invalidateQueries({ queryKey: queryKeys.communications.clientTimeline(clientId, filters) })
    await queryClient.invalidateQueries({ queryKey: queryKeys.clients.detail(clientId) })
    notify({ title, description, tone: 'success' })
  }

  const smsMutation = useMutation({
    mutationFn: async () => {
      const formData = new FormData()
      if (smsBody.trim()) formData.append('body', smsBody)
      smsFiles.forEach((file) => formData.append('attachments[]', file))
      if (fallbackPhone) formData.append('toPhone', fallbackPhone)
      return communicationsApi.sendSms(clientId, formData)
    },
    onSuccess: async () => {
      setSmsBody('')
      setSmsFiles([])
      await onMutationSuccess('SMS queued', 'The outbound message was persisted before provider submission and is now awaiting callbacks.')
    }
  })

  const emailMutation = useMutation({
    mutationFn: async () => {
      const formData = new FormData()
      emailTo.split(',').map((item) => item.trim()).filter(Boolean).forEach((item) => formData.append('to[]', item))
      emailCc.split(',').map((item) => item.trim()).filter(Boolean).forEach((item) => formData.append('cc[]', item))
      emailBcc.split(',').map((item) => item.trim()).filter(Boolean).forEach((item) => formData.append('bcc[]', item))
      formData.append('subject', emailSubject)
      formData.append('bodyText', emailBody)
      emailFiles.forEach((file) => formData.append('attachments[]', file))
      return communicationsApi.sendEmail(clientId, formData)
    },
    onSuccess: async () => {
      setEmailSubject('')
      setEmailBody('')
      setEmailFiles([])
      await onMutationSuccess('Email queued', 'The outbound email intent was persisted and queued for provider delivery.')
    }
  })

  const retryEmailMutation = useMutation({
    mutationFn: async (item: CommunicationTimelineItem) => {
      const formData = new FormData()
      formData.append('retryOfMessageId', item.id)
      formData.append('idempotencyKey', `email-retry:${item.id}:${Date.now()}`)
      return communicationsApi.sendEmail(clientId, formData)
    },
    onSuccess: async () => {
      await onMutationSuccess('Email retry queued', 'The failed email was cloned into a new governed resend attempt.')
    }
  })

  const callMutation = useMutation({
    mutationFn: async () => communicationsApi.startCall(clientId, { toPhone: fallbackPhone ?? '', purposeNote: callPurpose }),
    onSuccess: async () => {
      setCallPurpose('')
      await onMutationSuccess('Call queued', 'The outbound call log was created and will update from callback-driven lifecycle events.')
    }
  })

  const items = timelineQuery.data?.pages.flatMap((page) => page.data.items) ?? []
  const filterButtons = useMemo(() => ([{ key: 'all', label: 'All' }, { key: 'sms', label: 'SMS' }, { key: 'email', label: 'Email' }, { key: 'voice', label: 'Calls' }]) as const, [])

  return (
    <div className="space-y-6">
      <AppCard>
        <AppCardHeader>
          <div className="heading-md">Communications hub</div>
          <div className="body-sm text-text-muted">Outbound sends are persisted first, submitted asynchronously, and only move to terminal delivery states when callback evidence arrives.</div>
        </AppCardHeader>
        <AppCardBody>
          <AppTabs value={composerTab} onValueChange={(value) => setComposerTab(value as typeof composerTab)} className="space-y-4">
            <AppTabsList>
              <AppTabsTrigger value="sms">SMS / MMS</AppTabsTrigger>
              <AppTabsTrigger value="email">Email</AppTabsTrigger>
              <AppTabsTrigger value="call">Call</AppTabsTrigger>
            </AppTabsList>
            <AppTabsContent value="sms" className="space-y-4">
              <div className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_260px]">
                <div className="space-y-2"><label className="label-sm text-text">Message body</label><AppTextarea value={smsBody} onChange={(event) => setSmsBody(event.currentTarget.value)} placeholder="Send a governed SMS or MMS update from the client workspace." /></div>
                <div className="space-y-4">
                  <div className="space-y-2"><label className="label-sm text-text">Recipient</label><AppInput value={fallbackPhone ?? ''} readOnly /></div>
                  <div className="space-y-2">
                    <label className="label-sm text-text">Attachments</label>
                    <AppInput type="file" multiple onChange={(event) => setSmsFiles(Array.from(event.currentTarget.files ?? []))} />
                    <div className="text-xs text-text-muted">Attachments are persisted immediately, but provider delivery is blocked until security review marks them clean.</div>
                    {smsFiles.length ? <div className="flex flex-wrap gap-2">{smsFiles.map((file) => <AppBadge key={file.name}>{file.name}</AppBadge>)}</div> : null}
                  </div>
                  <AppButton type="button" onClick={() => smsMutation.mutate()} disabled={smsMutation.isPending || (!smsBody.trim() && smsFiles.length === 0) || !fallbackPhone}>{smsMutation.isPending ? 'Queueing…' : 'Queue SMS / MMS'}</AppButton>
                </div>
              </div>
            </AppTabsContent>
            <AppTabsContent value="email" className="space-y-4">
              <div className="grid gap-4 lg:grid-cols-2">
                <div className="space-y-2"><label className="label-sm text-text">To</label><AppInput value={emailTo} onChange={(event) => setEmailTo(event.currentTarget.value)} placeholder="name@example.com, second@example.com" /></div>
                <div className="space-y-2"><label className="label-sm text-text">Subject</label><AppInput value={emailSubject} onChange={(event) => setEmailSubject(event.currentTarget.value)} placeholder="Subject" /></div>
                <div className="space-y-2"><label className="label-sm text-text">CC</label><AppInput value={emailCc} onChange={(event) => setEmailCc(event.currentTarget.value)} placeholder="Optional CC list" /></div>
                <div className="space-y-2"><label className="label-sm text-text">BCC</label><AppInput value={emailBcc} onChange={(event) => setEmailBcc(event.currentTarget.value)} placeholder="Optional BCC list" /></div>
                <div className="space-y-2 lg:col-span-2"><label className="label-sm text-text">Body</label><AppTextarea value={emailBody} onChange={(event) => setEmailBody(event.currentTarget.value)} placeholder="Compose a governed email from the client workspace." /></div>
                <div className="space-y-2 lg:col-span-2">
                  <label className="label-sm text-text">Attachments</label>
                  <AppInput type="file" multiple onChange={(event) => setEmailFiles(Array.from(event.currentTarget.files ?? []))} />
                  <div className="text-xs text-text-muted">Email attachments also require a clean security review before SendGrid delivery is allowed.</div>
                  {emailFiles.length ? <div className="flex flex-wrap gap-2">{emailFiles.map((file) => <AppBadge key={file.name}>{file.name}</AppBadge>)}</div> : null}
                </div>
              </div>
              <AppButton type="button" onClick={() => emailMutation.mutate()} disabled={emailMutation.isPending || !emailTo.trim() || !emailSubject.trim() || !emailBody.trim()}>{emailMutation.isPending ? 'Queueing…' : 'Queue email'}</AppButton>
            </AppTabsContent>
            <AppTabsContent value="call" className="space-y-4">
              <div className="grid gap-4 lg:grid-cols-[260px_minmax(0,1fr)]">
                <div className="space-y-2"><label className="label-sm text-text">Recipient</label><AppInput value={fallbackPhone ?? ''} readOnly /></div>
                <div className="space-y-2"><label className="label-sm text-text">Purpose note</label><AppTextarea value={callPurpose} onChange={(event) => setCallPurpose(event.currentTarget.value)} placeholder="Optional purpose or talking points." /></div>
              </div>
              <AppButton type="button" onClick={() => callMutation.mutate()} disabled={callMutation.isPending || !fallbackPhone}>{callMutation.isPending ? 'Queueing…' : 'Initiate call'}</AppButton>
            </AppTabsContent>
          </AppTabs>
        </AppCardBody>
      </AppCard>
      <AppCard>
        <AppCardHeader>
          <div className="flex flex-wrap items-center justify-between gap-4">
            <div><div className="heading-md">Timeline</div><div className="body-sm text-text-muted">Statuses shown below are projected from canonical records and callback evidence, not optimistic browser assumptions.</div></div>
            <div className="flex gap-2">{filterButtons.map((filter) => <AppButton key={filter.key} type="button" variant={filters.channel === filter.key ? 'primary' : 'secondary'} onClick={() => setFilters((current) => ({ ...current, channel: filter.key }))}>{filter.label}</AppButton>)}<AppButton type="button" variant={filters.status === 'failed' ? 'primary' : 'secondary'} onClick={() => setFilters((current) => ({ ...current, status: current.status === 'failed' ? 'all' : 'failed' }))}>Failures</AppButton><AppButton type="button" variant="secondary" onClick={() => timelineQuery.refetch()}>Refresh</AppButton></div>
          </div>
        </AppCardHeader>
        <AppCardBody>
          {timelineQuery.isLoading ? <LoadingSkeleton lines={6} /> : items.length ? <div className="space-y-4">{items.map((item) => <TimelineItem key={item.id} item={item} isRetrying={retryingItemId === item.id} onRetry={item.kind === 'message' && item.channel === 'email' && item.actions.canRetry ? async () => {
            setRetryingItemId(item.id)
            try {
              await retryEmailMutation.mutateAsync(item)
            } finally {
              setRetryingItemId(null)
            }
          } : undefined} />)}</div> : <EmptyState title="No communications yet" description="Queue the first SMS, email, or call to begin a governed communication timeline for this client." />}
          {timelineQuery.hasNextPage ? (
            <div className="mt-4 flex justify-center">
              <AppButton type="button" variant="secondary" onClick={() => timelineQuery.fetchNextPage()} disabled={timelineQuery.isFetchingNextPage}>
                {timelineQuery.isFetchingNextPage ? 'Loading older activity…' : 'Load older activity'}
              </AppButton>
            </div>
          ) : null}
        </AppCardBody>
      </AppCard>
    </div>
  )
}

function TimelineItem({ item, onRetry, isRetrying = false }: { item: CommunicationTimelineItem, onRetry?: () => Promise<void> | void, isRetrying?: boolean }) {
  const hasFailure = item.status.tone === 'danger'

  return (
    <div className="rounded-lg border border-border bg-muted p-4">
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div className="space-y-2">
          <div className="flex flex-wrap items-center gap-2"><AppBadge>{item.channel.toUpperCase()}</AppBadge><AppBadge>{item.direction}</AppBadge><CommunicationStatusBadge status={item.status} /></div>
          <div className="font-medium text-text">{item.content.subject ?? item.content.preview ?? 'Communication activity'}</div>
          {item.counterpart.address ? <div className="body-sm text-text-muted">{item.counterpart.address}</div> : null}
        </div>
        <div className="text-xs text-text-muted">{item.occurredAt ? new Date(item.occurredAt).toLocaleString() : '—'}</div>
      </div>
      {item.content.bodyText ? <div className="body-sm mt-3 whitespace-pre-wrap text-text-muted">{item.content.bodyText}</div> : null}
      {item.attachments.length ? <div className="mt-3 flex flex-wrap gap-2">{item.attachments.map((attachment) => <AppBadge key={attachment.id}>{attachment.originalFilename} • {attachment.scanStatus}</AppBadge>)}</div> : null}
      <div className="mt-3 body-sm text-text-muted">Evidence source: {item.evidence.source.replaceAll('_', ' ')}{item.evidence.lastEventAt ? ` • Last update ${new Date(item.evidence.lastEventAt).toLocaleString()}` : ''}</div>
      {hasFailure && item.status.reasonMessage ? <div className="mt-3 rounded-md border border-danger/20 bg-danger/5 px-3 py-2 text-sm text-danger">{item.status.reasonMessage}</div> : null}
      {onRetry ? <div className="mt-3"><AppButton type="button" variant="secondary" onClick={() => void onRetry()} disabled={isRetrying}>{isRetrying ? 'Queueing resend…' : 'Retry email'}</AppButton></div> : null}
    </div>
  )
}
