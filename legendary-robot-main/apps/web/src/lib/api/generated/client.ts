/* eslint-disable */
/**
 * AUTO-GENERATED FILE.
 * Source: packages/contracts/openapi.json
 * Regenerate with: npm run client:generate
 */

export type HttpMethod = 'GET' | 'POST' | 'PATCH' | 'PUT' | 'DELETE';

export interface ApiRequestOptions {
  method: HttpMethod;
  path: string;
  body?: unknown;
  pathParams?: Record<string, string | number>;
  queryParams?: Record<string, string | number | boolean | null | undefined>;
  contentType?: 'application/json' | 'multipart/form-data';
}

export interface ApiHttpClient {
  request<T>(options: ApiRequestOptions): Promise<T>;
}

export type MessageResponse = {
  "message": string;
};

export type ResponseMeta = {
  "apiVersion": "v1";
  "correlationId": string;
};

export type SignInRequest = {
  "email": string;
  "password": string;
};

export type AuthContextEnvelope = {
  "data": AuthContextResponse;
  "meta": ResponseMeta;
};

export type AuthContextResponse = {
  "isAuthenticated": boolean;
  "user": UserSummary;
  "tenant": TenantSummary;
  "roles": (string)[];
  "permissions": (string)[];
  "onboardingState": "not_applicable" | "required" | "in_progress" | "completed";
  "onboardingStep": "profile_confirmation" | "industry_selection" | "completion" | null;
  "theme": ThemeSummary;
  "landingRoute": string;
  "selectedIndustry": "Legal" | "Medical" | "Mortgage" | null;
  "selectedIndustryConfigVersion": string | null;
  "capabilities": (string)[];
};

export type UserSummary = {
  "id": string;
  "email": string;
  "displayName": string;
};

export type TenantSummary = {
  "id": string;
  "name": string;
};

export type ThemeSummary = {
  "primary": string;
  "secondary": string;
  "tertiary": string;
};

export type ThemeEnvelope = {
  "data": ThemeSummary;
  "meta": ResponseMeta;
};

export type OnboardingStateEnvelope = {
  "data": OnboardingStateResponse;
  "meta": ResponseMeta;
};

export type OnboardingStateResponse = {
  "state": "not_applicable" | "required" | "in_progress" | "completed";
  "currentStep": "profile_confirmation" | "industry_selection" | "completion" | null;
  "isBypassed": boolean;
  "availableIndustries": ("Legal" | "Medical" | "Mortgage")[];
  "selectedIndustry": "Legal" | "Medical" | "Mortgage" | null;
  "selectedIndustryConfigVersion": string | null;
  "profile": ProfileSnapshot;
  "canComplete": boolean;
};

export type ProfileSnapshot = {
  "firstName": string;
  "lastName": string;
  "phone": string;
  "birthday": string | null;
  "addressLine1": string;
  "addressLine2": string;
  "city": string;
  "stateCode": string;
  "postalCode": string;
};

export type ProfileResponse = {
  "userId": string;
  "email": string;
  "displayName": string;
  "firstName": string;
  "lastName": string;
  "phone": string;
  "birthday": string | null;
  "addressLine1": string;
  "addressLine2": string;
  "city": string;
  "stateCode": string;
  "postalCode": string;
};

export type ProfileEnvelope = {
  "data": ProfileResponse;
  "meta": ResponseMeta;
};

export type ProfileConfirmationRequest = {
  "firstName": string;
  "lastName": string;
  "phone": string;
  "birthday"?: string | null;
  "addressLine1": string;
  "addressLine2"?: string;
  "city": string;
  "stateCode": string;
  "postalCode": string;
};

export type UpdateProfileRequest = {
  "displayName": string;
  "firstName": string;
  "lastName": string;
  "phone": string;
  "birthday"?: string | null;
  "addressLine1": string;
  "addressLine2"?: string;
  "city": string;
  "stateCode": string;
  "postalCode": string;
};

export type IndustrySelectionRequest = {
  "industry": "Legal" | "Medical" | "Mortgage";
};

export type AccountSummary = {
  "id": string;
  "email": string;
  "displayName": string;
  "roles": (string)[];
  "status": "active" | "deactivated";
  "onboardingState": "not_applicable" | "required" | "in_progress" | "completed";
  "selectedIndustry": "Legal" | "Medical" | "Mortgage" | null;
  "selectedIndustryConfigVersion": string | null;
  "firstName": string;
  "lastName": string;
};

export type AccountListEnvelope = {
  "data": (AccountSummary)[];
  "meta": ResponseMeta;
};

export type AccountEnvelope = {
  "data": AccountSummary;
  "meta": ResponseMeta;
};

export type CreateAccountRequest = {
  "email": string;
  "displayName": string;
  "role": "admin" | "user";
  "password": string;
  "firstName"?: string;
  "lastName"?: string;
};

export type UpdateAccountRequest = {
  "displayName": string;
  "role": "admin" | "user";
  "status": "active" | "deactivated";
  "firstName"?: string;
  "lastName"?: string;
};

export type IndustryConfigurationSummary = {
  "id": string;
  "industry": "Legal" | "Medical" | "Mortgage";
  "version": string;
  "status": "draft" | "published";
  "isActive": boolean;
  "capabilities": (string)[];
  "notes": string | null;
  "publishedAt": string | null;
  "activatedAt": string | null;
};

export type IndustryConfigurationListEnvelope = {
  "data": (IndustryConfigurationSummary)[];
  "meta": ResponseMeta;
};

export type IndustryConfigurationEnvelope = {
  "data": IndustryConfigurationSummary;
  "meta": ResponseMeta;
};

export type CreateIndustryConfigurationRequest = {
  "industry": "Legal" | "Medical" | "Mortgage";
  "status": "draft" | "published";
  "activate"?: boolean;
  "capabilities": (string)[];
  "notes"?: string;
};

export type DashboardHero = {
  "greeting": string;
  "userDisplayName": string;
  "tenantName": string;
  "selectedIndustry": string | null;
  "selectedIndustryConfigVersion": string | null;
  "subtitle": string;
};

export type DashboardKpiDelta = {
  "direction": "up" | "down" | "flat";
  "value": number;
  "label": string;
};

export type DashboardKpiCard = {
  "key": "clients_total" | "clients_new_7d" | "notes_7d" | "documents_7d";
  "label": string;
  "value": number;
  "description": string;
  "href": string;
  "delta": DashboardKpiDelta;
};

export type DashboardActivitySummary = {
  "visibleClientCount": number;
  "recentNoteCount": number;
  "recentDocumentCount": number;
};

export type DashboardSummaryResponse = {
  "hero": DashboardHero;
  "kpis": (DashboardKpiCard)[];
  "activitySummary": DashboardActivitySummary;
  "calendarPanelEnabled": boolean;
};

export type DashboardSummaryEnvelope = {
  "data": DashboardSummaryResponse;
  "meta": ResponseMeta;
};

export type DashboardRange = {
  "window": "7d" | "30d" | "90d";
  "startDate": string;
  "endDate": string;
  "granularity": "day";
};

export type ProductionPoint = {
  "bucketDate": string;
  "value": number;
};

