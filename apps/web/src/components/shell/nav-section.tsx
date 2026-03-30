import { ReactNode } from 'react'

export function NavSection({
  title,
  children
}: {
  title: string
  children: ReactNode
}) {
  return (
    <section className="space-y-2">
      <div className="label-sm uppercase tracking-[0.12em] text-text-muted">{title}</div>
      <div className="space-y-1">{children}</div>
    </section>
  )
}
