import { Navigate } from 'react-router-dom'
import type { RouteObject } from 'react-router-dom'
import { ProtectedRoute, PublicOnlyRoute, RouteGate } from '@/routes/route-guards'
import { AppShellLayout } from '@/layouts/app-shell-layout'
import { AuthLayout } from '@/layouts/auth-layout'
import { BlankLayout } from '@/layouts/blank-layout'
import { SignInPage } from '@/features/identity-access/pages/sign-in-page'
import { HomepagePage } from '@/features/homepage-analytics/pages/homepage-page'
import { SettingsProfilePage } from '@/features/identity-access/pages/settings-profile-page'
import { SettingsAccountsPage } from '@/features/identity-access/pages/settings-accounts-page'
import { SettingsThemePage } from '@/features/identity-access/pages/settings-theme-page'
import { SettingsIndustryConfigurationsPage } from '@/features/identity-access/pages/settings-industry-configurations-page'
import { OnboardingPage } from '@/features/onboarding/pages/onboarding-page'
import { PlaceholderPage } from '@/features/shared/pages/placeholder-page'
import { ClientListPage } from '@/features/clients/pages/client-list-page'
import { ClientCreatePage } from '@/features/clients/pages/client-create-page'
import { ClientWorkspacePage } from '@/features/clients/pages/client-workspace-page'
import { RulesListPage } from '@/features/rules-library/pages/rules-list-page'
import { RuleDetailPage } from '@/features/rules-library/pages/rule-detail-page'
import { WorkflowsListPage } from '@/features/workflow-builder/pages/workflows-list-page'
import { WorkflowDetailPage } from '@/features/workflow-builder/pages/workflow-detail-page'
import { ImportsListPage } from '@/features/imports/pages/imports-list-page'
import { ImportDetailPage } from '@/features/imports/pages/import-detail-page'
import { AuditListPage } from '@/features/audit/pages/audit-list-page'
import { routeMeta } from '@/routes/route-meta'

export const routeConfig: RouteObject[] = [
  {
    path: '/',
    element: <Navigate to="/app/dashboard" replace />
  },
  {
    element: <PublicOnlyRoute />,
    children: [
      {
        path: '/sign-in',
        element: <AuthLayout />,
        children: [
          {
            index: true,
            handle: { meta: routeMeta.signIn },
            element: <SignInPage />
          }
        ]
      }
    ]
  },
  {
    element: <ProtectedRoute />,
    children: [
      {
        path: '/onboarding',
        element: <BlankLayout />,
        children: [
          {
            index: true,
            handle: { meta: routeMeta.onboarding },
            element: (
              <RouteGate meta={routeMeta.onboarding}>
                <OnboardingPage />
              </RouteGate>
            )
          }
        ]
      },
      {
        path: '/app',
        element: <AppShellLayout />,
        children: [
          { index: true, element: <Navigate to="/app/dashboard" replace /> },
          {
            path: 'dashboard',
            handle: { meta: routeMeta.dashboard },
            element: (
              <RouteGate meta={routeMeta.dashboard}>
                <HomepagePage />
              </RouteGate>
            )
          },
          {
            path: 'settings/profile',
            handle: { meta: routeMeta.settingsProfile },
            element: (
              <RouteGate meta={routeMeta.settingsProfile}>
                <SettingsProfilePage />
              </RouteGate>
            )
          },
          {
            path: 'settings/accounts',
            handle: { meta: routeMeta.settingsAccounts },
            element: (
              <RouteGate meta={routeMeta.settingsAccounts}>
                <SettingsAccountsPage />
              </RouteGate>
            )
          },
          {
            path: 'settings/theme',
            handle: { meta: routeMeta.settingsTheme },
            element: (
              <RouteGate meta={routeMeta.settingsTheme}>
                <SettingsThemePage />
              </RouteGate>
            )
          },
          {
            path: 'settings/industry-configurations',
            handle: { meta: routeMeta.settingsIndustryConfigurations },
            element: (
              <RouteGate meta={routeMeta.settingsIndustryConfigurations}>
                <SettingsIndustryConfigurationsPage />
              </RouteGate>
            )
          },
          {
            path: 'clients',
            handle: { meta: routeMeta.clients },
            element: (
              <RouteGate meta={routeMeta.clients}>
                <ClientListPage />
              </RouteGate>
            )
          },
          {
            path: 'clients/new',
            handle: { meta: routeMeta.clients },
            element: (
              <RouteGate meta={routeMeta.clients}>
                <ClientCreatePage />
              </RouteGate>
            )
          },
          {
            path: 'clients/:clientId/:tab?',
            handle: { meta: routeMeta.clients },
            element: (
              <RouteGate meta={routeMeta.clients}>
                <ClientWorkspacePage />
              </RouteGate>
            )
          },
          {
            path: 'imports',
            handle: { meta: routeMeta.imports },
            element: (
              <RouteGate meta={routeMeta.imports}>
                <ImportsListPage />
              </RouteGate>
            )
          },
          {
            path: 'imports/:importId',
            handle: { meta: routeMeta.imports },
            element: (
              <RouteGate meta={routeMeta.imports}>
                <ImportDetailPage />
              </RouteGate>
            )
          },
          {
            path: 'calendar',
            handle: { meta: routeMeta.calendar },
            element: (
              <RouteGate meta={routeMeta.calendar}>
                <PlaceholderPage title="Calendar & Tasks" />
              </RouteGate>
            )
          },
          {
            path: 'communications',
            handle: { meta: routeMeta.communications },
            element: (
              <RouteGate meta={routeMeta.communications}>
                <PlaceholderPage title="Communications" />
              </RouteGate>
            )
          },
          {
            path: 'rules',
            handle: { meta: routeMeta.rules },
            element: (
              <RouteGate meta={routeMeta.rules}>
                <RulesListPage />
              </RouteGate>
            )
          },
          {
            path: 'rules/:ruleId',
            handle: { meta: routeMeta.rules },
            element: (
              <RouteGate meta={routeMeta.rules}>
                <RuleDetailPage />
              </RouteGate>
            )
          },
          {
            path: 'workflows',
            handle: { meta: routeMeta.workflows },
            element: (
              <RouteGate meta={routeMeta.workflows}>
                <WorkflowsListPage />
              </RouteGate>
            )
          },
          {
            path: 'workflows/:workflowId',
            handle: { meta: routeMeta.workflows },
            element: (
              <RouteGate meta={routeMeta.workflows}>
                <WorkflowDetailPage />
              </RouteGate>
            )
          },
          {
            path: 'audit',
            handle: { meta: routeMeta.audit },
            element: (
              <RouteGate meta={routeMeta.audit}>
                <AuditListPage />
              </RouteGate>
            )
          }
        ]
      }
    ]
  }
]
