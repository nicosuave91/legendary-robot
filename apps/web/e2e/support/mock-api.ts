import type { Page } from '@playwright/test'

type JsonValue = Record<string, unknown> | unknown[]

const authContext = {
  data: {
    isAuthenticated: true,
    user: { id: 'owner-user', email: 'owner@example.com', displayName: 'Tenant Owner' },
    tenant: { id: 'tenant-default', name: 'Default Workspace' },
    roles: ['owner'],
    permissions: [
      'dashboard.summary.read',
      'dashboard.production.read',
      'calendar.read',
      'calendar.create',
      'calendar.update',
      'calendar.tasks.update',
      'clients.read',
      'clients.read.all',
      'clients.events.read',
      'clients.communications.read',
      'clients.communications.sms.send',
      'clients.communications.email.send',
      'clients.communications.call.create',
      'clients.disposition.read',
      'clients.disposition.transition',
      'clients.applications.read',
      'clients.applications.create',
      'clients.applications.status.transition',
      'imports.read',
      'imports.create',
      'imports.validate',
      'imports.commit',
      'notifications.read',
      'notifications.dismiss',
      'audit.read',
      'rules.read',
      'rules.publish',
      'workflows.read',
      'workflows.publish',
      'settings.profile.read',
      'settings.accounts.read',
      'settings.theme.read',
      'settings.industry-configurations.read'
    ],
    onboardingState: 'completed',
    onboardingStep: null,
    theme: { primary: '#1d4ed8', secondary: '#0f172a', tertiary: '#64748b' },
    landingRoute: '/app/dashboard',
    selectedIndustry: 'Mortgage',
    selectedIndustryConfigVersion: 'mortgage-v1',
    capabilities: ['calendar', 'communications', 'imports'],
  },
  meta: { apiVersion: 'v1', correlationId: 'corr-auth' },
}

const dashboardSummary = {
  data: {
    hero: {
      greeting: 'Good morning',
      userDisplayName: 'Tenant Owner',
      tenantName: 'Default Workspace',
      selectedIndustry: 'Mortgage',
      selectedIndustryConfigVersion: 'mortgage-v1',
      subtitle: 'Proof-ready dashboard surface',
    },
    kpis: [
      { key: 'clients', label: 'Clients', value: 12, href: '/app/clients', delta: { direction: 'up', value: 2, label: 'vs prior window' } },
      { key: 'events', label: 'Events', value: 3, href: '/app/calendar', delta: { direction: 'flat', value: 0, label: 'today' } },
    ],
  },
  meta: { apiVersion: 'v1', correlationId: 'corr-dashboard' },
}

const dashboardProduction = {
  data: {
    window: '30d',
    points: [
      { label: 'Week 1', value: 8 },
      { label: 'Week 2', value: 11 },
      { label: 'Week 3', value: 14 },
      { label: 'Week 4', value: 16 },
    ],
  },
  meta: { apiVersion: 'v1', correlationId: 'corr-production' },
}

const calendarEvent = {
  id: 'event-1',
  title: 'Client review',
  description: 'Review intake completeness',
  eventType: 'appointment',
  status: 'scheduled',
  startsAt: '2026-03-31T14:00:00Z',
  endsAt: '2026-03-31T14:30:00Z',
  isAllDay: false,
  location: null,
  client: { id: 'client-1', displayName: 'Acme Mortgage' },
  owner: { id: 'owner-user', displayName: 'Tenant Owner' },
  taskSummary: { total: 1, open: 1, completed: 0, blocked: 0, skipped: 0 },
}

const calendarRange = {
  data: {
    items: [calendarEvent],
  },
  meta: { apiVersion: 'v1', correlationId: 'corr-range' },
}

const calendarDay = {
  data: {
    selectedDate: '2026-03-31',
    isToday: true,
    summary: { eventCount: 1, openTaskCount: 1, completedTaskCount: 0, blockedTaskCount: 0, skippedTaskCount: 0 },
    events: [calendarEvent],
  },
  meta: { apiVersion: 'v1', correlationId: 'corr-day' },
}

