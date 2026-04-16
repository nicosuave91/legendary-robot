import { useState } from 'react'
import { Outlet } from 'react-router-dom'
import { AppSidebar } from '@/components/shell/app-sidebar'
import { AppHeader } from '@/components/shell/app-header'

export function AppShellLayout() {
  const [sidebarCollapsed, setSidebarCollapsed] = useState(false)

  return (
    <div
      className="flex min-h-screen bg-background text-text"
      data-testid="app-shell"
    >
      <AppSidebar
        collapsed={sidebarCollapsed}
        onToggle={() => setSidebarCollapsed((current) => !current)}
      />
      <div className="flex min-h-screen min-w-0 flex-1 flex-col">
        <AppHeader />
        <main className="flex-1 overflow-y-auto bg-muted/20 px-5 py-5 xl:px-6">
          <Outlet />
        </main>
      </div>
    </div>
  )
}
