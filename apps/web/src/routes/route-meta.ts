import type { PermissionCode } from '@/lib/auth/permission-map'

export type PageArchetype = 'cockpit' | 'workspace' | 'settings' | 'governance' | 'audit'
export type NavigationSection = 'operations' | 'settings' | 'governance'

export type RouteMeta = {
  title: string
  requiresAuth?: boolean
  requiresOnboardingComplete?: boolean
  onboardingEligible?: boolean
  permissions?: readonly PermissionCode[]
  navVisible?: boolean
  pageArchetype?: PageArchetype
  shellSection?: NavigationSection
  shellDescription?: string
}

export const routeMeta = {
  signIn: { title: 'Sign in' },
  onboarding: {
    title: 'Complete onboarding',
    requiresAuth: true,
    onboardingEligible: true,
    requiresOnboardingComplete: false,
  },
  dashboard: {
    title: 'Homepage',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    permissions: ['dashboard.summary.read', 'dashboard.production.read'],
    navVisible: true,
    pageArchetype: 'cockpit',
    shellSection: 'operations',
    shellDescription: 'Operational cockpit',
  },
  settingsProfile: {
    title: 'My Profile',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    permissions: ['settings.profile.read'],
    navVisible: true,
    pageArchetype: 'settings',
    shellSection: 'settings',
    shellDescription: 'Administration & configuration',
  },
  settingsAccounts: {
    title: 'Accounts',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    permissions: ['settings.accounts.read'],
    navVisible: true,
    pageArchetype: 'settings',
    shellSection: 'settings',
    shellDescription: 'Administration & configuration',
  },
  settingsTheme: {
    title: 'Branding',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    permissions: ['settings.theme.read'],
    navVisible: true,
    pageArchetype: 'settings',
    shellSection: 'settings',
    shellDescription: 'Administration & configuration',
  },
  settingsIndustryConfigurations: {
    title: 'Industry configurations',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    permissions: ['settings.industry-configurations.read'],
    navVisible: true,
    pageArchetype: 'settings',
    shellSection: 'settings',
    shellDescription: 'Administration & configuration',
  },
  clients: {
    title: 'Clients',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    permissions: ['clients.read'],
    navVisible: true,
    pageArchetype: 'workspace',
    shellSection: 'operations',
    shellDescription: 'Client workspace',
  },
  imports: {
    title: 'Imports',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    permissions: ['imports.read'],
    navVisible: true,
    pageArchetype: 'workspace',
    shellSection: 'operations',
    shellDescription: 'Operational workspace',
  },
  calendar: {
    title: 'Calendar & tasks',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    permissions: ['calendar.read'],
    navVisible: true,
    pageArchetype: 'workspace',
    shellSection: 'operations',
    shellDescription: 'Operational workspace',
  },
  communications: {
    title: 'Communications',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    permissions: ['clients.communications.read'],
    navVisible: true,
    pageArchetype: 'workspace',
    shellSection: 'operations',
    shellDescription: 'Client workspace',
  },
  rules: {
    title: 'Rules Library',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    permissions: ['rules.read'],
    navVisible: true,
    pageArchetype: 'governance',
    shellSection: 'governance',
    shellDescription: 'Rules & workflow governance',
  },
  workflows: {
    title: 'Workflow Builder',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    permissions: ['workflows.read'],
    navVisible: true,
    pageArchetype: 'governance',
    shellSection: 'governance',
    shellDescription: 'Rules & workflow governance',
  },
  audit: {
    title: 'Audit',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    permissions: ['audit.read'],
    navVisible: true,
    pageArchetype: 'audit',
    shellSection: 'governance',
    shellDescription: 'Evidence & investigation',
  },
} as const
