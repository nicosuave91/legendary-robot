import { LogOut, PanelLeft } from 'lucide-react'
import { useNavigate } from 'react-router-dom'
import { AppButton } from '@/components/ui'
import { useAuth } from '@/lib/auth/auth-hooks'
import { useToast } from '@/components/shell/toast-host'
import { NotificationBellButton } from '@/features/notifications/components/notification-bell-button'

type AppHeaderProps = {
  onToggleSidebar: () => void
}

export function AppHeader({ onToggleSidebar }: AppHeaderProps) {
  const navigate = useNavigate()
  const { data, signOut } = useAuth()
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

  const workspaceSubtitle = data?.selectedIndustry
    ? `${data.selectedIndustry} · ${data.selectedIndustryConfigVersion ?? 'current configuration'}`
    : 'Server-governed CRM workspace'

  return (
    <header className="sticky top-0 z-10 border-b border-border bg-background/95 px-6 py-4 backdrop-blur">
      <div className="flex flex-wrap items-center justify-between gap-4">
        <div className="flex min-w-0 flex-1 items-center gap-3">
          <AppButton type="button" variant="secondary" onClick={onToggleSidebar} aria-label="Collapse navigation">
            <PanelLeft size={16} />
          </AppButton>

          <div className="min-w-0">
            <div className="label-sm uppercase tracking-[0.12em] text-text-muted">Workspace</div>
            <div className="heading-md truncate text-text">{data?.tenant.name ?? 'Workspace'}</div>
            <div className="body-sm truncate text-text-muted">{workspaceSubtitle}</div>
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
