import type { ComponentProps } from 'react'
import { ButtonBase } from '@/components/ui/button/button.base'

export function AppButton(props: ComponentProps<typeof ButtonBase>) {
  return <ButtonBase {...props} />
}
