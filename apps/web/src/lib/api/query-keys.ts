export const queryKeys = {
  auth: { all: ['auth'] as const, me: () => [...queryKeys.auth.all, 'me'] as const },
  onboarding: { all: ['onboarding'] as const, state: () => [...queryKeys.onboarding.all, 'state'] as const },
  settings: {
    all: ['settings'] as const,
    accounts: () => [...queryKeys.settings.all, 'accounts'] as const,
    profile: () => [...queryKeys.settings.all, 'profile'] as const,
    theme: () => [...queryKeys.settings.all, 'theme'] as const,
    industryConfigurations: () => [...queryKeys.settings.all, 'industry-configurations'] as const
  },
  dashboard: {
    all: ['dashboard'] as const,
    summary: () => [...queryKeys.dashboard.all, 'summary'] as const,
    production: (window: string) => [...queryKeys.dashboard.all, 'production', window] as const
  },
  calendar: {
    all: ['calendar'] as const,
    range: (startDate: string, endDate: string, filters: Record<string, string | number | null | undefined> = {}) => [...queryKeys.calendar.all, 'range', startDate, endDate, filters] as const,
    day: (date: string) => [...queryKeys.calendar.all, 'day', date] as const,
    event: (eventId: string) => [...queryKeys.calendar.all, 'event', eventId] as const,
    clientEvents: (clientId: string, filters: Record<string, string | number | null | undefined> = {}) => [...queryKeys.calendar.all, 'client-events', clientId, filters] as const
  },
  clients: {
    all: ['clients'] as const,
    list: (filters: Record<string, string | number | null | undefined> = {}) => [...queryKeys.clients.all, 'list', filters] as const,
    detail: (clientId: string) => [...queryKeys.clients.all, 'detail', clientId] as const
  },
  communications: {
    all: ['communications'] as const,
    clientTimeline: (clientId: string, filters: Record<string, string | number | null | undefined> = {}) =>
      [...queryKeys.communications.all, 'client', clientId, filters] as const
  },
  applications: {
    all: ['applications'] as const,
    list: (clientId: string) => [...queryKeys.applications.all, 'list', clientId] as const,
    detail: (clientId: string, applicationId: string) => [...queryKeys.applications.all, 'detail', clientId, applicationId] as const
  },
  rules: {
    all: ['rules'] as const,
    list: (filters: Record<string, string | number | null | undefined> = {}) => [...queryKeys.rules.all, 'list', filters] as const,
    detail: (ruleId: string) => [...queryKeys.rules.all, 'detail', ruleId] as const,
    executionLogs: (ruleId: string) => [...queryKeys.rules.all, 'execution-logs', ruleId] as const
  },
  workflows: {
    all: ['workflows'] as const,
    list: (filters: Record<string, string | number | null | undefined> = {}) => [...queryKeys.workflows.all, 'list', filters] as const,
    detail: (workflowId: string) => [...queryKeys.workflows.all, 'detail', workflowId] as const,
    runs: (workflowId: string) => [...queryKeys.workflows.all, 'runs', workflowId] as const,
    runDetail: (workflowId: string, runId: string) => [...queryKeys.workflows.all, 'run', workflowId, runId] as const
  },
  imports: {
    all: ['imports'] as const,
    list: (filters: Record<string, string | number | boolean | null | undefined> = {}) => [...queryKeys.imports.all, 'list', filters] as const,
    detail: (importId: string) => [...queryKeys.imports.all, 'detail', importId] as const,
    errors: (importId: string, filters: Record<string, string | number | boolean | null | undefined> = {}) => [...queryKeys.imports.all, 'errors', importId, filters] as const
  },
  notifications: {
    all: ['notifications'] as const,
    feed: (filters: Record<string, string | number | boolean | null | undefined> = {}) => [...queryKeys.notifications.all, 'feed', filters] as const
  },
  audit: {
    all: ['audit'] as const,
    list: (filters: Record<string, string | number | boolean | null | undefined> = {}) => [...queryKeys.audit.all, 'list', filters] as const
  }
}