export type ProductionSeries = {
  "key": "clientsCreated" | "notesCreated" | "documentsUploaded";
  "label": string;
  "points": (ProductionPoint)[];
};

export type ProductionTotals = {
  "clientsCreated": number;
  "notesCreated": number;
  "documentsUploaded": number;
};

export type DashboardProductionResponse = {
  "range": DashboardRange;
  "series": (ProductionSeries)[];
  "totals": ProductionTotals;
};

export type DashboardProductionEnvelope = {
  "data": DashboardProductionResponse;
  "meta": ResponseMeta;
};

export type ClientAddressSummary = {
  "addressLine1": string | null;
  "addressLine2": string | null;
  "city": string | null;
  "stateCode": string | null;
  "postalCode": string | null;
};

export type ClientDetail = {
  "id": string;
  "displayName": string;
  "firstName": string | null;
  "lastName": string | null;
  "companyName": string | null;
  "status": "lead" | "qualified" | "applied" | "active" | "inactive";
  "primaryEmail": string | null;
  "primaryPhone": string | null;
  "preferredContactChannel": "email" | "sms" | "phone" | null | null;
  "dateOfBirth": string | null;
  "ownerUserId": string | null;
  "ownerDisplayName": string | null;
  "address": ClientAddressSummary | unknown;
  "createdAt": string | null;
  "updatedAt": string | null;
};

export type ClientEnvelope = {
  "data": ClientDetail;
  "meta": ResponseMeta;
};

export type ClientListItem = {
  "id": string;
  "displayName": string;
  "status": "lead" | "qualified" | "applied" | "active" | "inactive";
  "primaryEmail": string | null;
  "primaryPhone": string | null;
  "city": string | null;
  "stateCode": string | null;
  "ownerDisplayName": string | null;
  "notesCount": number;
  "documentsCount": number;
  "lastActivityAt": string | null;
  "createdAt": string | null;
  "updatedAt": string | null;
};

export type ClientListPagination = {
  "page": number;
  "perPage": number;
  "total": number;
  "totalPages": number;
};

export type ClientListAppliedFilters = {
  "search": string | null;
  "status": "lead" | "active" | "inactive" | null | null;
  "sort": "display_name" | "created_at" | "updated_at" | "last_activity_at";
  "direction": "asc" | "desc";
};

export type ClientListResponse = {
  "items": (ClientListItem)[];
  "pagination": ClientListPagination;
  "appliedFilters": ClientListAppliedFilters;
};

export type ClientListEnvelope = {
  "data": ClientListResponse;
  "meta": ResponseMeta;
};

export type ClientNoteSummary = {
  "id": string;
  "sourceType": "user" | "system";
  "body": string;
  "isEditable": boolean;
  "authorDisplayName": string;
  "createdAt": string | null;
};

export type ClientDocumentSummary = {
  "id": string;
  "originalFilename": string;
  "mimeType": string;
  "sizeBytes": number;
  "provenance": "manual_upload";
  "attachmentCategory": string | null;
  "uploadedByDisplayName": string;
  "uploadedAt": string | null;
  "storageReference": string;
};

export type ClientAuditSummary = {
  "id": string;
  "action": string;
  "actorDisplayName": string;
  "subjectType": string;
  "createdAt": string | null;
};

export type ClientWorkspaceTab = {
  "key": string;
  "label": string;
  "href": string;
  "available": boolean;
};

export type ClientWorkspaceSummary = {
  "notesCount": number;
  "documentsCount": number;
  "eventsCount": number;
  "applicationsCount": number;
  "lastActivityAt": string | null;
};

export type ClientWorkspaceResponse = {
  "client": ClientDetail;
  "currentDisposition": DispositionProjection;
  "availableDispositionTransitions": (DispositionTransitionOption)[];
  "dispositionHistory": (DispositionHistoryItem)[];
  "summary": ClientWorkspaceSummary;
  "recentNotes": (ClientNoteSummary)[];
  "recentDocuments": (ClientDocumentSummary)[];
  "recentAudit": (ClientAuditSummary)[];
  "tabs": (ClientWorkspaceTab)[];
};

export type ClientWorkspaceEnvelope = {
  "data": ClientWorkspaceResponse;
  "meta": ResponseMeta;
};

export type CreateOrUpdateClientRequest = {
  "displayName": string;
  "firstName"?: string | null;
  "lastName"?: string | null;
  "companyName"?: string | null;
  "primaryEmail"?: string | null;
  "primaryPhone"?: string | null;
  "preferredContactChannel"?: "email" | "sms" | "phone" | null | null;
  "dateOfBirth"?: string | null;
  "status"?: "lead" | "active" | "inactive" | null | null;
  "ownerUserId"?: string | null;
  "addressLine1"?: string | null;
  "addressLine2"?: string | null;
  "city"?: string | null;
  "stateCode"?: string | null;
  "postalCode"?: string | null;
};

export type CreateClientNoteRequest = {
  "body": string;
};

export type CreateClientDocumentRequest = {
  "file": File;
  "attachmentCategory"?: string | null;
};

export type ClientNoteEnvelope = {
  "data": ClientNoteSummary;
  "meta": ResponseMeta;
};

export type ClientDocumentEnvelope = {
  "data": ClientDocumentSummary;
  "meta": ResponseMeta;
};

export type SendSmsRequest = {
  "body"?: string | null;
  "toPhone"?: string | null;
  "idempotencyKey"?: string | null;
  "retryOfMessageId"?: string | null;
  "attachments"?: (File)[];
};

export type SendEmailRequest = {
  "to": (string)[];
  "cc"?: (string)[];
  "bcc"?: (string)[];
  "subject": string;
  "bodyText"?: string | null;
  "bodyHtml"?: string | null;
  "idempotencyKey"?: string | null;
  "retryOfMessageId"?: string | null;
  "attachments"?: (File)[];
};

export type StartCallRequest = {
  "toPhone"?: string | null;
  "purposeNote"?: string | null;
  "idempotencyKey"?: string | null;
  "retryOfCallLogId"?: string | null;
};

export type CommunicationAttachmentSummary = {
  "id": string;
  "originalFilename": string;
  "mimeType": string;
  "sizeBytes": number;
  "provenance": string;
  "storageReference": string;
  "scanStatus": string;
};

export type DeliveryStatusProjection = {
  "lifecycle": string;
  "providerStatus": string | null;
  "displayLabel": string;
  "tone": "neutral" | "success" | "warning" | "danger";
  "isTerminal": boolean;
  "updatedAt": string | null;
  "reasonCode": string | null;
  "reasonMessage": string | null;
  "source": "internal" | "provider_submit" | "provider_callback";
};

export type CommunicationTimelineCounterpart = {
  "name": string | null;
  "address": string | null;
};

export type CommunicationTimelineContent = {
  "subject": string | null;
  "bodyText": string | null;
  "preview": string | null;
};

export type CommunicationTimelineEvidence = {
  "source": "internal" | "provider_submit" | "provider_callback";
  "lastEventAt": string | null;
  "lastEventType": string | null;
  "eventCount": number;
};

export type CommunicationTimelineCall = {
  "durationSeconds": number | null;
};

