import { ReactNode } from 'react'
import { AppCard, AppCardBody } from '@/components/ui'

export function ShellFrame({ children }: { children: ReactNode }) {
  return (
    <AppCard>
      <AppCardBody>{children}</AppCardBody>
    </AppCard>
  )
}
