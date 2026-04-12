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

function getInitials(name?: string) {
  if (!name) {
    return 'U'
  }

  const initials = name
    .trim()
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((segment) => segment[0]?.toUpperCase() ?? '')
    .join('')

  return initials || 'U'
}

function NavigationGroup({
  collapsed,
  items,
  subdued = false,
}: {
  collapsed: boolean
  items: typeof appNavigationItems
  subdued?: boolean
}) {
  return (
    <div className="space-y-1">
      {items.map((item) => {
        const Icon = item.icon

        return (
          <NavLink
            key={item.routeKey}
            to={item.to}
            className={({ isActive }) =>
              cn(
                'flex h-10 items-center gap-3 rounded-lg text-sm font-medium text-text-muted transition motion-base hover:bg-muted hover:text-text',
                collapsed ? 'justify-center px-0' : 'px-3',
                subdued && !isActive && 'opacity-75 hover:opacity-100',
                isActive && 'bg-muted text-text opacity-100',
              )
            }
            title={collapsed ? item.label : undefined}
          >
            <Icon size={16} />
            {!collapsed ? <span className="truncate">{item.label}</span> : null}
          </NavLink>
        )
      })}
    </div>
  )
}

export function AppSidebar({ collapsed, onToggle }: AppSidebarProps) {
  const { data } = useAuth()

  const visibleItems = appNavigationItems.filter((item) =>
    hasAllPermissions(data?.permissions ?? [], item.permissions),
  )

  const operationalItems = visibleItems.filter((item) => item.group === 'operations')
  const administrativeItems = visibleItems.filter(
    (item) => item.group === 'administration',
  )

  const displayName = data?.user.displayName ?? 'User'
  const workspaceName = data?.tenant.name ?? 'Workspace'
  const initials = getInitials(displayName)

  return (
    <aside
      className={cn(
        'sticky top-0 hidden h-screen shrink-0 border-r border-border bg-surface px-4 py-5 transition-all duration-200 lg:flex lg:flex-col',
        collapsed ? 'w-[88px]' : 'w-[240px]',
      )}
    >
      <div
        className={cn(
          'mb-6 flex items-start gap-3',
          collapsed ? 'justify-center' : 'justify-between',
        )}
      >
        <div className={cn('overflow-hidden', collapsed && 'sr-only')}>
          <div className="label-sm uppercase tracking-[0.16em] text-text-muted">
            Snowball
          </div>
          <div className="heading-lg text-text">CRM Platform</div>
        </div>

        <AppButton
          type="button"
          variant="secondary"
          size="sm"
          onClick={onToggle}
          aria-label={collapsed ? 'Expand navigation' : 'Collapse navigation'}
        >
          {collapsed ? <PanelLeftOpen size={16} /> : <PanelLeftClose size={16} />}
        </AppButton>
      </div>

      <nav className="flex flex-1 flex-col gap-4" aria-label="Primary">
        <NavigationGroup collapsed={collapsed} items={operationalItems} />

        {operationalItems.length > 0 && administrativeItems.length > 0 ? (
          <div className="border-t border-border/80 pt-4" aria-hidden="true" />
        ) : null}

        <NavigationGroup
          collapsed={collapsed}
          items={administrativeItems}
          subdued
        />
      </nav>

      <div className="mt-6 border-t border-border/80 pt-4">
        <div
          className={cn(
            'flex items-center gap-3',
            collapsed && 'justify-center',
          )}
          title={collapsed ? `${displayName} · ${workspaceName}` : undefined}
        >
          <div className="flex size-8 shrink-0 items-center justify-center rounded-full bg-muted text-[11px] font-semibold uppercase tracking-[0.08em] text-text">
            {initials}
          </div>

          {!collapsed ? (
            <div className="min-w-0">
              <div className="truncate text-[12px] font-medium leading-4 text-text">
                {displayName}
              </div>
              <div className="truncate text-[10px] leading-[14px] text-text-muted">
                {workspaceName}
              </div>
            </div>
          ) : null}
        </div>
      </div>
    </aside>
  )
}
