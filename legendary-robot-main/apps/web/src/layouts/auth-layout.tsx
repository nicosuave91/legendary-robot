import { Outlet } from 'react-router-dom'

export function AuthLayout() {
  return (
    <div className="flex min-h-screen items-center justify-center bg-background px-6 py-10 text-text">
      <div className="w-full max-w-md">
        <Outlet />
      </div>
    </div>
  )
}
