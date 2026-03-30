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

export type IndustrySelectionRequest = {
  "industry": "Legal" | "Medical" | "Mortgage";
};

export type AccountSummary = {
  "id": string;
  "email": string;
  "displayName": string;
  "roles": (string)[];
  "onboardingState": "not_applicable" | "required" | "in_progress" | "completed";
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


export async function postAuthSignIn(client: ApiHttpClient, body: SignInRequest): Promise<AuthContextEnvelope> {
  return client.request<AuthContextEnvelope>({
    method: "POST",
    path: "/api/v1/auth/sign-in", body
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
    path: "/api/v1/onboarding/profile-confirmation", body
  });
}


export async function patchOnboardingIndustrySelection(client: ApiHttpClient, body: IndustrySelectionRequest): Promise<OnboardingStateEnvelope> {
  return client.request<OnboardingStateEnvelope>({
    method: "PATCH",
    path: "/api/v1/onboarding/industry-selection", body
  });
}


export async function postOnboardingComplete(client: ApiHttpClient): Promise<OnboardingStateEnvelope> {
  return client.request<OnboardingStateEnvelope>({
    method: "POST",
    path: "/api/v1/onboarding/complete"
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
    path: "/api/v1/settings/accounts", body
  });
}