export type CommunicationTimelineActions = {
  "canRetry": boolean;
};

export type CommunicationTimelineItem = {
  "id": string;
  "kind": "message" | "call" | "system_event";
  "channel": "sms" | "mms" | "email" | "voice";
  "direction": "inbound" | "outbound" | "system";
  "occurredAt": string | null;
  "counterpart": CommunicationTimelineCounterpart;
  "content": CommunicationTimelineContent;
  "attachments": (CommunicationAttachmentSummary)[];
  "status": DeliveryStatusProjection;
  "evidence": CommunicationTimelineEvidence;
  "call": CommunicationTimelineCall | unknown;
  "actions": CommunicationTimelineActions;
};

export type ClientCommunicationsResponse = {
  "clientId": string;
  "items": (CommunicationTimelineItem)[];
  "paging": {
  "nextCursor": string | null;
  "hasMore": boolean;
};
  "filters": {
  "channel": string;
  "status": string;
};
  "refresh": {
  "hasPendingRecentItems": boolean;
  "recommendedPollSeconds": number | null;
};
};

export type ClientCommunicationsEnvelope = {
  "data": ClientCommunicationsResponse;
  "meta": ResponseMeta;
};

export type CommunicationTimelineItemEnvelope = {
  "data": CommunicationTimelineItem;
  "meta": ResponseMeta;
};

export type DispositionProjection = {
  "code": string;
  "label": string;
  "tone": "neutral" | "success" | "warning" | "danger" | "info";
  "isTerminal": boolean;
  "changedAt": string | null;
  "changedByDisplayName": string | null;
};

export type DispositionTransitionOption = {
  "code": string;
  "label": string;
  "tone": "neutral" | "success" | "warning" | "danger" | "info";
};

export type DispositionHistoryItem = {
  "id": string;
  "fromDispositionCode": string | null;
  "toDispositionCode": string;
  "reason": string | null;
  "occurredAt": string | null;
  "actorDisplayName": string | null;
};

export type TransitionIssue = {
  "code": string;
  "message": string;
  "severity": "warning" | "blocking";
};

export type DispositionTransitionRequest = {
  "targetDispositionCode": string;
  "reason"?: string | null;
  "acknowledgeWarnings"?: boolean;
};

export type DispositionTransitionResponse = {
  "result": "transitioned" | "blocked" | "warning_confirmation_required";
  "currentDisposition": DispositionProjection;
  "availableTransitions": (DispositionTransitionOption)[];
  "warnings": (TransitionIssue)[];
  "blockingIssues": (TransitionIssue)[];
  "historyEntry": DispositionHistoryItem | unknown;
};

export type DispositionTransitionEnvelope = {
  "data": DispositionTransitionResponse;
  "meta": ResponseMeta;
};

export type CreateClientRequest = {
  "displayName": string;
  "firstName"?: string | null;
  "lastName"?: string | null;
  "companyName"?: string | null;
  "primaryEmail"?: string | null;
  "primaryPhone"?: string | null;
  "preferredContactChannel"?: "email" | "sms" | "phone" | null | null;
  "dateOfBirth"?: string | null;
  "ownerUserId"?: string | null;
  "addressLine1"?: string | null;
  "addressLine2"?: string | null;
  "city"?: string | null;
  "stateCode"?: string | null;
  "postalCode"?: string | null;
};

export type UpdateClientRequest = {
  "displayName": string;
  "firstName"?: string | null;
  "lastName"?: string | null;
  "companyName"?: string | null;
  "primaryEmail"?: string | null;
  "primaryPhone"?: string | null;
  "preferredContactChannel"?: "email" | "sms" | "phone" | null | null;
  "dateOfBirth"?: string | null;
  "ownerUserId"?: string | null;
  "addressLine1"?: string | null;
  "addressLine2"?: string | null;
  "city"?: string | null;
  "stateCode"?: string | null;
  "postalCode"?: string | null;
};

export type ApplicationStatusTransitionOption = {
  "code": string;
  "label": string;
  "tone": "neutral" | "success" | "warning" | "danger" | "info";
};

export type ApplicationStatusSummary = {
  "code": string;
  "label": string;
  "tone": "neutral" | "success" | "warning" | "danger" | "info";
  "changedAt": string | null;
};

export type ApplicationRuleSummary = {
  "infoCount": number;
  "warningCount": number;
  "blockingCount": number;
  "lastAppliedAt": string | null;
};

export type ApplicationSummary = {
  "id": string;
  "applicationNumber": string;
  "productType": string;
  "ownerDisplayName": string | null;
  "currentStatus": ApplicationStatusSummary;
  "ruleSummary": ApplicationRuleSummary;
  "createdAt": string | null;
  "updatedAt": string | null;
};

export type ClientApplicationsListResponse = {
  "items": (ApplicationSummary)[];
  "meta": {
  "total": number;
};
};

export type ClientApplicationsListEnvelope = {
  "data": ClientApplicationsListResponse;
  "meta": ResponseMeta;
};

export type ApplicationStatusHistoryItem = {
  "id": string;
  "fromStatus": string | null;
  "toStatus": string;
  "reason": string | null;
  "occurredAt": string | null;
  "actorDisplayName": string | null;
};

export type ApplicationRuleNote = {
  "id": string;
  "ruleKey": string;
  "ruleVersion": string;
  "outcome": "info" | "warning" | "blocking";
  "title": string;
  "body": string;
  "appliedAt": string | null;
  "isViewOnly": boolean;
};

export type ApplicationDetailApplication = {
  "id": string;
  "applicationNumber": string;
  "productType": string;
  "ownerDisplayName": string | null;
  "currentStatus": ApplicationStatusSummary;
  "ruleSummary": ApplicationRuleSummary;
  "createdAt": string | null;
  "updatedAt": string | null;
  "externalReference": string | null;
  "amountRequested": string | null;
  "submittedAt": string | null;
  "availableStatusTransitions": (ApplicationStatusTransitionOption)[];
};

export type ApplicationDetailResponse = {
  "application": ApplicationDetailApplication;
  "statusHistory": (ApplicationStatusHistoryItem)[];
  "ruleNotes": (ApplicationRuleNote)[];
};

export type ApplicationDetailEnvelope = {
  "data": ApplicationDetailResponse;
  "meta": ResponseMeta;
};

export type CreateApplicationRequest = {
  "productType": string;
  "ownerUserId"?: string | null;
  "externalReference"?: string | null;
  "amountRequested"?: number | null;
  "submittedAt"?: string | null;
  "metadata"?: {

} | null;
};

export type TransitionApplicationStatusRequest = {
  "targetStatus": "draft" | "submitted" | "in_review" | "approved" | "declined" | "withdrawn";
  "submittedAt"?: string | null;
  "reason"?: string | null;
};

export type ApplicationTransitionResponse = {
  "result": "transitioned" | "blocked";
  "blockingIssues": (TransitionIssue)[];
  "warnings": (TransitionIssue)[];
  "application": ApplicationDetailResponse;
};

export type ApplicationTransitionEnvelope = {
  "data": ApplicationTransitionResponse;
  "meta": ResponseMeta;
};

export type PublishLifecycleRequest = {
  "publishNotes"?: string | null;
};

