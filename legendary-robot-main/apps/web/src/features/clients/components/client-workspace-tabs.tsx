import { NavLink } from 'react-router-dom'
import type { ClientWorkspaceTab } from '@/lib/api/generated/client'
import { cn } from '@/lib/utils/cn'

export function ClientWorkspaceTabs({ tabs }: { tabs: ClientWorkspaceTab[] }) {
  return (
    <div className="flex flex-wrap gap-2 rounded-lg bg-muted p-1">
      {tabs.map((tab) => (
        <NavLink
          key={tab.key}
          to={tab.href}
          className={({ isActive }) => cn(
            'rounded-md px-3 py-2 text-sm font-medium text-text-muted transition motion-base',
            isActive && 'bg-surface text-text',
            !tab.available && 'opacity-70'
          )}
        >
          {tab.label}
        </NavLink>
      ))}
    </div>
  )
}
