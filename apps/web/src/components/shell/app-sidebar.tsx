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

type NavigationSectionKey = 'operations' | 'administration' | 'governance'

const sectionCopy: Record<
  NavigationSectionKey,
  { title: string; description: string }
> = {
  operations: {
    title: 'Daily operations',
    description: 'Cockpit and production work',
  },
  administration: {
    title: 'Administration',
    description: 'Accounts, profile, and tenant settings',
  },
  governance: {
    title: 'Governance',
    description: 'Rules, workflows, and investigations',
  },
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
  title,
  description,
  items,
}: {
  collapsed: boolean
  title: string
  description: string
  items: typeof appNavigationItems
}) {
  if (items.length === 0) {
    return null
  }

  return (
    <section className="space-y-2.5">
      {!collapsed ? (
        <div className="space-y-0.5 px-2">
          <div className="text-[11px] font-semibold uppercase tracking-[0.2em] text-text-muted">
            {title}
          </div>
          <div className="text-[12px] leading-4 text-text-muted">
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
                  'group flex items-center gap-2.5 rounded-lg border text-sm transition motion-base',
                  collapsed ? 'h-10 justify-center px-0' : 'h-10 px-3',
                  isActive
                    ? 'border-border bg-background text-text shadow-xs'
                    : 'border-transparent text-text-muted hover:border-border/50 hover:bg-background/70 hover:text-text',
                )
              }
              title={collapsed ? item.label : undefined}
            >
              {({ isActive }) => (
                <>
                  <div
                    className={cn(
                      'flex size-7 shrink-0 items-center justify-center rounded-md',
                      isActive ? 'bg-muted text-text' : 'text-current',
                    )}
                  >
                    <Icon size={16} />
                  </div>
                  {!collapsed ? (
                    <span className="truncate font-medium">{item.label}</span>
                  ) : null}
                </>
              )}
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

  const operationalItems = visibleItems.filter((item) => item.group === 'operations')
  const administrativeItems = visibleItems.filter(
    (item) => item.group === 'administration',
  )
  const governanceItems = visibleItems.filter((item) => item.group === 'governance')

  const displayName = data?.user.displayName ?? 'User'
  const workspaceName = data?.tenant.name ?? 'Workspace'
  const initials = getInitials(displayName)

  return (
    <aside
      className={cn(
        'sticky top-0 hidden h-screen shrink-0 border-r border-border bg-surface px-3 py-3 transition-all duration-200 lg:flex lg:flex-col',
        collapsed ? 'w-[88px]' : 'w-[244px]',
      )}
    >
      <div
        className={cn(
          'mb-4 flex items-start gap-3',
          collapsed ? 'justify-center' : 'justify-between',
        )}
      >
        <div className={cn('overflow-hidden', collapsed && 'sr-only')}>
          <div className="text-[11px] font-semibold uppercase tracking-[0.2em] text-text-muted">
            Snowball
          </div>
          <div className="mt-1 text-[15px] font-semibold leading-5 text-text">
            CRM Platform
          </div>
          <div className="mt-1 text-[12px] leading-4 text-text-muted">
            Governed operations and controls
          </div>
        </div>

        <AppButton
          type="button"
          variant="secondary"
          size="sm"
          className="shrink-0"
          onClick={onToggle}
          aria-label={collapsed ? 'Expand navigation' : 'Collapse navigation'}
        >
          {collapsed ? <PanelLeftOpen size={15} /> : <PanelLeftClose size={15} />}
        </AppButton>
      </div>

      <nav className="flex flex-1 flex-col gap-4" aria-label="Primary">
        <NavigationGroup
          collapsed={collapsed}
          title={sectionCopy.operations.title}
          description={sectionCopy.operations.description}
          items={operationalItems}
        />
        <NavigationGroup
          collapsed={collapsed}
          title={sectionCopy.administration.title}
          description={sectionCopy.administration.description}
          items={administrativeItems}
        />
        <NavigationGroup
          collapsed={collapsed}
          title={sectionCopy.governance.title}
          description={sectionCopy.governance.description}
          items={governanceItems}
        />
      </nav>

      <div className="mt-4 border-t border-border/80 pt-3">
        <div
          className={cn(
            'rounded-lg border border-border bg-background/70 px-3 py-2.5',
            collapsed ? 'flex justify-center px-2.5' : 'flex items-center gap-2.5',
          )}
          title={collapsed ? `${displayName} · ${workspaceName}` : undefined}
        >
          <div className="flex size-7 shrink-0 items-center justify-center rounded-full bg-muted text-[11px] font-semibold uppercase tracking-[0.08em] text-text">
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
