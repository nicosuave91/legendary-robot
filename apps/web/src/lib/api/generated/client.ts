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
  "status": "lead" | "active" | "inactive";
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
  "status": "lead" | "active" | "inactive";
  "primaryEmail": string | null;
  "primaryPhone": string | null;
  "city": string | null;
  "stateCode": string | null;
  "ownerDisplayName": string | null;
  "notesCount": number;
  "documentsCount": number;
  "updatedAt": string | null;
  "createdAt": string | null;
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
  "status"?: "lead" | "active" | "inactive";
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


export async function postClients(client: ApiHttpClient, body: CreateOrUpdateClientRequest): Promise<ClientEnvelope> {
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
}, body: CreateOrUpdateClientRequest): Promise<ClientEnvelope> {
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

