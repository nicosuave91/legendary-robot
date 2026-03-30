import { RouterProvider } from 'react-router-dom'
import { QueryClientProvider } from '@tanstack/react-query'
import { ThemeProvider } from '@/lib/theme/theme-provider'
import { AuthProvider } from '@/lib/auth/auth-context'
import { queryClient } from '@/lib/api/query-client'
import { router } from '@/app/router/router'
import { AppErrorBoundary } from '@/app/providers/error-boundary'
import { ToastProvider } from '@/components/shell/toast-host'

export function AppProviders() {
  return (
    <AppErrorBoundary>
      <ThemeProvider>
        <QueryClientProvider client={queryClient}>
          <ToastProvider>
            <AuthProvider>
              <RouterProvider router={router} />
            </AuthProvider>
          </ToastProvider>
        </QueryClientProvider>
      </ThemeProvider>
    </AppErrorBoundary>
  )
}