export type RuleListItem = {
  "id": string;
  "ruleKey": string;
  "name": string;
  "description": string | null;
  "moduleScope": string;
  "subjectType": string;
  "status": string;
  "latestPublishedVersionNumber": number | null;
  "currentDraftVersionNumber": number | null;
  "latestPublishedAt": string | null;
  "updatedAt": string | null;
};

export type RuleVersionDto = {
  "id": string;
  "versionNumber": number;
  "lifecycleState": string;
  "triggerEvent": string;
  "severity": string;
  "conditionDefinition": {

};
  "actionDefinition": {

};
  "executionLabel": string | null;
  "noteTemplate": string | null;
  "checksum": string;
  "publishedAt": string | null;
  "publishedBy": string | null;
  "createdAt": string | null;
  "updatedAt": string | null;
};

export type RuleExecutionLogDto = {
  "id": string;
  "ruleId": string;
  "ruleVersionId": string;
  "subjectType": string;
  "subjectId": string;
  "triggerEvent": string;
  "executionSource": string;
  "outcome": string;
  "correlationId": string | null;
  "actorUserId": string | null;
  "contextSnapshot": {

};
  "outcomeSummary": {

};
  "executedAt": string | null;
};

export type RuleDetailResponse = {
  "rule": RuleListItem;
  "versions": (RuleVersionDto)[];
  "executionLogs": (RuleExecutionLogDto)[];
};

export type RuleListResponse = {
  "items": (RuleListItem)[];
  "meta": {
  "total": number;
};
};

export type RuleExecutionLogResponse = {
  "items": (RuleExecutionLogDto)[];
  "meta": {
  "total": number;
};
};

export type CreateRuleRequest = {
  "ruleKey": string;
  "name": string;
  "description"?: string | null;
  "moduleScope": string;
  "subjectType": string;
  "triggerEvent": string;
  "severity": string;
  "industryScope"?: {

} | null;
  "conditionDefinition": {

};
  "actionDefinition": {

};
  "executionLabel"?: string | null;
  "noteTemplate"?: string | null;
};

export type UpdateRuleDraftRequest = {
  "name"?: string;
  "description"?: string | null;
  "moduleScope"?: string;
  "subjectType"?: string;
  "triggerEvent"?: string;
  "severity"?: string;
  "industryScope"?: {

} | null;
  "conditionDefinition"?: {

};
  "actionDefinition"?: {

};
  "executionLabel"?: string | null;
  "noteTemplate"?: string | null;
};

export type WorkflowListItem = {
  "id": string;
  "workflowKey": string;
  "name": string;
  "description": string | null;
  "status": string;
  "triggerSummary": string;
  "latestPublishedVersionNumber": number | null;
  "currentDraftVersionNumber": number | null;
  "latestPublishedAt": string | null;
  "updatedAt": string | null;
};

export type WorkflowVersionDto = {
  "id": string;
  "versionNumber": number;
  "lifecycleState": string;
  "triggerDefinition": {

};
  "stepsDefinition": ({

})[];
  "checksum": string;
  "publishedAt": string | null;
  "publishedBy": string | null;
  "createdAt": string | null;
  "updatedAt": string | null;
};

export type WorkflowRunDto = {
  "id": string;
  "workflowId": string;
  "workflowVersionId": string;
  "triggerEvent": string;
  "subjectType": string;
  "subjectId": string;
  "status": string;
  "currentStepIndex": number | null;
  "correlationId": string | null;
  "queuedAt": string | null;
  "startedAt": string | null;
  "completedAt": string | null;
  "failedAt": string | null;
  "failureSummary": {

};
};

export type WorkflowRunLogDto = {
  "id": string;
  "workflowRunId": string;
  "workflowVersionId": string;
  "stepIndex": number | null;
  "logType": string;
  "message": string;
  "payloadSnapshot": {

};
  "occurredAt": string | null;
};

export type WorkflowDetailResponse = {
  "workflow": WorkflowListItem;
  "versions": (WorkflowVersionDto)[];
  "meta": {

};
};

export type WorkflowRunDetailResponse = {
  "run": WorkflowRunDto;
  "logs": (WorkflowRunLogDto)[];
};

export type WorkflowListResponse = {
  "items": (WorkflowListItem)[];
  "meta": {
  "total": number;
};
};

export type WorkflowRunListResponse = {
  "items": (WorkflowRunDto)[];
  "meta": {
  "total": number;
};
};

export type CreateWorkflowRequest = {
  "workflowKey": string;
  "name": string;
  "description"?: string | null;
  "triggerDefinition": {

};
  "stepsDefinition": ({

})[];
};

export type UpdateWorkflowDraftRequest = {
  "name"?: string;
  "description"?: string | null;
  "triggerDefinition"?: {

};
  "stepsDefinition"?: ({

})[];
};

export type RuleListEnvelope = {
  "data": RuleListResponse;
  "meta": ResponseMeta;
};

export type RuleDetailEnvelope = {
  "data": RuleDetailResponse;
  "meta": ResponseMeta;
};

export type RuleExecutionLogEnvelope = {
  "data": RuleExecutionLogResponse;
  "meta": ResponseMeta;
};

export type WorkflowListEnvelope = {
  "data": WorkflowListResponse;
  "meta": ResponseMeta;
};

export type WorkflowDetailEnvelope = {
  "data": WorkflowDetailResponse;
  "meta": ResponseMeta;
};

export type WorkflowRunListEnvelope = {
  "data": WorkflowRunListResponse;
  "meta": ResponseMeta;
};

export type WorkflowRunDetailEnvelope = {
  "data": WorkflowRunDetailResponse;
  "meta": ResponseMeta;
};

export type ImportListItem = {
  "id": string;
  "importType": string;
  "fileFormat": string;
  "originalFilename": string;
  "status": string;
  "rowCount": number;
  "validRowCount": number;
  "invalidRowCount": number;
  "committedRowCount": number;
  "uploadedByUserId": string | null;
  "validatedByUserId": string | null;
  "committedByUserId": string | null;
  "parserVersion": string | null;
  "canValidate": boolean;
  "canCommit": boolean;
  "uploadedAt": string | null;
  "validatedAt": string | null;
  "committedAt": string | null;
};

export type ImportPreviewRow = {
  "id": string;
  "rowNumber": number;
  "rowStatus": string;
  "normalizedPayload": {

};
  "targetSubjectType": string | null;
  "targetSubjectId": string | null;
};

export type ImportErrorItem = {
  "id": string;
  "rowNumber": number;
  "fieldName": string | null;
  "errorCode": string;
  "severity": string;
  "message": string;
  "contextSnapshot": {

};
};

export type ImportDetailSummary = {
  "blockingErrorCount": number;
  "warningCount": number;
  "createdTargetCount": number;
};

export type ImportDetailResponse = {
  "import": unknown;
};

export type ImportListResponse = {
  "items": (ImportListItem)[];
  "meta": {
  "total": number;
};
};

export type ImportErrorResponse = {
  "items": (ImportErrorItem)[];
  "meta": {
  "total": number;
};
};

export type CreateImportRequest = {
  "importType": string;
  "file": File;
};

