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
}

export const appNavigationItems = [
  {
    routeKey: 'dashboard',
    to: '/app/dashboard',
    label: 'Homepage',
    icon: LayoutGrid,
    permissions: routeMeta.dashboard.permissions ?? [],
  },
  {
    routeKey: 'settingsProfile',
    to: '/app/settings/profile',
    label: 'My Profile',
    icon: UserRound,
    permissions: routeMeta.settingsProfile.permissions ?? [],
  },
  {
    routeKey: 'settingsAccounts',
    to: '/app/settings/accounts',
    label: 'Accounts',
    icon: Settings,
    permissions: routeMeta.settingsAccounts.permissions ?? [],
  },
  {
    routeKey: 'settingsTheme',
    to: '/app/settings/theme',
    label: 'Branding',
    icon: Palette,
    permissions: routeMeta.settingsTheme.permissions ?? [],
  },
  {
    routeKey: 'settingsIndustryConfigurations',
    to: '/app/settings/industry-configurations',
    label: 'Industry Config',
    icon: Network,
    permissions: routeMeta.settingsIndustryConfigurations.permissions ?? [],
  },
  {
    routeKey: 'clients',
    to: '/app/clients',
    label: 'Clients',
    icon: Users,
    permissions: routeMeta.clients.permissions ?? [],
  },
  {
    routeKey: 'imports',
    to: '/app/imports',
    label: 'Imports',
    icon: Upload,
    permissions: routeMeta.imports.permissions ?? [],
  },
  {
    routeKey: 'calendar',
    to: '/app/calendar',
    label: 'Calendar',
    icon: CalendarDays,
    permissions: routeMeta.calendar.permissions ?? [],
  },
  {
    routeKey: 'communications',
    to: '/app/communications',
    label: 'Communications',
    icon: MessageSquare,
    permissions: routeMeta.communications.permissions ?? [],
  },
  {
    routeKey: 'rules',
    to: '/app/rules',
    label: 'Rules Library',
    icon: Library,
    permissions: routeMeta.rules.permissions ?? [],
  },
  {
    routeKey: 'workflows',
    to: '/app/workflows',
    label: 'Workflow Builder',
    icon: Workflow,
    permissions: routeMeta.workflows.permissions ?? [],
  },
  {
    routeKey: 'audit',
    to: '/app/audit',
    label: 'Audit',
    icon: Shield,
    permissions: routeMeta.audit.permissions ?? [],
  },
] satisfies readonly AppNavigationItem[]

export function navigationKeysWithRouteMeta() {
  return appNavigationItems.map((item) => item.routeKey)
}
