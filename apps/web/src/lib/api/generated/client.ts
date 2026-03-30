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

export type SignInRequest = {
  "email": string;
  "password": string;
};

export type AuthContextEnvelope = {
  "data": AuthContextResponse;
  "meta": ResponseMeta;
};

export type ResponseMeta = {
  "apiVersion": "v1";
  "correlationId": string;
};

export type AuthContextResponse = {
  "isAuthenticated": boolean;
  "user": UserSummary;
  "tenant": TenantSummary;
  "roles": string[];
  "permissions": string[];
  "onboardingState": "not_applicable" | "required" | "in_progress" | "completed";
  "theme": ThemeSummary;
  "landingRoute": string;
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


export async function postAuthSignIn(client: ApiHttpClient, body: SignInRequest): Promise<AuthContextEnvelope> {
  return client.request<AuthContextEnvelope>({
    method: "POST",
    path: "/api/v1/auth/sign-in", body
  });
}


export async function postAuthSignOut(client: ApiHttpClient): Promise<{
  "message": string;
}> {
  return client.request<{
  "message": string;
}>({
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

