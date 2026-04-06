type LoadingSkeletonProps = {
  lines?: number
}

export function LoadingSkeleton({ lines = 3 }: LoadingSkeletonProps) {
  return (
    <div className="space-y-3" aria-busy="true" aria-live="polite">
      {Array.from({ length: lines }).map((_, index) => (
        <div
          key={index}
          className="h-4 animate-pulse rounded bg-muted"
          style={{ width: `${100 - index * 12}%` }}
        />
      ))}
    </div>
  )
}
