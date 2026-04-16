import { PanelLeftClose, PanelLeftOpen } from 'lucide-react'
import { NavLink } from 'react-router-dom'
import { AppButton } from '@/components/ui'
import { cn } from '@/lib/utils/cn'
import { useAuth } from '@/lib/auth/auth-hooks'
import { hasAllPermissions } from '@/lib/auth/permission-map'
import { appNavigationItems, type AppNavigationGroup } from '@/routes/app-navigation'

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

const navigationSections: Array<{
  key: AppNavigationGroup
  label: string
  description: string
}> = [
  {
    key: 'operations',
    label: 'Daily operations',
    description: 'Cockpit and production work',
  },
  {
    key: 'settings',
    label: 'Administration',
    description: 'Accounts, profile, and tenant settings',
  },
  {
    key: 'governance',
    label: 'Governance',
    description: 'Rules, workflows, and investigations',
  },
]

function NavigationSection({
  collapsed,
  label,
  description,
  items,
}: {
  collapsed: boolean
  label: string
  description: string
  items: typeof appNavigationItems
}) {
  if (items.length === 0) {
    return null
  }

  return (
    <section className="space-y-2">
      {!collapsed ? (
        <div className="px-2">
          <div className="label-sm uppercase tracking-[0.16em] text-text-muted">
            {label}
          </div>
          <div className="mt-1 text-[11px] leading-4 text-text-muted">
            {description}
          </div>
        </div>
      ) : null}

      <div className="space-y-1">
        {items.map((item) => {
          const Icon = item.icon

          return (
            <NavLink
              key={item.routeKey}
              to={item.to}
              className={({ isActive }) =>
                cn(
                  'flex min-h-11 items-center gap-3 rounded-xl border border-transparent text-sm font-medium text-text-muted transition motion-base hover:border-border hover:bg-background hover:text-text',
                  collapsed ? 'justify-center px-0' : 'px-3',
                  isActive &&
                    'border-primary/20 bg-background text-text shadow-xs',
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
    </section>
  )
}

export function AppSidebar({ collapsed, onToggle }: AppSidebarProps) {
  const { data } = useAuth()

  const visibleItems = appNavigationItems.filter((item) =>
    hasAllPermissions(data?.permissions ?? [], item.permissions),
  )

  const displayName = data?.user.displayName ?? 'User'
  const workspaceName = data?.tenant.name ?? 'Workspace'
  const initials = getInitials(displayName)

  return (
    <aside
      className={cn(
        'sticky top-0 hidden h-screen shrink-0 border-r border-border bg-surface px-4 py-5 transition-all duration-200 lg:flex lg:flex-col',
        collapsed ? 'w-[92px]' : 'w-[272px]',
      )}
    >
      <div
        className={cn(
          'mb-6 flex items-start gap-3',
          collapsed ? 'justify-center' : 'justify-between',
        )}
      >
        <div className={cn('overflow-hidden', collapsed && 'sr-only')}>
          <div className="label-sm uppercase tracking-[0.18em] text-text-muted">
            Snowball
          </div>
          <div className="heading-lg text-text">CRM Platform</div>
          <div className="mt-1 text-xs text-text-muted">
            Governed operations and controls
          </div>
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

      <nav className="flex flex-1 flex-col gap-5" aria-label="Primary">
        {navigationSections.map((section) => (
          <NavigationSection
            key={section.key}
            collapsed={collapsed}
            label={section.label}
            description={section.description}
            items={visibleItems.filter((item) => item.group === section.key)}
          />
        ))}
      </nav>

      <div className="mt-6 rounded-xl border border-border bg-background/80 p-3">
        <div
          className={cn('flex items-center gap-3', collapsed && 'justify-center')}
          title={collapsed ? `${displayName} · ${workspaceName}` : undefined}
        >
          <div className="flex size-9 shrink-0 items-center justify-center rounded-full bg-muted text-[11px] font-semibold uppercase tracking-[0.08em] text-text">
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
