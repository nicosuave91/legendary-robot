export type ThemeContract = {
  brand: {
    primary: string
    secondary: string
    tertiary: string
  }
  semantic: {
    success: string
    warning: string
    danger: string
    info: string
  }
  surface: {
    background: string
    card: string
    muted: string
  }
  border: {
    default: string
    ring: string
  }
  typography: {
    displayFont: string
    bodyFont: string
  }
}
