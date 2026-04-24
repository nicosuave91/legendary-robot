import { PropsWithChildren } from 'react'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { render, screen, waitFor, cleanup } from '@testing-library/react'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { AuthProvider, useAuthContext } from '@/lib/auth/auth-context'
import { ThemeProvider, useThemeContract } from '@/lib/theme/theme-provider'
import { defaultTheme } from '@/lib/theme/tokens'
import { ApiError } from '@/lib/api/http'
import { authApi } from '@/lib/api/client'

vi.mock('@/lib/api/client', () => ({
  authApi: {
    me: vi.fn(),
    signIn: vi.fn(),
    signOut: vi.fn(),
  },
}))

const mockedAuthApi = vi.mocked(authApi)

function Providers({ children }: PropsWithChildren) {
  const queryClient = new QueryClient({
    defaultOptions: {
      queries: {
        retry: false,
      },
      mutations: {
        retry: false,
      },
    },
  })

  return (
    <QueryClientProvider client={queryClient}>
      <ThemeProvider>
        <AuthProvider>{children}</AuthProvider>
      </ThemeProvider>
    </QueryClientProvider>
  )
}

function Probe() {
  const auth = useAuthContext()
  const { theme } = useThemeContract()

  return (
    <div>
      <div data-testid="is-authenticated">{String(auth.isAuthenticated)}</div>
      <div data-testid="industry">{auth.data?.selectedIndustry ?? ''}</div>
      <div data-testid="industry-version">{auth.data?.selectedIndustryConfigVersion ?? ''}</div>
      <div data-testid="capabilities">{(auth.data?.capabilities ?? []).join(',')}</div>
      <div data-testid="theme-primary">{theme.brand.primary}</div>
      <div data-testid="theme-secondary">{theme.brand.secondary}</div>
      <div data-testid="theme-tertiary">{theme.brand.tertiary}</div>
    </div>
  )
}

describe('AuthProvider', () => {
  beforeEach(() => {
    mockedAuthApi.me.mockReset()
    mockedAuthApi.signIn.mockReset()
    mockedAuthApi.signOut.mockReset()
    document.documentElement.style.cssText = ''
  })

  afterEach(() => {
    cleanup()
  })

  it('hydrates theme, selected industry, and capabilities from auth bootstrap', async () => {
    mockedAuthApi.me.mockResolvedValueOnce({
      data: {
        isAuthenticated: true,
        user: {
          id: 'user-1',
          email: 'user@example.com',
          displayName: 'Runtime User',
        },
        tenant: {
          id: 'tenant-1',
          name: 'Default Workspace',
        },
        roles: ['user'],
        permissions: ['calendar.read'],
        onboardingState: 'completed',
        onboardingStep: null,
        theme: {
          primary: '#112233',
          secondary: '#223344',
          tertiary: '#334455',
        },
        landingRoute: '/app/dashboard',
        selectedIndustry: 'Mortgage',
        selectedIndustryConfigVersion: 'v2',
        capabilities: ['calendar', 'communications', 'underwriting-dashboard'],
      },
      meta: {
        apiVersion: 'v1',
        correlationId: 'corr-auth-theme-test',
      },
    })

    render(
      <Providers>
        <Probe />
      </Providers>,
    )

    await waitFor(() => {
      expect(screen.getByTestId('is-authenticated')).toHaveTextContent('true')
    })

    expect(screen.getByTestId('industry')).toHaveTextContent('Mortgage')
    expect(screen.getByTestId('industry-version')).toHaveTextContent('v2')
    expect(screen.getByTestId('capabilities')).toHaveTextContent('calendar,communications,underwriting-dashboard')
    expect(screen.getByTestId('theme-primary')).toHaveTextContent('#112233')
    expect(screen.getByTestId('theme-secondary')).toHaveTextContent('#223344')
    expect(screen.getByTestId('theme-tertiary')).toHaveTextContent('#334455')

    await waitFor(() => {
      expect(document.documentElement.style.getPropertyValue('--color-primary')).toBe('#112233')
      expect(document.documentElement.style.getPropertyValue('--color-secondary')).toBe('#223344')
      expect(document.documentElement.style.getPropertyValue('--color-accent')).toBe('#334455')
    })
  })

  it('falls back to the default theme when auth bootstrap returns 401', async () => {
    mockedAuthApi.me.mockRejectedValueOnce(new ApiError(401, 'Unauthorized', { message: 'Unauthorized' }))

    render(
      <Providers>
        <Probe />
      </Providers>,
    )

    await waitFor(() => {
      expect(screen.getByTestId('is-authenticated')).toHaveTextContent('false')
    })

    expect(screen.getByTestId('industry')).toHaveTextContent('')
    expect(screen.getByTestId('industry-version')).toHaveTextContent('')
    expect(screen.getByTestId('capabilities')).toHaveTextContent('')

    await waitFor(() => {
      expect(screen.getByTestId('theme-primary')).toHaveTextContent(defaultTheme.brand.primary)
      expect(screen.getByTestId('theme-secondary')).toHaveTextContent(defaultTheme.brand.secondary)
      expect(screen.getByTestId('theme-tertiary')).toHaveTextContent(defaultTheme.brand.tertiary)
      expect(document.documentElement.style.getPropertyValue('--color-primary')).toBe(defaultTheme.brand.primary)
    })
  })
})