export type NotificationListItem = {
  "id": string;
  "category": string;
  "notificationType": string;
  "title": string;
  "body": string | null;
  "tone": string;
  "actionUrl": string | null;
  "sourceEventType": string;
  "sourceEventId": string | null;
  "isRead": boolean;
  "readAt": string | null;
  "isDismissed": boolean;
  "dismissedAt": string | null;
  "emittedAt": string | null;
  "payloadSnapshot": {

};
};

export type NotificationListResponse = {
  "items": (NotificationListItem)[];
  "meta": {
  "total": number;
  "unread": number;
};
};

export type DismissNotificationRequest = {
  "surface"?: string | null;
};

export type NotificationDismissResponse = {
  "notificationId": string;
  "dismissed": boolean;
  "dismissedAt": string | null;
  "surface": string;
};

export type NotificationReadResponse = {
  "notificationId": string;
  "read": boolean;
  "readAt": string | null;
};

export type AuditListItem = {
  "id": number;
  "action": string;
  "subjectType": string;
  "subjectId": string | null;
  "actorId": string | null;
  "actorDisplayName": string | null;
  "correlationId": string | null;
  "beforeSummary": {

};
  "afterSummary": {

};
  "occurredAt": string | null;
};

export type AuditListResponse = {
  "items": (AuditListItem)[];
  "meta": {
  "total": number;
  "page": number;
  "perPage": number;
};
};

export type ImportListEnvelope = {
  "data": ImportListResponse;
  "meta": ResponseMeta;
};

export type ImportDetailEnvelope = {
  "data": ImportDetailResponse;
  "meta": ResponseMeta;
};

export type ImportErrorEnvelope = {
  "data": ImportErrorResponse;
  "meta": ResponseMeta;
};

export type NotificationListEnvelope = {
  "data": NotificationListResponse;
  "meta": ResponseMeta;
};

export type NotificationDismissEnvelope = {
  "data": NotificationDismissResponse;
  "meta": ResponseMeta;
};

export type NotificationReadEnvelope = {
  "data": NotificationReadResponse;
  "meta": ResponseMeta;
};

export type AuditListEnvelope = {
  "data": AuditListResponse;
  "meta": ResponseMeta;
};

export type CalendarSummaryCounts = {
  "eventCount": number;
  "openTaskCount": number;
  "completedTaskCount": number;
  "blockedTaskCount": number;
  "skippedTaskCount": number;
};

export type CalendarLinkedRecordSummary = {
  "id": string;
  "displayName": string;
};

export type EventTaskSummary = {
  "total": number;
  "open": number;
  "completed": number;
  "blocked": number;
  "skipped": number;
};

export type CalendarEventSummary = {
  "id": string;
  "title": string;
  "description": string | null;
  "eventType": "appointment" | "follow_up" | "document_review" | "call" | "deadline" | "task_batch";
  "status": "scheduled" | "completed" | "cancelled";
  "startsAt": string | null;
  "endsAt": string | null;
  "isAllDay": boolean;
  "location": string | null;
  "client": CalendarLinkedRecordSummary | null;
  "owner": CalendarLinkedRecordSummary | null;
  "taskSummary": EventTaskSummary;
};

export type EventTaskHistoryItem = {
  "id": string;
  "fromStatus": string | null;
  "toStatus": "open" | "completed" | "skipped" | "blocked";
  "reason": string | null;
  "occurredAt": string | null;
  "actorDisplayName": string;
};

export type EventTaskDetail = {
  "id": string;
  "title": string;
  "description": string | null;
  "status": "open" | "completed" | "skipped" | "blocked";
  "isRequired": boolean;
  "sortOrder": number;
  "dueAt": string | null;
  "completedAt": string | null;
  "blockedReason": string | null;
  "assignedUser": CalendarLinkedRecordSummary | null;
  "availableActions": (string)[];
  "history": (EventTaskHistoryItem)[];
};

export type CalendarDayResponse = {
  "selectedDate": string;
  "isToday": boolean;
  "summary": CalendarSummaryCounts;
  "events": (CalendarEventSummary)[];
};

export type CalendarDayEnvelope = {
  "data": CalendarDayResponse;
  "meta": ResponseMeta;
};

export type EventListResponse = {
  "items": (CalendarEventSummary)[];
  "range": {
  "startDate": string;
  "endDate": string;
};
};

export type EventListEnvelope = {
  "data": EventListResponse;
  "meta": ResponseMeta;
};

export type EventDetailResponse = {
  "id": string;
  "title": string;
  "description": string | null;
  "eventType": "appointment" | "follow_up" | "document_review" | "call" | "deadline" | "task_batch";
  "status": "scheduled" | "completed" | "cancelled";
  "startsAt": string | null;
  "endsAt": string | null;
  "isAllDay": boolean;
  "location": string | null;
  "client": CalendarLinkedRecordSummary | null;
  "owner": CalendarLinkedRecordSummary | null;
  "taskSummary": EventTaskSummary;
  "tasks": (EventTaskDetail)[];
};

export type EventDetailEnvelope = {
  "data": EventDetailResponse;
  "meta": ResponseMeta;
};

export type CreateEventTaskRequest = {
  "title": string;
  "description"?: string;
  "assignedUserId"?: string;
  "isRequired"?: boolean;
  "sortOrder"?: number;
  "dueAt"?: string;
  "metadata"?: {

};
};

export type CreateEventRequest = {
  "title": string;
  "description"?: string;
  "eventType": "appointment" | "follow_up" | "document_review" | "call" | "deadline" | "task_batch";
  "status"?: "scheduled" | "completed" | "cancelled";
  "startsAt": string;
  "endsAt"?: string;
  "isAllDay"?: boolean;
  "location"?: string;
  "clientId"?: string;
  "ownerUserId"?: string;
  "metadata"?: {

};
  "tasks"?: (CreateEventTaskRequest)[];
};

export type UpdateEventRequest = {
  "title"?: string;
  "description"?: string;
  "eventType"?: "appointment" | "follow_up" | "document_review" | "call" | "deadline" | "task_batch";
  "status"?: "scheduled" | "completed" | "cancelled";
  "startsAt"?: string;
  "endsAt"?: string;
  "isAllDay"?: boolean;
  "location"?: string;
  "clientId"?: string;
  "ownerUserId"?: string;
  "metadata"?: {

};
};

export type UpdateTaskStatusRequest = {
  "targetStatus": "open" | "completed" | "skipped" | "blocked";
  "reason"?: string;
  "blockedReason"?: string;
};

export type TaskStatusTransitionResponse = {
  "result": string;
  "mutatedTaskId": string;
  "event": EventDetailResponse;
};

export type TaskStatusTransitionEnvelope = {
  "data": TaskStatusTransitionResponse;
  "meta": ResponseMeta;
};

export type ClientEventListResponse = {
  "items": (CalendarEventSummary)[];
};

export type ClientEventListEnvelope = {
  "data": ClientEventListResponse;
  "meta": ResponseMeta;
};


export async function postAuthSignIn(client: ApiHttpClient, body: SignInRequest): Promise<AuthContextEnvelope> {
  return client.request<AuthContextEnvelope>({
    method: "POST",
    path: "/api/v1/auth/sign-in",
    body,
    contentType: "application/json"
  });
}


