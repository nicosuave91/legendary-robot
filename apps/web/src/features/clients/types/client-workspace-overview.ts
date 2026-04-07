import type {
  ApplicationRuleSummary,
  ApplicationStatusSummary,
  DeliveryStatusProjection,
  EventTaskSummary,
  WorkflowDetailEnvelope,
  WorkflowDraftValidationSummary,
} from '@/lib/api/generated/client'

export type ClientWorkspaceRecommendedAction = {
  code: string
  title: string
  description: string
  tone: 'neutral' | 'info' | 'success' | 'warning' | 'danger'
  ctaLabel: string | null
  ctaHref: string | null
}

export type ClientWorkspaceLatestCommunication = {
  id: string
  channel: 'sms' | 'mms' | 'email' | 'voice'
  direction: 'inbound' | 'outbound' | 'system'
  occurredAt: string | null
  preview: string | null
  status: DeliveryStatusProjection
}

export type ClientWorkspaceNextEvent = {
  id: string
  title: string
  eventType: 'appointment' | 'follow_up' | 'document_review' | 'call' | 'deadline' | 'task_batch'
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

export type WorkflowDetailEnvelopeAligned = WorkflowDetailEnvelope & {
  data: WorkflowDetailEnvelope['data'] & {
    draftValidation: WorkflowDraftValidationSummary
  }
}
