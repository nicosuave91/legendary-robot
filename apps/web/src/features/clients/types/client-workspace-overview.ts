import type {
  ApplicationRuleSummary,
  ApplicationStatusSummary,
  ClientWorkspaceResponse,
  EventTaskSummary,
} from '@/lib/api/generated/client'

export type ClientWorkspaceActionTone = 'neutral' | 'info' | 'warning' | 'danger' | 'success'

export type ClientWorkspaceRecommendedAction = {
  code: string
  title: string
  description: string
  ctaLabel: string
  ctaHref: string
  tone: ClientWorkspaceActionTone
}

export type ClientWorkspaceCommunicationStatus = {
  label: string
  tone: ClientWorkspaceActionTone
}

export type ClientWorkspaceLatestCommunication = {
  id: string
  channel: string
  direction: string
  occurredAt: string | null
  preview: string | null
  status: ClientWorkspaceCommunicationStatus
}

export type ClientWorkspaceNextEvent = {
  id: string
  title: string
  eventType: string
  startsAt: string | null
  endsAt: string | null
  taskSummary: EventTaskSummary
}

export type ClientWorkspaceLeadApplication = {
  id: string
  applicationNumber: string
  productType: string
  currentStatus: ApplicationStatusSummary
  ruleSummary: ApplicationRuleSummary
}

export type ClientWorkspaceRecentNote = {
  id: string
  body: string
  authorDisplayName: string
  createdAt: string | null
}

export type ClientWorkspaceOverview = {
  recommendedAction: ClientWorkspaceRecommendedAction
  latestCommunication: ClientWorkspaceLatestCommunication | null
  nextEvent: ClientWorkspaceNextEvent | null
  leadApplication: ClientWorkspaceLeadApplication | null
  recentNote: ClientWorkspaceRecentNote | null
}

export type ClientWorkspaceResponseWithOverview = ClientWorkspaceResponse & {
  overview?: ClientWorkspaceOverview | null
}