export async function postAuthSignOut(client: ApiHttpClient): Promise<MessageResponse> {
  return client.request<MessageResponse>({
    method: "POST",
    path: "/api/v1/auth/sign-out"
  });
}


export async function getAuthMe(client: ApiHttpClient): Promise<AuthContextEnvelope> {
  return client.request<AuthContextEnvelope>({
    method: "GET",
    path: "/api/v1/auth/me"
  });
}


export async function getOnboardingState(client: ApiHttpClient): Promise<OnboardingStateEnvelope> {
  return client.request<OnboardingStateEnvelope>({
    method: "GET",
    path: "/api/v1/onboarding/state"
  });
}


export async function patchOnboardingProfileConfirmation(client: ApiHttpClient, body: ProfileConfirmationRequest): Promise<OnboardingStateEnvelope> {
  return client.request<OnboardingStateEnvelope>({
    method: "PATCH",
    path: "/api/v1/onboarding/profile-confirmation",
    body,
    contentType: "application/json"
  });
}


export async function patchOnboardingIndustrySelection(client: ApiHttpClient, body: IndustrySelectionRequest): Promise<OnboardingStateEnvelope> {
  return client.request<OnboardingStateEnvelope>({
    method: "PATCH",
    path: "/api/v1/onboarding/industry-selection",
    body,
    contentType: "application/json"
  });
}


export async function postOnboardingComplete(client: ApiHttpClient): Promise<OnboardingStateEnvelope> {
  return client.request<OnboardingStateEnvelope>({
    method: "POST",
    path: "/api/v1/onboarding/complete"
  });
}


export async function getSettingsProfile(client: ApiHttpClient): Promise<ProfileEnvelope> {
  return client.request<ProfileEnvelope>({
    method: "GET",
    path: "/api/v1/settings/profile"
  });
}


export async function patchSettingsProfile(client: ApiHttpClient, body: UpdateProfileRequest): Promise<ProfileEnvelope> {
  return client.request<ProfileEnvelope>({
    method: "PATCH",
    path: "/api/v1/settings/profile",
    body,
    contentType: "application/json"
  });
}


export async function getSettingsAccounts(client: ApiHttpClient): Promise<AccountListEnvelope> {
  return client.request<AccountListEnvelope>({
    method: "GET",
    path: "/api/v1/settings/accounts"
  });
}


export async function postSettingsAccounts(client: ApiHttpClient, body: CreateAccountRequest): Promise<AccountEnvelope> {
  return client.request<AccountEnvelope>({
    method: "POST",
    path: "/api/v1/settings/accounts",
    body,
    contentType: "application/json"
  });
}


export async function patchSettingsAccount(client: ApiHttpClient, pathParams: {
  "userId": string;
}, body: UpdateAccountRequest): Promise<AccountEnvelope> {
  return client.request<AccountEnvelope>({
    method: "PATCH",
    path: "/api/v1/settings/accounts/{userId}",
    pathParams,
    body,
    contentType: "application/json"
  });
}


export async function deleteSettingsAccount(client: ApiHttpClient, pathParams: {
  "userId": string;
}): Promise<MessageResponse> {
  return client.request<MessageResponse>({
    method: "DELETE",
    path: "/api/v1/settings/accounts/{userId}",
    pathParams
  });
}


export async function getSettingsTheme(client: ApiHttpClient): Promise<ThemeEnvelope> {
  return client.request<ThemeEnvelope>({
    method: "GET",
    path: "/api/v1/settings/theme"
  });
}


export async function patchSettingsTheme(client: ApiHttpClient, body: ThemeSummary): Promise<ThemeEnvelope> {
  return client.request<ThemeEnvelope>({
    method: "PATCH",
    path: "/api/v1/settings/theme",
    body,
    contentType: "application/json"
  });
}


export async function getSettingsIndustryConfigurations(client: ApiHttpClient): Promise<IndustryConfigurationListEnvelope> {
  return client.request<IndustryConfigurationListEnvelope>({
    method: "GET",
    path: "/api/v1/settings/industry-configurations"
  });
}


export async function postSettingsIndustryConfigurations(client: ApiHttpClient, body: CreateIndustryConfigurationRequest): Promise<IndustryConfigurationEnvelope> {
  return client.request<IndustryConfigurationEnvelope>({
    method: "POST",
    path: "/api/v1/settings/industry-configurations",
    body,
    contentType: "application/json"
  });
}


export async function getDashboardSummary(client: ApiHttpClient): Promise<DashboardSummaryEnvelope> {
  return client.request<DashboardSummaryEnvelope>({
    method: "GET",
    path: "/api/v1/dashboard/summary"
  });
}


export async function getDashboardProduction(client: ApiHttpClient, queryParams?: {
  "window"?: "7d" | "30d" | "90d";
}): Promise<DashboardProductionEnvelope> {
  return client.request<DashboardProductionEnvelope>({
    method: "GET",
    path: "/api/v1/dashboard/production",
    queryParams
  });
}


export async function getClients(client: ApiHttpClient, queryParams?: {
  "search"?: string;
  "status"?: "lead" | "qualified" | "applied" | "active" | "inactive";
  "sort"?: "display_name" | "created_at" | "updated_at" | "last_activity_at";
  "direction"?: "asc" | "desc";
  "page"?: number;
  "perPage"?: number;
}): Promise<ClientListEnvelope> {
  return client.request<ClientListEnvelope>({
    method: "GET",
    path: "/api/v1/clients",
    queryParams
  });
}


export async function postClients(client: ApiHttpClient, body: CreateClientRequest): Promise<ClientEnvelope> {
  return client.request<ClientEnvelope>({
    method: "POST",
    path: "/api/v1/clients",
    body,
    contentType: "application/json"
  });
}


export async function getClient(client: ApiHttpClient, pathParams: {
  "clientId": string;
}): Promise<ClientWorkspaceEnvelope> {
  return client.request<ClientWorkspaceEnvelope>({
    method: "GET",
    path: "/api/v1/clients/{clientId}",
    pathParams
  });
}


export async function patchClient(client: ApiHttpClient, pathParams: {
  "clientId": string;
}, body: UpdateClientRequest): Promise<ClientEnvelope> {
  return client.request<ClientEnvelope>({
    method: "PATCH",
    path: "/api/v1/clients/{clientId}",
    pathParams,
    body,
    contentType: "application/json"
  });
}


export async function postClientNotes(client: ApiHttpClient, pathParams: {
  "clientId": string;
}, body: CreateClientNoteRequest): Promise<ClientNoteEnvelope> {
  return client.request<ClientNoteEnvelope>({
    method: "POST",
    path: "/api/v1/clients/{clientId}/notes",
    pathParams,
    body,
    contentType: "application/json"
  });
}


export async function postClientDocuments(client: ApiHttpClient, pathParams: {
  "clientId": string;
}, body: FormData): Promise<ClientDocumentEnvelope> {
  return client.request<ClientDocumentEnvelope>({
    method: "POST",
    path: "/api/v1/clients/{clientId}/documents",
    pathParams,
    body,
    contentType: "multipart/form-data"
  });
}


