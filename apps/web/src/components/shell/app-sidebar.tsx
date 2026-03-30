import { CalendarDays, LayoutGrid, MessageSquare, Settings, Shield, Users, UserRound, Palette, Network, PanelLeftClose, PanelLeftOpen } from 'lucide-react'
import { NavLink } from 'react-router-dom'
import { AppBadge, AppButton } from '@/components/ui'
import { cn } from '@/lib/utils/cn'
import { useAuth } from '@/lib/auth/auth-hooks'
import { hasAllPermissions } from '@/lib/auth/permission-map'

const items = [
  { to: '/app/dashboard', label: 'Dashboard', icon: LayoutGrid },
  { to: '/app/settings/profile', label: 'My Profile', icon: UserRound, permissions: ['settings.profile.read'] as const },
  { to: '/app/settings/accounts', label: 'Accounts', icon: Settings, permissions: ['settings.accounts.read'] as const },
  { to: '/app/settings/theme', label: 'Branding', icon: Palette, permissions: ['settings.theme.read'] as const },
  { to: '/app/settings/industry-configurations', label: 'Industry Config', icon: Network, permissions: ['settings.industry-configurations.read'] as const },
  { to: '/app/clients', label: 'Clients', icon: Users },
  { to: '/app/calendar', label: 'Calendar', icon: CalendarDays },
  { to: '/app/communications', label: 'Communications', icon: MessageSquare },
  { to: '/app/audit', label: 'Audit', icon: Shield }
]

type AppSidebarProps = {
  collapsed: boolean
  onToggle: () => void
}

export function AppSidebar({ collapsed, onToggle }: AppSidebarProps) {
  const { data } = useAuth()
  const visibleItems = items.filter((item) => hasAllPermissions(data?.permissions ?? [], item.permissions ? [...item.permissions] : []))

  return (
    <aside
      className={cn(
        'sticky top-0 hidden h-screen shrink-0 border-r border-border bg-surface p-4 transition-all duration-200 lg:block',
        collapsed ? 'w-[92px]' : 'w-[264px]'
      )}
    >
      <div className="mb-6 flex items-start justify-between gap-3">
        <div className={cn('overflow-hidden', collapsed && 'sr-only')}>
          <div className="label-sm uppercase tracking-[0.16em] text-text-muted">Snowball</div>
          <div className="heading-lg text-text">CRM Platform</div>
        </div>
        <div className="flex flex-col items-end gap-2">
          <AppButton type="button" variant="secondary" size="sm" onClick={onToggle} aria-label="Toggle sidebar">
            {collapsed ? <PanelLeftOpen size={16} /> : <PanelLeftClose size={16} />}
          </AppButton>
          {!collapsed ? <AppBadge variant="info">Sprint 3</AppBadge> : null}
        </div>
      </div>

      <nav className="space-y-2" aria-label="Primary">
        {visibleItems.map((item) => {
          const Icon = item.icon
          return (
            <NavLink
              key={item.to}
              to={item.to}
              className={({ isActive }) =>
                cn(
                  'flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-text-muted transition motion-base hover:bg-muted hover:text-text',
                  collapsed && 'justify-center px-2',
                  isActive && 'bg-muted text-text'
                )
              }
              title={collapsed ? item.label : undefined}
            >
              <Icon size={16} />
              {!collapsed ? item.label : null}
            </NavLink>
          )
        })}
      </nav>

      {!collapsed ? (
        <div className="mt-8 rounded-lg border border-border bg-muted p-3">
          <div className="label-sm uppercase tracking-[0.12em] text-text-muted">Tenant</div>
          <div className="heading-md mt-1">{data?.tenant.name ?? 'Workspace'}</div>
          <div className="body-sm mt-1 text-text-muted">
            {data?.selectedIndustry
              ? `${data.selectedIndustry} resolves through ${data.selectedIndustryConfigVersion ?? 'no version'}.`
              : data?.roles.includes('owner')
                ? 'Owner governance controls available.'
                : 'Complete onboarding to resolve tenant-scoped capabilities.'}
          </div>
        </div>
      ) : null}
    </aside>
  )
}
