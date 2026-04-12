import { LogOut } from 'lucide-react'
import { useNavigate } from 'react-router-dom'
import { AppButton } from '@/components/ui'
import { useAuth } from '@/lib/auth/auth-hooks'
import { useToast } from '@/components/shell/toast-host'
import { NotificationBellButton } from '@/features/notifications/components/notification-bell-button'

export function AppHeader() {
  const navigate = useNavigate()
  const { data, signOut } = useAuth()
  const { notify } = useToast()

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
    <header className="sticky top-0 z-10 border-b border-border bg-background/95 px-6 py-3 backdrop-blur">
      <div className="flex min-h-10 items-center justify-between gap-4">
        <div className="min-w-0 flex-1">
          <div className="truncate text-sm font-medium text-text-muted">
            {data?.tenant.name ?? 'Workspace'}
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