export async function getClientCommunications(client: ApiHttpClient, pathParams: {
  "clientId": string;
}, queryParams?: {
  "channel"?: "all" | "sms" | "email" | "voice";
  "status"?: "all" | "pending" | "failed";
  "limit"?: number;
}): Promise<ClientCommunicationsEnvelope> {
  return client.request<ClientCommunicationsEnvelope>({
    method: "GET",
    path: "/api/v1/clients/{clientId}/communications",
    pathParams,
    queryParams
  });
}


export async function postClientCommunicationsSms(client: ApiHttpClient, pathParams: {
  "clientId": string;
}, body: FormData): Promise<CommunicationTimelineItemEnvelope> {
  return client.request<CommunicationTimelineItemEnvelope>({
    method: "POST",
    path: "/api/v1/clients/{clientId}/communications/sms",
    pathParams,
    body,
    contentType: "multipart/form-data"
  });
}


export async function postClientCommunicationsEmail(client: ApiHttpClient, pathParams: {
  "clientId": string;
}, body: FormData): Promise<CommunicationTimelineItemEnvelope> {
  return client.request<CommunicationTimelineItemEnvelope>({
    method: "POST",
    path: "/api/v1/clients/{clientId}/communications/email",
    pathParams,
    body,
    contentType: "multipart/form-data"
  });
}


export async function postClientCommunicationsCall(client: ApiHttpClient, pathParams: {
  "clientId": string;
}, body: StartCallRequest): Promise<CommunicationTimelineItemEnvelope> {
  return client.request<CommunicationTimelineItemEnvelope>({
    method: "POST",
    path: "/api/v1/clients/{clientId}/communications/call",
    pathParams,
    body,
    contentType: "application/json"
  });
}


export async function postWebhookTwilioMessaging(client: ApiHttpClient): Promise<void> {
  return client.request<void>({
    method: "POST",
    path: "/webhooks/twilio/messaging"
  });
}


export async function postWebhookTwilioVoice(client: ApiHttpClient): Promise<void> {
  return client.request<void>({
    method: "POST",
    path: "/webhooks/twilio/voice"
  });
}


export async function postWebhookSendgridInbound(client: ApiHttpClient): Promise<void> {
  return client.request<void>({
    method: "POST",
    path: "/webhooks/sendgrid/inbound"
  });
}


export async function postWebhookSendgridEvents(client: ApiHttpClient): Promise<void> {
  return client.request<void>({
    method: "POST",
    path: "/webhooks/sendgrid/events"
  });
}


export async function postClientDispositionTransitions(client: ApiHttpClient, pathParams: {
  "clientId": string;
}, body: DispositionTransitionRequest): Promise<DispositionTransitionEnvelope> {
  return client.request<DispositionTransitionEnvelope>({
    method: "POST",
    path: "/api/v1/clients/{clientId}/disposition-transitions",
    pathParams,
    body,
    contentType: "application/json"
  });
}


export async function getClientApplications(client: ApiHttpClient, pathParams: {
  "clientId": string;
}): Promise<ClientApplicationsListEnvelope> {
  return client.request<ClientApplicationsListEnvelope>({
    method: "GET",
    path: "/api/v1/clients/{clientId}/applications",
    pathParams
  });
}


export async function postClientApplications(client: ApiHttpClient, pathParams: {
  "clientId": string;
}, body: CreateApplicationRequest): Promise<ApplicationDetailEnvelope> {
  return client.request<ApplicationDetailEnvelope>({
    method: "POST",
    path: "/api/v1/clients/{clientId}/applications",
    pathParams,
    body,
    contentType: "application/json"
  });
}


export async function getClientApplication(client: ApiHttpClient, pathParams: {
  "clientId": string;
  "applicationId": string;
}): Promise<ApplicationDetailEnvelope> {
  return client.request<ApplicationDetailEnvelope>({
    method: "GET",
    path: "/api/v1/clients/{clientId}/applications/{applicationId}",
    pathParams
  });
}


export async function postClientApplicationStatusTransitions(client: ApiHttpClient, pathParams: {
  "clientId": string;
  "applicationId": string;
}, body: TransitionApplicationStatusRequest): Promise<ApplicationTransitionEnvelope> {
  return client.request<ApplicationTransitionEnvelope>({
    method: "POST",
    path: "/api/v1/clients/{clientId}/applications/{applicationId}/status-transitions",
    pathParams,
    body,
    contentType: "application/json"
  });
}


export async function getRules(client: ApiHttpClient, queryParams?: {
  "moduleScope"?: string;
  "status"?: string;
}): Promise<RuleListEnvelope> {
  return client.request<RuleListEnvelope>({
    method: "GET",
    path: "/api/v1/rules",
    queryParams
  });
}


export async function postRules(client: ApiHttpClient, body: CreateRuleRequest): Promise<RuleDetailEnvelope> {
  return client.request<RuleDetailEnvelope>({
    method: "POST",
    path: "/api/v1/rules",
    body,
    contentType: "application/json"
  });
}


export async function getRule(client: ApiHttpClient, pathParams: {
  "ruleId": string;
}): Promise<RuleDetailEnvelope> {
  return client.request<RuleDetailEnvelope>({
    method: "GET",
    path: "/api/v1/rules/{ruleId}",
    pathParams
  });
}


export async function patchRule(client: ApiHttpClient, pathParams: {
  "ruleId": string;
}, body: UpdateRuleDraftRequest): Promise<RuleDetailEnvelope> {
  return client.request<RuleDetailEnvelope>({
    method: "PATCH",
    path: "/api/v1/rules/{ruleId}",
    pathParams,
    body,
    contentType: "application/json"
  });
}


export async function postRulePublish(client: ApiHttpClient, pathParams: {
  "ruleId": string;
}, body: PublishLifecycleRequest): Promise<RuleDetailEnvelope> {
  return client.request<RuleDetailEnvelope>({
    method: "POST",
    path: "/api/v1/rules/{ruleId}/publish",
    pathParams,
    body,
    contentType: "application/json"
  });
}


export async function getRuleExecutionLogs(client: ApiHttpClient, pathParams: {
  "ruleId": string;
}): Promise<RuleExecutionLogEnvelope> {
  return client.request<RuleExecutionLogEnvelope>({
    method: "GET",
    path: "/api/v1/rules/{ruleId}/execution-logs",
    pathParams
  });
}


export async function getWorkflows(client: ApiHttpClient, queryParams?: {
  "status"?: string;
}): Promise<WorkflowListEnvelope> {
  return client.request<WorkflowListEnvelope>({
    method: "GET",
    path: "/api/v1/workflows",
    queryParams
  });
}


export async function postWorkflows(client: ApiHttpClient, body: CreateWorkflowRequest): Promise<WorkflowDetailEnvelope> {
  return client.request<WorkflowDetailEnvelope>({
    method: "POST",
    path: "/api/v1/workflows",
    body,
    contentType: "application/json"
  });
}


export async function getWorkflow(client: ApiHttpClient, pathParams: {
  "workflowId": string;
}): Promise<WorkflowDetailEnvelope> {
  return client.request<WorkflowDetailEnvelope>({
    method: "GET",
    path: "/api/v1/workflows/{workflowId}",
    pathParams
  });
}


