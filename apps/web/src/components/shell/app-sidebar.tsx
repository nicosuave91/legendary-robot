import { PanelLeftClose, PanelLeftOpen } from 'lucide-react'
import { NavLink } from 'react-router-dom'
import { AppBadge, AppButton } from '@/components/ui'
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
        <div className="flex flex-col items-end gap-2">
          <AppButton type="button" variant="secondary" size="sm" onClick={onToggle} aria-label="Toggle sidebar">
            {collapsed ? <PanelLeftOpen size={16} /> : <PanelLeftClose size={16} />}
          </AppButton>
          {!collapsed ? <AppBadge variant="info">Release candidate</AppBadge> : null}
        </div>
      </div>

      <nav className="space-y-2" aria-label="Primary">
        {visibleItems.map((item) => {
          const Icon = item.icon
          return (
            <NavLink
              key={item.routeKey}
              to={item.to}
              className={({ isActive }) =>
                cn(
                  'flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-text-muted transition motion-base hover:bg-muted hover:text-text',
                  collapsed && 'justify-center px-2',
                  isActive && 'bg-muted text-text',
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
