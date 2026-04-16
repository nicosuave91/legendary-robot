import { LogOut, Search } from 'lucide-react'
import { useMatches, useNavigate } from 'react-router-dom'
import { AppButton, AppInput } from '@/components/ui'
import { useAuth } from '@/lib/auth/auth-hooks'
import { useToast } from '@/components/shell/toast-host'
import { NotificationBellButton } from '@/features/notifications/components/notification-bell-button'
import type { RouteMeta } from '@/routes/route-meta'

function resolveActiveMeta(matches: ReturnType<typeof useMatches>) {
  const activeMatch = [...matches]
    .reverse()
    .find(
      (match) =>
        typeof match.handle === 'object' &&
        match.handle !== null &&
        'meta' in match.handle,
    )

  return activeMatch?.handle &&
    typeof activeMatch.handle === 'object' &&
    'meta' in activeMatch.handle
    ? (activeMatch.handle.meta as RouteMeta)
    : undefined
}

export function AppHeader() {
  const matches = useMatches()
  const navigate = useNavigate()
  const { data, signOut } = useAuth()
  const { notify } = useToast()

  const activeMeta = resolveActiveMeta(matches)
  const workspaceName = data?.tenant.name ?? 'Workspace'

  const handleSignOut = async () => {
    await signOut()
    notify({
      title: 'Signed out',
      description: 'Your server-backed session has been cleared.',
      tone: 'success',
    })
    navigate('/sign-in', { replace: true })
  }

  return (
    <header className="sticky top-0 z-20 border-b border-border bg-surface/96 backdrop-blur">
      <div className="flex min-h-[68px] items-center gap-4 px-5 xl:px-6">
        <div className="min-w-0 shrink-0 xl:w-[260px]">
          <div className="text-[11px] font-semibold uppercase tracking-[0.2em] text-text-muted">
            {activeMeta?.shellSectionLabel ?? 'Workspace'}
          </div>
          <div className="mt-1 truncate text-[15px] font-medium leading-5 text-text">
            {workspaceName}
          </div>
        </div>

        <div className="hidden min-w-0 max-w-[560px] flex-1 xl:block">
          <div className="relative">
            <Search
              size={16}
              className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-text-muted"
            />
            <AppInput
              aria-label="Global search"
              className="bg-background pl-9"
              placeholder="Search clients, workflows, or audit"
            />
          </div>
        </div>

        <div className="ml-auto flex items-center gap-2">
          <NotificationBellButton />
          <AppButton
            type="button"
            size="sm"
            variant="ghost"
            onClick={() => void handleSignOut()}
          >
            <LogOut size={16} />
            Sign out
          </AppButton>
        </div>
      </div>
    </header>
  )
}
