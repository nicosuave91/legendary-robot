import { PanelLeftClose, PanelLeftOpen } from 'lucide-react'
import { NavLink } from 'react-router-dom'
import { AppButton } from '@/components/ui'
import { cn } from '@/lib/utils/cn'
import { useAuth } from '@/lib/auth/auth-hooks'
import { hasAllPermissions } from '@/lib/auth/permission-map'
import { appNavigationItems } from '@/routes/app-navigation'

type AppSidebarProps = {
  collapsed: boolean
  onToggle: () => void
}

export function AppSidebar({ collapsed, onToggle }: AppSidebarProps) {
  const { data } = useAuth()
  const visibleItems = appNavigationItems.filter((item) =>
    hasAllPermissions(data?.permissions ?? [], item.permissions),
  )

  const operationalItems = visibleItems.filter((item) => item.group === 'operations')
  const administrativeItems = visibleItems.filter((item) => item.group === 'administration')
  const workspaceContext = [data?.tenant.name, data?.selectedIndustry].filter(Boolean).join(' · ')

  const renderNavItem = (item: (typeof visibleItems)[number], subdued = false) => {
    const Icon = item.icon

    return (
      <NavLink
        key={item.routeKey}
        to={item.to}
        className={({ isActive }) =>
          cn(
            'flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-text-muted transition motion-base hover:bg-muted hover:text-text',
            collapsed && 'justify-center px-2',
            subdued && !isActive && 'opacity-85',
            isActive && 'bg-muted text-text opacity-100',
          )
        }
        title={collapsed ? item.label : undefined}
      >
        <Icon size={16} />
        {!collapsed ? item.label : null}
      </NavLink>
    )
  }

  return (
    <aside
      className={cn(
        'sticky top-0 hidden h-screen shrink-0 border-r border-border bg-surface p-4 transition-all duration-200 lg:block',
        collapsed ? 'w-[92px]' : 'w-[264px]',
      )}
    >
      <div className="mb-6 flex items-start justify-between gap-3">
        <div className={cn('overflow-hidden', collapsed && 'sr-only')}>
          <div className="label-sm uppercase tracking-[0.16em] text-text-muted">Snowball</div>
          <div className="heading-lg text-text">CRM Platform</div>
        </div>
        <AppButton type="button" variant="secondary" size="sm" onClick={onToggle} aria-label="Toggle sidebar">
          {collapsed ? <PanelLeftOpen size={16} /> : <PanelLeftClose size={16} />}
        </AppButton>
      </div>

      <nav className="space-y-2" aria-label="Primary">
        {operationalItems.map((item) => renderNavItem(item))}

        {!collapsed && administrativeItems.length ? (
          <div className="my-3 border-t border-border pt-4" />
        ) : null}

        {administrativeItems.map((item) => renderNavItem(item, true))}
      </nav>

      {!collapsed ? (
        <div className="mt-8 border-t border-border pt-4">
          <div className="body-sm truncate text-text-muted">{workspaceContext || 'Workspace'}</div>
        </div>
      ) : null}
    </aside>
  )
}
