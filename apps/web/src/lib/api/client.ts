import { apiHttpClient } from '@/lib/api/http'
import {
  deleteSettingsAccount,
  getAudit,
  getAuthMe,
  getCalendarDay,
  getClient,
  getClientApplication,
  getClientApplications,
  getClientEvents,
  getClients,
  getDashboardProduction,
  getDashboardSummary,
  getEvent,
  getEvents,
  getImport,
  getImportErrors,
  getImports,
  getNotifications,
  getOnboardingState,
  getRule,
  getRuleExecutionLogs,
  getRules,
  getSettingsAccounts,
  getSettingsIndustryConfigurations,
  getSettingsProfile,
  getSettingsTheme,
  getWorkflow,
  getWorkflowRun,
  getWorkflowRuns,
  getWorkflows,
  patchClient,
  patchEvent,
  patchOnboardingIndustrySelection,
  patchOnboardingProfileConfirmation,
  patchRule,
  patchSettingsAccount,
  patchSettingsProfile,
  patchSettingsTheme,
  patchTaskStatus,
  patchWorkflow,
  postAuthSignIn,
  postAuthSignOut,
  postClientApplicationStatusTransitions,
  postClientApplications,
  postClientCommunicationsCall,
  postClientCommunicationsEmail,
  postClientCommunicationsSms,
  postClientDispositionTransitions,
  postClientDocuments,
  postClientNotes,
  postClients,
  postEvents,
  postImportCommit,
  postImports,
  postImportValidate,
  postNotificationDismiss,
  postNotificationRead,
  postOnboardingComplete,
  postRulePublish,
  postRules,
  postSettingsAccounts,
  postSettingsIndustryConfigurations,
  postWorkflowPublish,
  postWorkflows,
  type AuditListEnvelope,
  type ClientCommunicationsEnvelope,
  type CreateAccountRequest,
  type CreateApplicationRequest,
  type CreateClientNoteRequest,
  type CreateClientRequest,
  type CreateEventRequest,
  type CreateIndustryConfigurationRequest,
  type CreateRuleRequest,
  type CreateWorkflowRequest,
  type DashboardProductionEnvelope,
  type DismissNotificationRequest,
  type DispositionTransitionRequest,
  type EventDetailEnvelope,
  type EventListEnvelope,
  type ImportDetailEnvelope,
  type ImportErrorEnvelope,
  type ImportListEnvelope,
  type IndustrySelectionRequest,
  type NotificationDismissEnvelope,
  type NotificationListEnvelope,
  type NotificationReadEnvelope,
  type ProfileConfirmationRequest,
  type PublishLifecycleRequest,
  type RuleDetailEnvelope,
  type RuleExecutionLogEnvelope,
  type RuleListEnvelope,
  type SignInRequest,
  type StartCallRequest,
  type TaskStatusTransitionEnvelope,
  type ThemeSummary,
  type TransitionApplicationStatusRequest,
  type UpdateAccountRequest,
  type UpdateClientRequest,
  type UpdateEventRequest,
  type UpdateProfileRequest,
  type UpdateRuleDraftRequest,
  type UpdateTaskStatusRequest,
  type UpdateWorkflowDraftRequest,
  type WorkflowDetailEnvelope,
  type WorkflowListEnvelope,
  type WorkflowRunDetailEnvelope,
  type WorkflowRunListEnvelope,
} from '@/lib/api/generated/client'

type ApiMeta = {
  apiVersion: string
  correlationId: string
}

export type CommunicationTimelineItem = ClientCommunicationsEnvelope['data']['items'][number]

export type ClientCommunicationsQuery = {
  channel?: 'all' | 'sms' | 'email' | 'voice'
  status?: 'all' | 'pending' | 'failed'
  limit?: number
  cursor?: string
}

export type CommunicationsInboxQuery = {
  search?: string
  channel?: 'all' | 'sms' | 'email' | 'voice'
  status?: 'all' | 'pending' | 'failed'
  limit?: number
  cursor?: string
}

export type CommunicationAttachmentScanStatusUpdateRequest = {
  status: 'pending' | 'clean' | 'rejected' | 'quarantined'
  engine?: string | null
  detail?: string | null
  quarantineReason?: string | null
}

export type CommunicationAttachmentGovernanceSummary = {
  id: string
  originalFilename: string
  mimeType: string
  sizeBytes: number
  scanStatus: string
  scanRequestedAt: string | null
  scannedAt: string | null
  scanEngine: string | null
  scanResultDetail: string | null
  quarantineReason: string | null
}

export type CommunicationAttachmentGovernanceEnvelope = {
  data: CommunicationAttachmentGovernanceSummary
  meta: ApiMeta
}

export type CommunicationsInboxItem = {
  client: {
    id: string
    displayName: string
    status: string
    ownerDisplayName: string | null
    primaryEmail: string | null
    primaryPhone: string | null
    lastActivityAt: string | null
  }
  timelineItem: CommunicationTimelineItem
}

