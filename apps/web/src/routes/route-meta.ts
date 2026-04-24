import type { PermissionCode } from '@/lib/auth/permission-map'

export type PageArchetype =
  | 'cockpit'
  | 'workspace'
  | 'settings'
  | 'governance'
  | 'audit'

export type ShellSection = 'operations' | 'administration' | 'governance'

export type RouteMeta = {
  title: string
  requiresAuth?: boolean
  requiresOnboardingComplete?: boolean
  onboardingEligible?: boolean
  permissions?: readonly PermissionCode[]
  navVisible?: boolean
  pageArchetype?: PageArchetype
  shellSection?: ShellSection
  shellSectionLabel?: string
  shellSectionDescription?: string
}

export const routeMeta: Record<string, RouteMeta> = {
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
    shellSectionLabel: 'Daily operations',
    shellSectionDescription: 'Cockpit and production work',
  },
  settingsProfile: {
    title: 'My Profile',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    permissions: ['settings.profile.read'],
    navVisible: true,
    pageArchetype: 'settings',
    shellSection: 'administration',
    shellSectionLabel: 'Administration',
    shellSectionDescription: 'Accounts, profile, and tenant settings',
  },
  settingsAccounts: {
    title: 'Accounts',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    permissions: ['settings.accounts.read'],
    navVisible: true,
    pageArchetype: 'settings',
    shellSection: 'administration',
    shellSectionLabel: 'Administration',
    shellSectionDescription: 'Accounts, profile, and tenant settings',
  },
  settingsTheme: {
    title: 'Branding tokens',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    permissions: ['settings.theme.read'],
    navVisible: true,
    pageArchetype: 'settings',
    shellSection: 'administration',
    shellSectionLabel: 'Administration',
    shellSectionDescription: 'Accounts, profile, and tenant settings',
  },
  settingsIndustryConfigurations: {
    title: 'Industry configurations',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    permissions: ['settings.industry-configurations.read'],
    navVisible: true,
    pageArchetype: 'settings',
    shellSection: 'administration',
    shellSectionLabel: 'Administration',
    shellSectionDescription: 'Accounts, profile, and tenant settings',
  },
  clients: {
    title: 'Clients',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    permissions: ['clients.read'],
    navVisible: true,
    pageArchetype: 'workspace',
    shellSection: 'operations',
    shellSectionLabel: 'Daily operations',
    shellSectionDescription: 'Cockpit and production work',
  },
  imports: {
    title: 'Imports',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    permissions: ['imports.read'],
    navVisible: true,
    pageArchetype: 'workspace',
    shellSection: 'operations',
    shellSectionLabel: 'Daily operations',
    shellSectionDescription: 'Cockpit and production work',
  },
  calendar: {
    title: 'Calendar & Tasks',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    permissions: ['calendar.read'],
    navVisible: true,
    pageArchetype: 'workspace',
    shellSection: 'operations',
    shellSectionLabel: 'Daily operations',
    shellSectionDescription: 'Cockpit and production work',
  },
  communications: {
    title: 'Communications',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    permissions: ['clients.communications.read'],
    navVisible: true,
    pageArchetype: 'workspace',
    shellSection: 'operations',
    shellSectionLabel: 'Daily operations',
    shellSectionDescription: 'Cockpit and production work',
  },
  rules: {
    title: 'Rules Library',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    permissions: ['rules.read'],
    navVisible: true,
    pageArchetype: 'governance',
    shellSection: 'governance',
    shellSectionLabel: 'Governance',
    shellSectionDescription: 'Rules, workflows, and investigations',
  },
  workflows: {
    title: 'Workflow Builder',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    permissions: ['workflows.read'],
    navVisible: true,
    pageArchetype: 'governance',
    shellSection: 'governance',
    shellSectionLabel: 'Governance',
    shellSectionDescription: 'Rules, workflows, and investigations',
  },
  audit: {
    title: 'Audit',
    requiresAuth: true,
    requiresOnboardingComplete: true,
    permissions: ['audit.read'],
    navVisible: true,
    pageArchetype: 'audit',
    shellSection: 'governance',
    shellSectionLabel: 'Governance',
    shellSectionDescription: 'Rules, workflows, and investigations',
  },
}
