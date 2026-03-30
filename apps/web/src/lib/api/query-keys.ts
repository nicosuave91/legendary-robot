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
    accounts: () => [...queryKeys.settings.all, 'accounts'] as const,
    profile: () => [...queryKeys.settings.all, 'profile'] as const,
    theme: () => [...queryKeys.settings.all, 'theme'] as const,
    industryConfigurations: () => [...queryKeys.settings.all, 'industry-configurations'] as const
  }
}
