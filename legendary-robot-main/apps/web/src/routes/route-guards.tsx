import type { ReactNode } from 'react'
import { Navigate, Outlet, useLocation } from 'react-router-dom'
import { useAuth } from '@/lib/auth/auth-hooks'
import { LoadingSkeleton } from '@/components/ui/skeleton/loading-skeleton'
import type { RouteMeta } from '@/routes/route-meta'
import { hasAllPermissions } from '@/lib/auth/permission-map'

function LoadingState() {
  return (
    <div className="p-6">
      <LoadingSkeleton lines={4} />
    </div>
  )
}

function isOnboardingComplete(state?: string) {
  return state === 'completed' || state === 'not_applicable'
}

export function PublicOnlyRoute() {
  const { isLoading, isAuthenticated, data } = useAuth()

  if (isLoading) {
    return <LoadingState />
  }

  if (isAuthenticated) {
    return <Navigate to={data?.landingRoute ?? '/app/dashboard'} replace />
  }

  return <Outlet />
}

export function ProtectedRoute() {
  const { isLoading, isAuthenticated } = useAuth()
  const location = useLocation()

  if (isLoading) {
    return <LoadingState />
  }

  if (!isAuthenticated) {
    return <Navigate to="/sign-in" replace state={{ from: location.pathname }} />
  }

  return <Outlet />
}

export function RouteGate({ meta, children }: { meta: RouteMeta; children: ReactNode }) {
  const { isLoading, data } = useAuth()
  const location = useLocation()

  if (isLoading) {
    return <LoadingState />
  }

  if (!data?.isAuthenticated) {
    return <Navigate to="/sign-in" replace state={{ from: location.pathname }} />
  }

  const complete = isOnboardingComplete(data.onboardingState)

  if (meta.onboardingEligible) {
    if (complete) {
      return <Navigate to={data.landingRoute} replace />
    }
  } else if (meta.requiresOnboardingComplete !== false && !complete) {
    return <Navigate to="/onboarding" replace />
  }

  if (meta.permissions && !hasAllPermissions(data.permissions, meta.permissions)) {
    return <Navigate to={data.landingRoute ?? '/app/dashboard'} replace />
  }

  return <>{children}</>
}
