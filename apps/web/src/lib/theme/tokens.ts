import type { ThemeContract } from '@/lib/theme/theme-contract'

export const defaultTheme: ThemeContract = {
  brand: {
    primary: '#1d4ed8',
    secondary: '#0f172a',
    tertiary: '#64748b'
  },
  semantic: {
    success: '#16a34a',
    warning: '#d97706',
    danger: '#dc2626',
    info: '#2563eb'
  },
  surface: {
    background: '#f8fafc',
    card: '#ffffff',
    muted: '#eef2f7'
  },
  border: {
    default: '#d9e2ec',
    ring: '#1d4ed8'
  },
  typography: {
    displayFont: '"Archivo Black", system-ui, sans-serif',
    bodyFont: '"Roboto", system-ui, sans-serif'
  }
}
