type EmptyStateProps = {
  title: string
  description: string
}

export function EmptyState({ title, description }: EmptyStateProps) {
  return (
    <div className="rounded-xl border border-dashed border-border bg-surface px-5 py-7 text-center">
      <div className="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-muted text-text-muted">
        ∅
      </div>
      <h2 className="heading-md text-text">{title}</h2>
      <p className="mx-auto mt-1.5 max-w-md body-sm text-text-muted">
        {description}
      </p>
    </div>
  )
}
