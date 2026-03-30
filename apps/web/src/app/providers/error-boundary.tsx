import { Component, ReactNode } from 'react'
import { EmptyState } from '@/components/ui/empty-state/empty-state'

type Props = { children: ReactNode }
type State = { hasError: boolean }

export class AppErrorBoundary extends Component<Props, State> {
  public state: State = { hasError: false }

  public static getDerivedStateFromError(): State {
    return { hasError: true }
  }

  public override render() {
    if (this.state.hasError) {
      return (
        <div className="min-h-screen bg-background p-8 text-text">
          <EmptyState
            title="Something went wrong"
            description="The shell recovered into a safe fallback state."
          />
        </div>
      )
    }

    return this.props.children
  }
}
