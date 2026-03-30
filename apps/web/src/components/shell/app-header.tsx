import { Bell, Search, LogOut, PanelLeft } from 'lucide-react'
import { AppButton, AppInput } from '@/components/ui'
import { useAuth } from '@/lib/auth/auth-hooks'

type AppHeaderProps = {
  onToggleSidebar: () => void
}

export function AppHeader({ onToggleSidebar }: AppHeaderProps) {
  const { signOut } = useAuth()

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
          <AppButton type="button" variant="secondary" aria-label="Notifications scaffold">
            <Bell size={16} />
            Notifications
          </AppButton>
          <AppButton type="button" variant="ghost" onClick={() => void signOut()}>
            <LogOut size={16} />
            Sign out
          </AppButton>
        </div>
      </div>
    </header>
  )
}
