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
      'settings.industry-configurations.read',
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
      { key: 'clients_total', label: 'Clients', value: 12, description: 'Visible client records across the workspace.', href: '/app/clients', delta: { direction: 'up', value: 2, label: 'vs prior window' } },
      { key: 'clients_new_7d', label: 'New in 7 days', value: 3, description: 'Recently created client records.', href: '/app/clients', delta: { direction: 'up', value: 1, label: 'vs prior window' } },
      { key: 'notes_7d', label: 'Notes in 7 days', value: 8, description: 'Recent activity notes.', href: '/app/clients', delta: { direction: 'flat', value: 0, label: 'stable' } },
      { key: 'documents_7d', label: 'Documents in 7 days', value: 4, description: 'Recent uploaded evidence.', href: '/app/clients', delta: { direction: 'up', value: 2, label: 'vs prior window' } },
    ],
    activitySummary: { visibleClientCount: 12, recentNoteCount: 8, recentDocumentCount: 4 },
    calendarPanelEnabled: true,
  },
  meta: { apiVersion: 'v1', correlationId: 'corr-dashboard' },
}

const dashboardProduction = {
  data: {
    range: { window: '30d', startDate: '2026-03-01', endDate: '2026-03-31', granularity: 'day' },
    series: [
      { key: 'clientsCreated', label: 'Clients created', points: [{ bucketDate: '2026-03-08', value: 2 }, { bucketDate: '2026-03-15', value: 3 }] },
      { key: 'notesCreated', label: 'Notes created', points: [{ bucketDate: '2026-03-08', value: 4 }, { bucketDate: '2026-03-15', value: 5 }] },
      { key: 'documentsUploaded', label: 'Documents uploaded', points: [{ bucketDate: '2026-03-08', value: 1 }, { bucketDate: '2026-03-15', value: 2 }] },
    ],
    totals: { clientsCreated: 12, notesCreated: 22, documentsUploaded: 8 },
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

const calendarRange = { data: { items: [calendarEvent], range: { startDate: '2026-03-01', endDate: '2026-03-31' } }, meta: { apiVersion: 'v1', correlationId: 'corr-range' } }
const calendarDay = { data: { selectedDate: '2026-03-31', isToday: true, summary: { eventCount: 1, openTaskCount: 1, completedTaskCount: 0, blockedTaskCount: 0, skippedTaskCount: 0 }, events: [calendarEvent] }, meta: { apiVersion: 'v1', correlationId: 'corr-day' } }

const eventDetail = {
  data: {
    ...calendarEvent,
    tasks: [{ id: 'task-1', title: 'Confirm intake packet', description: 'Validate required documents', status: 'open', isRequired: true, sortOrder: 1, dueAt: null, completedAt: null, blockedReason: null, assignedUser: { id: 'owner-user', displayName: 'Tenant Owner' }, availableActions: ['completed', 'blocked', 'skipped'], history: [] }],
  },
  meta: { apiVersion: 'v1', correlationId: 'corr-event' },
}

const clientWorkspace = {
  data: {
    client: { id: 'client-1', displayName: 'Acme Mortgage', firstName: 'Alicia', lastName: 'Stone', companyName: 'Acme Mortgage', status: 'lead', primaryEmail: 'alicia@example.com', primaryPhone: '804-555-0101', preferredContactChannel: 'email', dateOfBirth: null, ownerUserId: 'owner-user', ownerDisplayName: 'Tenant Owner', address: null, createdAt: '2026-03-20T12:00:00Z', updatedAt: '2026-03-31T12:00:00Z' },
    currentDisposition: null,
    availableDispositionTransitions: [],
    dispositionHistory: [],
    summary: { notesCount: 1, documentsCount: 0, eventsCount: 1, applicationsCount: 0, lastActivityAt: '2026-03-31T12:00:00Z' },
    recentNotes: [],
    recentDocuments: [],
    recentAudit: [],
    tabs: [
      { key: 'overview', label: 'Overview', href: '/app/clients/client-1/overview', available: true },
      { key: 'communications', label: 'Communications', href: '/app/clients/client-1/communications', available: true },
      { key: 'events', label: 'Events', href: '/app/clients/client-1/events', available: true },
      { key: 'applications', label: 'Applications', href: '/app/clients/client-1/applications', available: true },
    ],
  },
  meta: { apiVersion: 'v1', correlationId: 'corr-client' },
}

const clientEvents = { data: { items: [calendarEvent] }, meta: { apiVersion: 'v1', correlationId: 'corr-client-events' } }
const notifications = { data: { items: [], meta: { unread: 0 } }, meta: { apiVersion: 'v1', correlationId: 'corr-notifications' } }

const workflowDetail = {
  data: {
    workflow: {
      id: 'workflow-1',
      workflowKey: 'application-review-follow-up',
      name: 'Application review follow-up',
      description: 'Queues a follow-up note and message when a created application crosses review conditions.',
      status: 'draft',
      triggerSummary: 'application.created',
      latestPublishedVersionNumber: 1,
      currentDraftVersionNumber: 2,
      latestPublishedAt: '2026-04-05T14:00:00Z',
      updatedAt: '2026-04-06T09:15:00Z',
      currentDraftVersionId: 'workflow-version-2',
      latestPublishedVersionId: 'workflow-version-1',
    },
    versions: [
      {
        id: 'workflow-version-2',
        versionNumber: 2,
        lifecycleState: 'draft',
        triggerDefinition: { event: 'application.created', subjectType: 'application' },
        stepsDefinition: [
          { type: 'condition', definition: { fact: 'amountRequested', operator: 'gte', value: 250000 } },
          { type: 'send_email', definition: {} },
        ],
        checksum: 'abc1234567890draft',
        publishedAt: null,
        publishedBy: null,
        createdAt: '2026-04-06T08:45:00Z',
        updatedAt: '2026-04-06T09:10:00Z',
      },
      {
        id: 'workflow-version-1',
        versionNumber: 1,
        lifecycleState: 'published',
        triggerDefinition: { event: 'application.created', subjectType: 'application' },
        stepsDefinition: [{ type: 'create_client_note', definition: { body: 'Application created.' } }],
        checksum: 'abc1234567890pubd',
        publishedAt: '2026-04-05T14:00:00Z',
        publishedBy: 'owner-user',
        createdAt: '2026-04-05T13:30:00Z',
        updatedAt: '2026-04-05T14:00:00Z',
      },
    ],
    draftValidation: {
      hasDraft: true,
      isValid: false,
      errors: [
        { code: 'workflow.step.missing_to', path: 'stepsDefinition[1].definition.to', message: 'Email workflow steps must define at least one recipient address.' },
        { code: 'workflow.step.missing_body', path: 'stepsDefinition[1].definition.bodyText', message: 'Email workflow steps must define bodyText or bodyHtml.' },
      ],
    },
    meta: { versionCount: 2 },
  },
  meta: { apiVersion: 'v1', correlationId: 'corr-workflow-detail' },
}

const workflowRuns = {
  data: {
    items: [
      {
        id: 'workflow-run-1',
        workflowId: 'workflow-1',
        workflowVersionId: 'workflow-version-1',
        triggerEvent: 'application.created',
        subjectType: 'application',
        subjectId: 'application-1',
        status: 'completed',
        currentStepIndex: 1,
        correlationId: 'corr-workflow-run-1',
        queuedAt: '2026-04-05T14:05:00Z',
        startedAt: '2026-04-05T14:05:05Z',
        completedAt: '2026-04-05T14:05:08Z',
        failedAt: null,
        failureSummary: {},
      },
    ],
    meta: { total: 1 },
  },
  meta: { apiVersion: 'v1', correlationId: 'corr-workflow-runs' },
}

const workflowRunDetail = {
  data: {
    run: workflowRuns.data.items[0],
    logs: [
      { id: 'workflow-log-1', workflowRunId: 'workflow-run-1', workflowVersionId: 'workflow-version-1', stepIndex: 0, logType: 'step_started', message: 'Executing workflow step.', payloadSnapshot: { type: 'create_client_note' }, occurredAt: '2026-04-05T14:05:05Z' },
      { id: 'workflow-log-2', workflowRunId: 'workflow-run-1', workflowVersionId: 'workflow-version-1', stepIndex: 0, logType: 'note_created', message: 'Workflow created a client note through the governed client note service.', payloadSnapshot: { clientId: 'client-1', noteId: 'note-1' }, occurredAt: '2026-04-05T14:05:07Z' },
      { id: 'workflow-log-3', workflowRunId: 'workflow-run-1', workflowVersionId: 'workflow-version-1', stepIndex: null, logType: 'run_completed', message: 'Workflow run completed with durable version-bound evidence.', payloadSnapshot: {}, occurredAt: '2026-04-05T14:05:08Z' },
    ],
  },
  meta: { apiVersion: 'v1', correlationId: 'corr-workflow-run-detail' },
}

type RouteMatcher = string | RegExp | ((url: URL) => boolean)

async function fulfillJson(page: Page, matcher: RouteMatcher, payload: JsonValue) {
  await page.route(matcher, async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify(payload),
    })
  })
}

