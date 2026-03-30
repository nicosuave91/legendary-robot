import { createContext, ReactNode, useContext, useEffect, useMemo, useState } from 'react'
import { defaultTheme } from '@/lib/theme/tokens'
import type { ThemeContract } from '@/lib/theme/theme-contract'

type ThemeContextValue = {
  theme: ThemeContract
  setTheme: (theme: ThemeContract) => void
}

const ThemeContext = createContext<ThemeContextValue | undefined>(undefined)

function applyTheme(theme: ThemeContract) {
  const root = document.documentElement
  root.style.setProperty('--font-display', theme.typography.displayFont)
  root.style.setProperty('--font-body', theme.typography.bodyFont)
  root.style.setProperty('--color-primary', theme.brand.primary)
  root.style.setProperty('--color-secondary', theme.brand.secondary)
  root.style.setProperty('--color-accent', theme.brand.tertiary)
  root.style.setProperty('--color-success', theme.semantic.success)
  root.style.setProperty('--color-warning', theme.semantic.warning)
  root.style.setProperty('--color-danger', theme.semantic.danger)
  root.style.setProperty('--color-info', theme.semantic.info)
  root.style.setProperty('--color-bg', theme.surface.background)
  root.style.setProperty('--color-surface', theme.surface.card)
  root.style.setProperty('--color-surface-muted', theme.surface.muted)
  root.style.setProperty('--color-border', theme.border.default)
  root.style.setProperty('--color-ring', theme.border.ring)
}

export function ThemeProvider({ children }: { children: ReactNode }) {
  const [theme, setTheme] = useState(defaultTheme)

  useEffect(() => {
    applyTheme(theme)
  }, [theme])

  const value = useMemo(() => ({ theme, setTheme }), [theme])

  return <ThemeContext.Provider value={value}>{children}</ThemeContext.Provider>
}

export function useThemeContract() {
  const context = useContext(ThemeContext)
  if (!context) {
    throw new Error('useThemeContract must be used within ThemeProvider')
  }
  return context
}
