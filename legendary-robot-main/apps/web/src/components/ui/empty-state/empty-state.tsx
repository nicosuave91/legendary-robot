type EmptyStateProps = {
  title: string
  description: string
}

export function EmptyState({ title, description }: EmptyStateProps) {
  return (
    <div className="rounded-lg border border-dashed border-border bg-surface px-6 py-10 text-center shadow-xs">
      <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-muted text-text-muted">
        ∅
      </div>
      <h2 className="heading-lg text-text">{title}</h2>
      <p className="mx-auto mt-2 max-w-md body-md text-text-muted">{description}</p>
    </div>
  )
}