export async function patchWorkflow(client: ApiHttpClient, pathParams: {
  "workflowId": string;
}, body: UpdateWorkflowDraftRequest): Promise<WorkflowDetailEnvelope> {
  return client.request<WorkflowDetailEnvelope>({
    method: "PATCH",
    path: "/api/v1/workflows/{workflowId}",
    pathParams,
    body,
    contentType: "application/json"
  });
}


export async function postWorkflowPublish(client: ApiHttpClient, pathParams: {
  "workflowId": string;
}, body: PublishLifecycleRequest): Promise<WorkflowDetailEnvelope> {
  return client.request<WorkflowDetailEnvelope>({
    method: "POST",
    path: "/api/v1/workflows/{workflowId}/publish",
    pathParams,
    body,
    contentType: "application/json"
  });
}


export async function getWorkflowRuns(client: ApiHttpClient, pathParams: {
  "workflowId": string;
}): Promise<WorkflowRunListEnvelope> {
  return client.request<WorkflowRunListEnvelope>({
    method: "GET",
    path: "/api/v1/workflows/{workflowId}/runs",
    pathParams
  });
}


export async function getWorkflowRun(client: ApiHttpClient, pathParams: {
  "workflowId": string;
  "runId": string;
}): Promise<WorkflowRunDetailEnvelope> {
  return client.request<WorkflowRunDetailEnvelope>({
    method: "GET",
    path: "/api/v1/workflows/{workflowId}/runs/{runId}",
    pathParams
  });
}


export async function getImports(client: ApiHttpClient, queryParams?: {
  "status"?: string;
  "importType"?: string;
}): Promise<ImportListEnvelope> {
  return client.request<ImportListEnvelope>({
    method: "GET",
    path: "/api/v1/imports",
    queryParams
  });
}


export async function postImports(client: ApiHttpClient, body: FormData): Promise<ImportDetailEnvelope> {
  return client.request<ImportDetailEnvelope>({
    method: "POST",
    path: "/api/v1/imports",
    body,
    contentType: "multipart/form-data"
  });
}


export async function getImport(client: ApiHttpClient, pathParams: {
  "importId": string;
}): Promise<ImportDetailEnvelope> {
  return client.request<ImportDetailEnvelope>({
    method: "GET",
    path: "/api/v1/imports/{importId}",
    pathParams
  });
}


export async function getImportErrors(client: ApiHttpClient, pathParams: {
  "importId": string;
}, queryParams?: {
  "severity"?: string;
}): Promise<ImportErrorEnvelope> {
  return client.request<ImportErrorEnvelope>({
    method: "GET",
    path: "/api/v1/imports/{importId}/errors",
    pathParams,
    queryParams
  });
}


export async function postImportValidate(client: ApiHttpClient, pathParams: {
  "importId": string;
}): Promise<ImportDetailEnvelope> {
  return client.request<ImportDetailEnvelope>({
    method: "POST",
    path: "/api/v1/imports/{importId}/validate",
    pathParams
  });
}


export async function postImportCommit(client: ApiHttpClient, pathParams: {
  "importId": string;
}): Promise<ImportDetailEnvelope> {
  return client.request<ImportDetailEnvelope>({
    method: "POST",
    path: "/api/v1/imports/{importId}/commit",
    pathParams
  });
}


export async function getNotifications(client: ApiHttpClient, queryParams?: {
  "includeDismissed"?: boolean;
}): Promise<NotificationListEnvelope> {
  return client.request<NotificationListEnvelope>({
    method: "GET",
    path: "/api/v1/notifications",
    queryParams
  });
}


export async function postNotificationDismiss(client: ApiHttpClient, pathParams: {
  "notificationId": string;
}, body: DismissNotificationRequest): Promise<NotificationDismissEnvelope> {
  return client.request<NotificationDismissEnvelope>({
    method: "POST",
    path: "/api/v1/notifications/{notificationId}/dismiss",
    pathParams,
    body,
    contentType: "application/json"
  });
}


export async function postNotificationRead(client: ApiHttpClient, pathParams: {
  "notificationId": string;
}): Promise<NotificationReadEnvelope> {
  return client.request<NotificationReadEnvelope>({
    method: "POST",
    path: "/api/v1/notifications/{notificationId}/read",
    pathParams
  });
}


export async function getAudit(client: ApiHttpClient, queryParams?: {
  "action"?: string;
  "subjectType"?: string;
  "subjectId"?: string;
  "actorId"?: string;
  "correlationId"?: string;
  "q"?: string;
  "from"?: string;
  "to"?: string;
}): Promise<AuditListEnvelope> {
  return client.request<AuditListEnvelope>({
    method: "GET",
    path: "/api/v1/audit",
    queryParams
  });
}


export async function getCalendarDay(client: ApiHttpClient, queryParams: {
  "date": string;
}): Promise<CalendarDayEnvelope> {
  return client.request<CalendarDayEnvelope>({
    method: "GET",
    path: "/api/v1/calendar/day",
    queryParams
  });
}


export async function getEvents(client: ApiHttpClient, queryParams: {
  "startDate": string;
  "endDate": string;
  "clientId"?: string;
  "ownerUserId"?: string;
}): Promise<EventListEnvelope> {
  return client.request<EventListEnvelope>({
    method: "GET",
    path: "/api/v1/events",
    queryParams
  });
}


export async function postEvents(client: ApiHttpClient, body: CreateEventRequest): Promise<EventDetailEnvelope> {
  return client.request<EventDetailEnvelope>({
    method: "POST",
    path: "/api/v1/events",
    body,
    contentType: "application/json"
  });
}


export async function getEvent(client: ApiHttpClient, pathParams: {
  "eventId": string;
}): Promise<EventDetailEnvelope> {
  return client.request<EventDetailEnvelope>({
    method: "GET",
    path: "/api/v1/events/{eventId}",
    pathParams
  });
}


export async function patchEvent(client: ApiHttpClient, pathParams: {
  "eventId": string;
}, body: UpdateEventRequest): Promise<EventDetailEnvelope> {
  return client.request<EventDetailEnvelope>({
    method: "PATCH",
    path: "/api/v1/events/{eventId}",
    pathParams,
    body,
    contentType: "application/json"
  });
}


export async function patchTaskStatus(client: ApiHttpClient, pathParams: {
  "taskId": string;
}, body: UpdateTaskStatusRequest): Promise<TaskStatusTransitionEnvelope> {
  return client.request<TaskStatusTransitionEnvelope>({
    method: "PATCH",
    path: "/api/v1/tasks/{taskId}/status",
    pathParams,
    body,
    contentType: "application/json"
  });
}


export async function getClientEvents(client: ApiHttpClient, pathParams: {
  "clientId": string;
}, queryParams?: {
  "startDate"?: string;
  "endDate"?: string;
}): Promise<ClientEventListEnvelope> {
  return client.request<ClientEventListEnvelope>({
    method: "GET",
    path: "/api/v1/clients/{clientId}/events",
    pathParams,
    queryParams
  });
}

