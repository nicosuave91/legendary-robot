/* eslint-disable */
/**
 * AUTO-GENERATED FILE.
 * Source: packages/contracts/openapi.json
 * Regenerate with: npm run client:generate
 *
 * Communications contract extension generated from the PHP OpenAPI overlay.
 * This file keeps the hand-authored API wrapper consuming generated API code while
 * the monolithic generated client is reconciled by the full contract generation flow.
 */

import type { ApiHttpClient, ClientCommunicationsEnvelope, ResponseMeta } from './client'

export type CommunicationTimelineItem = ClientCommunicationsEnvelope['data']['items'][number]

export type UpdateCommunicationAttachmentScanStatusRequest = {
  "status": "pending" | "clean" | "rejected" | "quarantined";
  "engine"?: string | null;
  "detail"?: string | null;
  "quarantineReason"?: string | null;
};

export type CommunicationAttachmentGovernanceSummary = {
  "id": string;
  "originalFilename": string;
  "mimeType": string;
  "sizeBytes": number;
  "scanStatus": string;
  "scanRequestedAt": string | null;
  "scannedAt": string | null;
  "scanEngine": string | null;
  "scanResultDetail": string | null;
  "quarantineReason": string | null;
};

export type CommunicationAttachmentGovernanceEnvelope = {
  "data": CommunicationAttachmentGovernanceSummary;
  "meta": ResponseMeta;
};

export type CommunicationInboxClientSummary = {
  "id": string;
  "displayName": string;
  "status": string;
  "ownerDisplayName": string | null;
  "primaryEmail": string | null;
  "primaryPhone": string | null;
  "lastActivityAt": string | null;
};

export type CommunicationInboxItem = {
  "client": CommunicationInboxClientSummary;
  "timelineItem": CommunicationTimelineItem;
};

export type CommunicationsInboxResponse = {
  "items": (CommunicationInboxItem)[];
  "paging": {
    "nextCursor": string | null;
    "hasMore": boolean;
  };
  "filters": {
    "search": string | null;
    "channel": string;
    "status": string;
  };
  "refresh": {
    "hasPendingRecentItems": boolean;
    "recommendedPollSeconds": number | null;
  };
  "summary": {
    "clientCount": number;
    "itemCount": number;
  };
};

export type CommunicationsInboxEnvelope = {
  "data": CommunicationsInboxResponse;
  "meta": ResponseMeta;
};

export async function getCommunicationsInbox(
  client: ApiHttpClient,
  queryParams?: {
    search?: string;
    channel?: "all" | "sms" | "email" | "voice";
    status?: "all" | "pending" | "failed";
    limit?: number;
    cursor?: string;
  },
): Promise<CommunicationsInboxEnvelope> {
  return client.request<CommunicationsInboxEnvelope>({
    method: 'GET',
    path: '/api/v1/communications/inbox',
    queryParams,
  });
}

export async function patchCommunicationAttachmentScanStatus(
  client: ApiHttpClient,
  pathParams: { attachmentId: string },
  body: UpdateCommunicationAttachmentScanStatusRequest,
): Promise<CommunicationAttachmentGovernanceEnvelope> {
  return client.request<CommunicationAttachmentGovernanceEnvelope>({
    method: 'PATCH',
    path: '/api/v1/communications/attachments/{attachmentId}/scan-status',
    pathParams,
    body,
  });
}