const eventDetail = {
  data: {
    ...calendarEvent,
    tasks: [
      {
        id: 'task-1',
        title: 'Confirm intake packet',
        description: 'Validate required documents',
        status: 'open',
        isRequired: true,
        blockedReason: null,
        availableActions: ['completed', 'blocked', 'skipped'],
        history: [],
      },
    ],
  },
  meta: { apiVersion: 'v1', correlationId: 'corr-event' },
}

const clientWorkspace = {
  data: {
    client: {
      id: 'client-1',
      displayName: 'Acme Mortgage',
      firstName: 'Alicia',
      lastName: 'Stone',
      companyName: 'Acme Mortgage',
      status: 'lead',
      primaryEmail: 'alicia@example.com',
      primaryPhone: '804-555-0101',
      preferredContactChannel: 'email',
      dateOfBirth: null,
      ownerUserId: 'owner-user',
      ownerDisplayName: 'Tenant Owner',
      address: null,
      createdAt: '2026-03-20T12:00:00Z',
      updatedAt: '2026-03-31T12:00:00Z',
    },
    currentDisposition: null,
    availableDispositionTransitions: [],
    dispositionHistory: [],
    summary: {
      notesCount: 1,
      documentsCount: 0,
      eventsCount: 1,
      applicationsCount: 0,
      lastActivityAt: '2026-03-31T12:00:00Z',
    },
    recentNotes: [],
    recentDocuments: [],
    recentAudit: [],
    tabs: [
      { key: 'overview', label: 'Overview', href: '/app/clients/client-1/overview', available: true },
      { key: 'communications', label: 'Communications', href: '/app/clients/client-1/communications', available: true },
      { key: 'events', label: 'Events', href: '/app/clients/client-1/events', available: true },
      { key: 'applications', label: 'Applications', href: '/app/clients/client-1/applications', available: true },
      { key: 'notes', label: 'Notes', href: '/app/clients/client-1/notes', available: true },
      { key: 'documents', label: 'Documents', href: '/app/clients/client-1/documents', available: true },
      { key: 'audit', label: 'Audit', href: '/app/clients/client-1/audit', available: true },
    ],
  },
  meta: { apiVersion: 'v1', correlationId: 'corr-client' },
}

const clientEvents = {
  data: {
    items: [calendarEvent],
  },
  meta: { apiVersion: 'v1', correlationId: 'corr-client-events' },
}

const notifications = {
  data: {
    items: [],
    meta: { unread: 0 },
  },
  meta: { apiVersion: 'v1', correlationId: 'corr-notifications' },
}

async function fulfillJson(page: Page, matcher: string, payload: JsonValue) {
  await page.route(matcher, async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify(payload),
    })
  })
}

export async function installAuthenticatedAppMocks(page: Page) {
  await fulfillJson(page, '**/api/v1/auth/me', authContext)
  await fulfillJson(page, '**/api/v1/notifications**', notifications)
  await fulfillJson(page, '**/api/v1/dashboard/summary', dashboardSummary)
  await fulfillJson(page, '**/api/v1/dashboard/production**', dashboardProduction)
  await fulfillJson(page, '**/api/v1/events**', calendarRange)
  await fulfillJson(page, '**/api/v1/calendar/day**', calendarDay)
  await fulfillJson(page, '**/api/v1/events/event-1', eventDetail)
  await fulfillJson(page, '**/api/v1/tasks/task-1/status', {
    data: {
      result: 'updated',
      mutatedTaskId: 'task-1',
      event: {
        ...eventDetail.data,
        taskSummary: { total: 1, open: 0, completed: 1, blocked: 0, skipped: 0 },
        tasks: [
          {
            ...eventDetail.data.tasks[0],
            status: 'completed',
            availableActions: ['open'],
            history: [
              {
                id: 'hist-1',
                actorDisplayName: 'Tenant Owner',
                fromStatus: 'open',
                toStatus: 'completed',
                reason: 'Browser smoke proof',
                occurredAt: '2026-03-31T15:00:00Z',
              },
            ],
          },
        ],
      },
    },
    meta: { apiVersion: 'v1', correlationId: 'corr-task-update' },
  })
  await fulfillJson(page, '**/api/v1/clients/client-1', clientWorkspace)
  await fulfillJson(page, '**/api/v1/clients/client-1/events**', clientEvents)
}
