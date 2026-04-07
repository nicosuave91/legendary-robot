import type {
  ApplicationSummary,
  CalendarEventSummary,
  ClientNoteSummary,
  ClientWorkspaceResponse,
  CommunicationTimelineItem,
} from '@/lib/api/generated/client'

export type ClientWorkspaceRecommendedAction = {
  code:
    | 'review_blocking_application'
    | 'retry_failed_communication'
    | 'prepare_upcoming_event'
    | 'follow_up_client'
    | 'no_urgent_action'
  title: string
  description: string
  ctaLabel: string | null
  ctaHref: string | null
  tone: 'neutral' | 'info' | 'warning' | 'danger' | 'success'
}

export type ClientWorkspaceOverview = {
  recommendedAction: ClientWorkspaceRecommendedAction
  latestCommunication: CommunicationTimelineItem | null
  nextEvent: CalendarEventSummary | null
  leadApplication: ApplicationSummary | null
  recentNote: ClientNoteSummary | null
}

export type ClientWorkspaceResponseWithOverview = ClientWorkspaceResponse & {
  overview?: ClientWorkspaceOverview
}
