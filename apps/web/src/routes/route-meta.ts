import type { PermissionCode } from '@/lib/auth/permission-map'

export type RouteMeta = {
  title: string
  requiresAuth?: boolean
  requiresOnboardingComplete?: boolean
  onboardingEligible?: boolean
  permissions?: readonly PermissionCode[]
  navVisible?: boolean
}

export const routeMeta = {
  signIn: {
    title: 'Sign in'
  },
  onboarding: {
    title: 'Complete onboarding',
    requiresAuth: true,
    onboardingEligible: true,
    requiresOnboardingComplete: false
  },
  dashboard: {
    title: 'Dashboard',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    navVisible: true
  },
  settingsProfile: {
    title: 'My Profile',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    permissions: ['settings.profile.read'],
    navVisible: true
  },
  settingsAccounts: {
    title: 'Accounts',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    permissions: ['settings.accounts.read'],
    navVisible: true
  },
  settingsTheme: {
    title: 'Branding tokens',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    permissions: ['settings.theme.read'],
    navVisible: true
  },
  settingsIndustryConfigurations: {
    title: 'Industry configurations',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    permissions: ['settings.industry-configurations.read'],
    navVisible: true
  },
  clients: {
    title: 'Clients',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    navVisible: true
  },
  calendar: {
    title: 'Calendar & Tasks',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    navVisible: true
  },
  communications: {
    title: 'Communications',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    navVisible: true
  },
  audit: {
    title: 'Audit',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    navVisible: true
  }
} as const
