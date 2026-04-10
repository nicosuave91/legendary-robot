import type { LucideIcon } from 'lucide-react'
import {
  CalendarDays,
  LayoutGrid,
  Library,
  MessageSquare,
  Network,
  Palette,
  Settings,
  Shield,
  Upload,
  UserRound,
  Users,
  Workflow,
} from 'lucide-react'
import type { PermissionCode } from '@/lib/auth/permission-map'
import { routeMeta } from '@/routes/route-meta'

type AppNavigationRouteKey =
  | 'dashboard'
  | 'settingsProfile'
  | 'settingsAccounts'
  | 'settingsTheme'
  | 'settingsIndustryConfigurations'
  | 'clients'
  | 'imports'
  | 'calendar'
  | 'communications'
  | 'rules'
  | 'workflows'
  | 'audit'

type AppNavigationItem = {
  routeKey: AppNavigationRouteKey
  to: string
  label: string
  icon: LucideIcon
  permissions: readonly PermissionCode[]
  group: 'operations' | 'administration'
}

export const appNavigationItems = [
  {
    routeKey: 'dashboard',
    to: '/app/dashboard',
    label: 'Homepage',
    icon: LayoutGrid,
    permissions: routeMeta.dashboard.permissions ?? [],
    group: 'operations',
  },
  {
    routeKey: 'settingsProfile',
    to: '/app/settings/profile',
    label: 'My Profile',
    icon: UserRound,
    permissions: routeMeta.settingsProfile.permissions ?? [],
    group: 'administration',
  },
  {
    routeKey: 'settingsAccounts',
    to: '/app/settings/accounts',
    label: 'Accounts',
    icon: Settings,
    permissions: routeMeta.settingsAccounts.permissions ?? [],
    group: 'administration',
  },
  {
    routeKey: 'settingsTheme',
    to: '/app/settings/theme',
    label: 'Branding',
    icon: Palette,
    permissions: routeMeta.settingsTheme.permissions ?? [],
    group: 'administration',
  },
  {
    routeKey: 'settingsIndustryConfigurations',
    to: '/app/settings/industry-configurations',
    label: 'Industry Config',
    icon: Network,
    permissions: routeMeta.settingsIndustryConfigurations.permissions ?? [],
    group: 'administration',
  },
  {
    routeKey: 'clients',
    to: '/app/clients',
    label: 'Clients',
    icon: Users,
    permissions: routeMeta.clients.permissions ?? [],
    group: 'operations',
  },
  {
    routeKey: 'imports',
    to: '/app/imports',
    label: 'Imports',
    icon: Upload,
    permissions: routeMeta.imports.permissions ?? [],
    group: 'operations',
  },
  {
    routeKey: 'calendar',
    to: '/app/calendar',
    label: 'Calendar',
    icon: CalendarDays,
    permissions: routeMeta.calendar.permissions ?? [],
    group: 'operations',
  },
  {
    routeKey: 'communications',
    to: '/app/communications',
    label: 'Communications',
    icon: MessageSquare,
    permissions: routeMeta.communications.permissions ?? [],
    group: 'operations',
  },
  {
    routeKey: 'rules',
    to: '/app/rules',
    label: 'Rules Library',
    icon: Library,
    permissions: routeMeta.rules.permissions ?? [],
    group: 'administration',
  },
  {
    routeKey: 'workflows',
    to: '/app/workflows',
    label: 'Workflow Builder',
    icon: Workflow,
    permissions: routeMeta.workflows.permissions ?? [],
    group: 'administration',
  },
  {
    routeKey: 'audit',
    to: '/app/audit',
    label: 'Audit',
    icon: Shield,
    permissions: routeMeta.audit.permissions ?? [],
    group: 'administration',
  },
] satisfies readonly AppNavigationItem[]

export function navigationKeysWithRouteMeta() {
  return appNavigationItems.map((item) => item.routeKey)
}
