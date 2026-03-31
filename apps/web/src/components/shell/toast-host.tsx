import {
  createContext,
  ReactNode,
  useCallback,
  useContext,
  useMemo,
  useState
} from 'react'
import { cn } from '@/lib/utils/cn'

type Toast = {
  id: number
  title: string
  description?: string
  tone?: 'info' | 'success' | 'warning' | 'danger'
}

type ToastContextValue = {
  notify: (toast: Omit<Toast, 'id'>) => void
}

const ToastContext = createContext<ToastContextValue | undefined>(undefined)

export function ToastProvider({ children }: { children: ReactNode }) {
  const [toasts, setToasts] = useState<Toast[]>([])

  const notify = useCallback((toast: Omit<Toast, 'id'>) => {
    const next = { ...toast, id: Date.now() + Math.random() }
    setToasts((current) => [...current, next])
    window.setTimeout(() => {
      setToasts((current) => current.filter((entry) => entry.id !== next.id))
    }, 3_000)
  }, [])

  const value = useMemo(() => ({ notify }), [notify])

  return (
    <ToastContext.Provider value={value}>
      {children}
      <div className="fixed bottom-4 right-4 z-50 space-y-3">
        {toasts.map((toast) => (
          <div
            key={toast.id}
            className={cn(
              'w-80 rounded-lg border border-border bg-surface p-4 shadow-md',
              toast.tone === 'success' && 'border-success/30',
              toast.tone === 'warning' && 'border-warning/30',
              toast.tone === 'danger' && 'border-danger/30',
              toast.tone === 'info' && 'border-info/30'
            )}
          >
            <div className="heading-md">{toast.title}</div>
            {toast.description ? (
              <div className="body-sm mt-1 text-text-muted">{toast.description}</div>
            ) : null}
          </div>
        ))}
      </div>
    </ToastContext.Provider>
  )
}

export function useToast() {
  const context = useContext(ToastContext)
  if (!context) {
    throw new Error('useToast must be used within ToastProvider')
  }
  return context
}
