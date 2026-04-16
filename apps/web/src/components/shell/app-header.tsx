import { LogOut, Search } from 'lucide-react'
import { useMatches, useNavigate } from 'react-router-dom'
import { AppButton, AppBadge } from '@/components/ui'
import { useAuth } from '@/lib/auth/auth-hooks'
import { useToast } from '@/components/shell/toast-host'
import { NotificationBellButton } from '@/features/notifications/components/notification-bell-button'
import type { RouteMeta } from '@/routes/route-meta'

const archetypeLabels: Record<
  NonNullable<RouteMeta['pageArchetype']>,
  string
> = {
  cockpit: 'Cockpit',
  workspace: 'Workspace',
  settings: 'Settings',
  governance: 'Governance',
  audit: 'Audit',
}

export function AppHeader() {
  const navigate = useNavigate()
  const matches = useMatches()
  const { data, signOut } = useAuth()
  const { notify } = useToast()

  const activeMatch = [...matches].reverse().find((match) => {
    const handle = match.handle as { meta?: RouteMeta } | undefined
    return Boolean(handle?.meta)
  })

  const activeMeta = (activeMatch?.handle as { meta?: RouteMeta } | undefined)?.meta

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
    <header className="sticky top-0 z-20 border-b border-border bg-background/95 px-5 py-3 backdrop-blur sm:px-6 xl:px-8">
      <div className="mx-auto flex min-h-14 w-full max-w-[1600px] items-center gap-4">
        <div className="min-w-0 flex-1">
          <div className="flex flex-wrap items-center gap-2">
            {activeMeta?.pageArchetype ? (
              <AppBadge variant="neutral">
                {archetypeLabels[activeMeta.pageArchetype]}
              </AppBadge>
            ) : null}
            <div className="truncate text-sm font-medium text-text">
              {activeMeta?.title ?? 'Workspace'}
            </div>
          </div>
          <div className="mt-1 truncate text-xs text-text-muted">
            {activeMeta?.shellDescription ?? 'Tenant-aware CRM shell'} ·{' '}
            {data?.tenant.name ?? 'Workspace'}
          </div>
        </div>

        <label className="hidden min-w-0 flex-1 items-center gap-2 rounded-xl border border-border bg-surface px-3 py-2 text-sm text-text-muted lg:flex">
          <Search size={16} />
          <input
            type="search"
            placeholder="Search clients, workflows, or audit evidence"
            className="w-full bg-transparent text-sm text-text outline-none placeholder:text-text-muted"
            aria-label="Global search"
          />
        </label>

        <div className="flex shrink-0 items-center gap-3">
          <NotificationBellButton />
          <AppButton
            type="button"
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
