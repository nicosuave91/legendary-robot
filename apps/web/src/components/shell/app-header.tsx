import { Search, LogOut, PanelLeft } from 'lucide-react'
import { useNavigate } from 'react-router-dom'
import { AppButton, AppInput } from '@/components/ui'
import { useAuth } from '@/lib/auth/auth-hooks'
import { useToast } from '@/components/shell/toast-host'
import { NotificationBellButton } from '@/features/notifications/components/notification-bell-button'

type AppHeaderProps = {
  onToggleSidebar: () => void
}

export function AppHeader({ onToggleSidebar }: AppHeaderProps) {
  const navigate = useNavigate()
  const { signOut } = useAuth()
  const { notify } = useToast()

  const handleSignOut = async () => {
    await signOut()
    notify({
      title: 'Signed out',
      description: 'Your server-backed session has been cleared.',
      tone: 'success'
    })
    navigate('/sign-in', { replace: true })
  }

  return (
    <header className="sticky top-0 z-10 border-b border-border bg-background/95 px-6 py-4 backdrop-blur">
      <div className="flex flex-wrap items-center justify-between gap-4">
        <div className="flex max-w-2xl flex-1 items-center gap-3">
          <AppButton type="button" variant="secondary" onClick={onToggleSidebar} aria-label="Collapse navigation">
            <PanelLeft size={16} />
          </AppButton>

          <div className="max-w-md flex-1">
            <label className="sr-only" htmlFor="global-search">
              Search
            </label>
            <div className="relative">
              <Search className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-text-muted" size={16} />
              <AppInput
                id="global-search"
                placeholder="Search scaffold"
                className="pl-9"
                aria-label="Search scaffold"
              />
            </div>
          </div>
        </div>

        <div className="flex items-center gap-3">
          <NotificationBellButton />
          <AppButton type="button" variant="ghost" onClick={() => void handleSignOut()}>
            <LogOut size={16} />
            Sign out
          </AppButton>
        </div>
      </div>
    </header>
  )
}