const pathIs = (pathname: string) => (url: URL) => url.pathname === pathname

export async function installAuthenticatedAppMocks(page: Page) {
  await fulfillJson(page, pathIs('/api/v1/auth/me'), authContext)
  await fulfillJson(page, /\/api\/v1\/notifications(?:\?.*)?$/, notifications)
  await fulfillJson(page, pathIs('/api/v1/dashboard/summary'), dashboardSummary)
  await fulfillJson(page, /\/api\/v1\/dashboard\/production(?:\?.*)?$/, dashboardProduction)
  await fulfillJson(page, pathIs('/api/v1/events/event-1'), eventDetail)
  await fulfillJson(page, pathIs('/api/v1/events'), calendarRange)
  await fulfillJson(page, pathIs('/api/v1/calendar/day'), calendarDay)
  await fulfillJson(page, /\/api\/v1\/clients\/client-1\/events(?:\?.*)?$/, clientEvents)
  await fulfillJson(page, pathIs('/api/v1/clients/client-1'), clientWorkspace)
  await fulfillJson(page, pathIs('/api/v1/workflows/workflow-1'), workflowDetail)
  await fulfillJson(page, pathIs('/api/v1/workflows/workflow-1/runs'), workflowRuns)
  await fulfillJson(page, pathIs('/api/v1/workflows/workflow-1/runs/workflow-run-1'), workflowRunDetail)

  await fulfillJson(page, pathIs('/api/v1/tasks/task-1/status'), {
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
              { id: 'hist-1', actorDisplayName: 'Tenant Owner', fromStatus: 'open', toStatus: 'completed', reason: 'Browser smoke proof', occurredAt: '2026-03-31T15:00:00Z' },
            ],
          },
        ],
      },
    },
    meta: { apiVersion: 'v1', correlationId: 'corr-task-update' },
  })
}