export type CommunicationsInboxEnvelope = {
  data: {
    items: CommunicationsInboxItem[]
    paging: {
      nextCursor: string | null
      hasMore: boolean
    }
    filters: {
      search: string | null
      channel: string
      status: string
    }
    refresh: {
      hasPendingRecentItems: boolean
      recommendedPollSeconds: number | null
    }
    summary: {
      clientCount: number
      itemCount: number
    }
  }
  meta: ApiMeta
}

export const authApi = {
  me: () => getAuthMe(apiHttpClient),
  signIn: (body: SignInRequest) => postAuthSignIn(apiHttpClient, body),
  signOut: () => postAuthSignOut(apiHttpClient)
}

export const onboardingApi = {
  state: () => getOnboardingState(apiHttpClient),
  confirmProfile: (body: ProfileConfirmationRequest) => patchOnboardingProfileConfirmation(apiHttpClient, body),
  selectIndustry: (body: IndustrySelectionRequest) => patchOnboardingIndustrySelection(apiHttpClient, body),
  complete: () => postOnboardingComplete(apiHttpClient)
}

export const profileApi = {
  get: () => getSettingsProfile(apiHttpClient),
  update: (body: UpdateProfileRequest) => patchSettingsProfile(apiHttpClient, body)
}

export const themeApi = {
  get: () => getSettingsTheme(apiHttpClient),
  update: (body: ThemeSummary) => patchSettingsTheme(apiHttpClient, body)
}

export const accountsApi = {
  list: () => getSettingsAccounts(apiHttpClient),
  create: (body: CreateAccountRequest) => postSettingsAccounts(apiHttpClient, body),
  update: (userId: string, body: UpdateAccountRequest) => patchSettingsAccount(apiHttpClient, { userId }, body),
  decommission: (userId: string) => deleteSettingsAccount(apiHttpClient, { userId })
}

export const industryConfigurationsApi = {
  list: () => getSettingsIndustryConfigurations(apiHttpClient),
  create: (body: CreateIndustryConfigurationRequest) => postSettingsIndustryConfigurations(apiHttpClient, body)
}

export const dashboardApi = {
  summary: () => getDashboardSummary(apiHttpClient),
  production: (window: string) => getDashboardProduction(apiHttpClient, { window })
}

export const calendarApi = {
  day: (date: string) => getCalendarDay(apiHttpClient, { date }),
  list: (queryParams: Parameters<typeof getEvents>[1]): Promise<EventListEnvelope> => getEvents(apiHttpClient, queryParams),
  create: (body: CreateEventRequest): Promise<EventDetailEnvelope> => postEvents(apiHttpClient, body),
  get: (eventId: string): Promise<EventDetailEnvelope> => getEvent(apiHttpClient, { eventId }),
  update: (eventId: string, body: UpdateEventRequest): Promise<EventDetailEnvelope> => patchEvent(apiHttpClient, { eventId }, body),
  updateTaskStatus: (taskId: string, body: UpdateTaskStatusRequest): Promise<TaskStatusTransitionEnvelope> => patchTaskStatus(apiHttpClient, { taskId }, body),
  clientEvents: (clientId: string, queryParams?: Parameters<typeof getClientEvents>[2]) => getClientEvents(apiHttpClient, { clientId }, queryParams)
}

export const clientsApi = {
  list: (queryParams?: Parameters<typeof getClients>[1]) => getClients(apiHttpClient, queryParams),
  create: (body: CreateClientRequest) => postClients(apiHttpClient, body),
  get: (clientId: string) => getClient(apiHttpClient, { clientId }),
  update: (clientId: string, body: UpdateClientRequest) => patchClient(apiHttpClient, { clientId }, body),
  createNote: (clientId: string, body: CreateClientNoteRequest) => postClientNotes(apiHttpClient, { clientId }, body),
  uploadDocument: (clientId: string, body: FormData) => postClientDocuments(apiHttpClient, { clientId }, body),
  transitionDisposition: (clientId: string, body: DispositionTransitionRequest) => postClientDispositionTransitions(apiHttpClient, { clientId }, body)
}

