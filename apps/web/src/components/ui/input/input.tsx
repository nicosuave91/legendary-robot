import type { ComponentProps } from 'react'
import { InputBase } from '@/components/ui/input/input.base'

export function AppInput(props: ComponentProps<typeof InputBase>) {
  return <InputBase {...props} />
}
