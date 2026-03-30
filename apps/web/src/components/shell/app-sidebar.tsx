import { LayoutGrid, Users, CalendarDays, MessageSquare, Shield, PanelLeftClose, PanelLeftOpen } from 'lucide-react'
import { NavLink } from 'react-router-dom'
import { AppBadge, AppButton } from '@/components/ui'
import { cn } from '@/lib/utils/cn'

const items = [
  { to: '/app/dashboard', label: 'Dashboard', icon: LayoutGrid },
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
          {!collapsed ? <AppBadge variant="info">Sprint 1</AppBadge> : null}
        </div>
      </div>

      <nav className="space-y-2" aria-label="Primary">
        {items.map((item) => {
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
          <div className="heading-md mt-1">Default Workspace</div>
          <div className="body-sm mt-1 text-text-muted">Tenant-aware shell scaffold only.</div>
        </div>
      ) : null}
    </aside>
  )
}
