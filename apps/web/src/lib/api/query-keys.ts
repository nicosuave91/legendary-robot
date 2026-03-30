export const queryKeys = {
  auth: {
    all: ['auth'] as const,
    me: () => [...queryKeys.auth.all, 'me'] as const
  },
  onboarding: {
    all: ['onboarding'] as const,
    state: () => [...queryKeys.onboarding.all, 'state'] as const
  },
  settings: {
    all: ['settings'] as const,
    accounts: () => [...queryKeys.settings.all, 'accounts'] as const
  }
}
