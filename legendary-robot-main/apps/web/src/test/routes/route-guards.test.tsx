import { vi } from 'vitest'
import { MemoryRouter, Route, Routes } from 'react-router-dom'
import { render, screen } from '@testing-library/react'
import { ProtectedRoute, RouteGate } from '@/routes/route-guards'
import type { RouteMeta } from '@/routes/route-meta'
import { useAuth } from '@/lib/auth/auth-hooks'

vi.mock('@/lib/auth/auth-hooks', () => ({
  useAuth: vi.fn()
}))

const mockedUseAuth = vi.mocked(useAuth)

function renderWithRouter(element: React.ReactNode, initialEntries = ['/app/dashboard']) {
  return render(<MemoryRouter initialEntries={initialEntries}>{element}</MemoryRouter>)
}

describe('route guards', () => {
  it('redirects unauthenticated users to sign-in for protected routes', () => {
    mockedUseAuth.mockReturnValue({
      isLoading: false,
      isAuthenticated: false,
      data: null,
      signIn: vi.fn(),
      signOut: vi.fn(),
      refresh: vi.fn(),
    } as never)

    renderWithRouter(
      <Routes>
        <Route path="/sign-in" element={<div>Sign in page</div>} />
        <Route element={<ProtectedRoute />}>
          <Route path="/app/dashboard" element={<div>Protected content</div>} />
        </Route>
      </Routes>,
    )

    expect(screen.getByText('Sign in page')).toBeInTheDocument()
  })

  it('redirects onboarding-incomplete users to onboarding when the route requires completion', () => {
    mockedUseAuth.mockReturnValue({
      isLoading: false,
      isAuthenticated: true,
      data: {
        isAuthenticated: true,
        onboardingState: 'required',
        permissions: ['dashboard.summary.read', 'dashboard.production.read'],
        landingRoute: '/app/dashboard',
      },
      signIn: vi.fn(),
      signOut: vi.fn(),
      refresh: vi.fn(),
    } as never)

    const meta: RouteMeta = {
      title: 'Homepage',
      requiresAuth: true,
      requiresOnboardingComplete: true,
      permissions: ['dashboard.summary.read', 'dashboard.production.read'],
    }

    renderWithRouter(
      <Routes>
        <Route path="/onboarding" element={<div>Onboarding page</div>} />
        <Route path="/app/dashboard" element={<RouteGate meta={meta}><div>Dashboard</div></RouteGate>} />
      </Routes>,
    )

    expect(screen.getByText('Onboarding page')).toBeInTheDocument()
  })

  it('redirects to the landing route when a required permission is missing', () => {
    mockedUseAuth.mockReturnValue({
      isLoading: false,
      isAuthenticated: true,
      data: {
        isAuthenticated: true,
        onboardingState: 'completed',
        permissions: ['dashboard.summary.read'],
        landingRoute: '/app/dashboard',
      },
      signIn: vi.fn(),
      signOut: vi.fn(),
      refresh: vi.fn(),
    } as never)

    const meta: RouteMeta = {
      title: 'Audit',
      requiresAuth: true,
      requiresOnboardingComplete: true,
      permissions: ['audit.read'],
    }

    renderWithRouter(
      <Routes>
        <Route path="/app/dashboard" element={<div>Landing route</div>} />
        <Route path="/app/audit" element={<RouteGate meta={meta}><div>Audit route</div></RouteGate>} />
      </Routes>,
      ['/app/audit'],
    )

    expect(screen.getByText('Landing route')).toBeInTheDocument()
  })
})