export const communicationsApi = {
  inbox: (queryParams?: CommunicationsInboxQuery) =>
    apiHttpClient.request<CommunicationsInboxEnvelope>({
      path: '/api/v1/communications/inbox',
      method: 'GET',
      queryParams
    }),
  list: (clientId: string, queryParams?: ClientCommunicationsQuery) =>
    apiHttpClient.request<ClientCommunicationsEnvelope>({
      path: `/api/v1/clients/${clientId}/communications`,
      method: 'GET',
      queryParams
    }),
  sendSms: (clientId: string, body: FormData) => postClientCommunicationsSms(apiHttpClient, { clientId }, body),
  sendEmail: (clientId: string, body: FormData) => postClientCommunicationsEmail(apiHttpClient, { clientId }, body),
  startCall: (clientId: string, body: StartCallRequest) => postClientCommunicationsCall(apiHttpClient, { clientId }, body),
  updateAttachmentScanStatus: (attachmentId: string, body: CommunicationAttachmentScanStatusUpdateRequest) =>
    apiHttpClient.request<CommunicationAttachmentGovernanceEnvelope>({
      path: `/api/v1/communications/attachments/${attachmentId}/scan-status`,
      method: 'PATCH',
      body,
      contentType: 'application/json'
    })
}

export const applicationsApi = {
  list: (clientId: string) => getClientApplications(apiHttpClient, { clientId }),
  create: (clientId: string, body: CreateApplicationRequest) => postClientApplications(apiHttpClient, { clientId }, body),
  get: (clientId: string, applicationId: string) => getClientApplication(apiHttpClient, { clientId, applicationId }),
  transitionStatus: (clientId: string, applicationId: string, body: TransitionApplicationStatusRequest) =>
    postClientApplicationStatusTransitions(apiHttpClient, { clientId, applicationId }, body)
}

export const rulesApi = {
  list: (queryParams?: Parameters<typeof getRules>[1]): Promise<RuleListEnvelope> => getRules(apiHttpClient, queryParams),
  create: (body: CreateRuleRequest): Promise<RuleDetailEnvelope> => postRules(apiHttpClient, body),
  get: (ruleId: string): Promise<RuleDetailEnvelope> => getRule(apiHttpClient, { ruleId }),
  updateDraft: (ruleId: string, body: UpdateRuleDraftRequest): Promise<RuleDetailEnvelope> => patchRule(apiHttpClient, { ruleId }, body),
  publish: (ruleId: string, body: PublishLifecycleRequest = {}): Promise<RuleDetailEnvelope> => postRulePublish(apiHttpClient, { ruleId }, body),
  executionLogs: (ruleId: string): Promise<RuleExecutionLogEnvelope> => getRuleExecutionLogs(apiHttpClient, { ruleId })
}

export const workflowsApi = {
  list: (queryParams?: Parameters<typeof getWorkflows>[1]): Promise<WorkflowListEnvelope> => getWorkflows(apiHttpClient, queryParams),
  create: (body: CreateWorkflowRequest): Promise<WorkflowDetailEnvelope> => postWorkflows(apiHttpClient, body),
  get: (workflowId: string): Promise<WorkflowDetailEnvelope> => getWorkflow(apiHttpClient, { workflowId }),
  updateDraft: (workflowId: string, body: UpdateWorkflowDraftRequest): Promise<WorkflowDetailEnvelope> => patchWorkflow(apiHttpClient, { workflowId }, body),
  publish: (workflowId: string, body: PublishLifecycleRequest = {}): Promise<WorkflowDetailEnvelope> => postWorkflowPublish(apiHttpClient, { workflowId }, body),
  runs: (workflowId: string): Promise<WorkflowRunListEnvelope> => getWorkflowRuns(apiHttpClient, { workflowId }),
  run: (workflowId: string, runId: string): Promise<WorkflowRunDetailEnvelope> => getWorkflowRun(apiHttpClient, { workflowId, runId })
}

export const importsApi = {
  list: (queryParams?: Parameters<typeof getImports>[1]): Promise<ImportListEnvelope> => getImports(apiHttpClient, queryParams),
  create: (body: FormData): Promise<ImportDetailEnvelope> => postImports(apiHttpClient, body),
  get: (importId: string): Promise<ImportDetailEnvelope> => getImport(apiHttpClient, { importId }),
  errors: (importId: string, queryParams?: Parameters<typeof getImportErrors>[2]): Promise<ImportErrorEnvelope> => getImportErrors(apiHttpClient, { importId }, queryParams),
  validate: (importId: string): Promise<ImportDetailEnvelope> => postImportValidate(apiHttpClient, { importId }),
  commit: (importId: string): Promise<ImportDetailEnvelope> => postImportCommit(apiHttpClient, { importId })
}

export const notificationsApi = {
  list: (queryParams?: Parameters<typeof getNotifications>[1]): Promise<NotificationListEnvelope> => getNotifications(apiHttpClient, queryParams),
  dismiss: (notificationId: string, body: DismissNotificationRequest = {}): Promise<NotificationDismissEnvelope> => postNotificationDismiss(apiHttpClient, { notificationId }, body),
  read: (notificationId: string): Promise<NotificationReadEnvelope> => postNotificationRead(apiHttpClient, { notificationId })
}

export const auditApi = {
  list: (queryParams?: Parameters<typeof getAudit>[1]): Promise<AuditListEnvelope> => getAudit(apiHttpClient, queryParams)
}
