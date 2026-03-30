import { Navigate } from 'react-router-dom'
import type { RouteObject } from 'react-router-dom'
import { ProtectedRoute } from '@/routes/route-guards'
import { AppShellLayout } from '@/layouts/app-shell-layout'
import { AuthLayout } from '@/layouts/auth-layout'
import { SignInPage } from '@/features/identity-access/pages/sign-in-page'
import { DashboardPage } from '@/features/identity-access/pages/dashboard-page'
import { SettingsProfilePage } from '@/features/identity-access/pages/settings-profile-page'
import { PlaceholderPage } from '@/features/shared/pages/placeholder-page'

export const routeConfig: RouteObject[] = [
  {
    path: '/',
    element: <Navigate to="/app/dashboard" replace />
  },
  {
    path: '/sign-in',
    element: <AuthLayout />,
    children: [{ index: true, element: <SignInPage /> }]
  },
  {
    element: <ProtectedRoute />,
    children: [
      {
        path: '/app',
        element: <AppShellLayout />,
        children: [
          { index: true, element: <Navigate to="/app/dashboard" replace /> },
          { path: 'dashboard', element: <DashboardPage /> },
          { path: 'settings/profile', element: <SettingsProfilePage /> },
          { path: 'settings/accounts', element: <PlaceholderPage title="Accounts" /> },
          { path: 'clients', element: <PlaceholderPage title="Clients" /> },
          { path: 'calendar', element: <PlaceholderPage title="Calendar & Tasks" /> },
          { path: 'communications', element: <PlaceholderPage title="Communications" /> },
          { path: 'audit', element: <PlaceholderPage title="Audit" /> }
        ]
      }
    ]
  }
]
