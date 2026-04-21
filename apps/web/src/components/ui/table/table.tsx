import type { HTMLAttributes, TdHTMLAttributes, ThHTMLAttributes, TableHTMLAttributes } from 'react'
import { cn } from '@/lib/utils/cn'

type TableDensity = 'compact' | 'default'

export function AppTableShell({
  className,
  ...props
}: HTMLAttributes<HTMLDivElement>) {
  return (
    <div
      className={cn('overflow-hidden rounded-lg border border-border bg-surface shadow-sm', className)}
      {...props}
    />
  )
}

export function AppTable({
  className,
  ...props
}: TableHTMLAttributes<HTMLTableElement>) {
  return (
    <AppTableShell>
      <table
        className={cn('min-w-full divide-y divide-border text-sm', className)}
        {...props}
      />
    </AppTableShell>
  )
}

export function AppTableHeader({ className, ...props }: HTMLAttributes<HTMLTableSectionElement>) {
  return <thead className={cn('bg-muted/60', className)} {...props} />
}

export function AppTableBody({ className, ...props }: HTMLAttributes<HTMLTableSectionElement>) {
  return <tbody className={cn('divide-y divide-border bg-surface', className)} {...props} />
}

export function AppTableRow({
  className,
  ...props
}: HTMLAttributes<HTMLTableRowElement>) {
  return (
    <tr
      className={cn('transition-colors hover:bg-muted/45', className)}
      {...props}
    />
  )
}

export function AppTableHead({
  className,
  density = 'default',
  ...props
}: ThHTMLAttributes<HTMLTableCellElement> & { density?: TableDensity }) {
  return (
    <th
      className={cn(
        'text-left label-sm uppercase tracking-[0.12em] text-text-muted',
        density === 'compact' ? 'px-3 py-2' : 'px-4 py-3',
        className,
      )}
      {...props}
    />
  )
}

export function AppTableCell({
  className,
  density = 'default',
  numeric = false,
  ...props
}: TdHTMLAttributes<HTMLTableCellElement> & { density?: TableDensity; numeric?: boolean }) {
  return (
    <td
      className={cn(
        'align-middle body-sm text-text',
        density === 'compact' ? 'px-3 py-2' : 'px-4 py-3',
        numeric && 'text-right tabular-nums',
        className,
      )}
      {...props}
    />
  )
}

export function AppTableEmptyState({
  colSpan,
  children,
}: {
  colSpan: number
  children: React.ReactNode
}) {
  return (
    <tr>
      <td colSpan={colSpan} className="px-4 py-8 text-center body-sm text-text-muted">
        {children}
      </td>
    </tr>
  )
}

export function AppTablePagination({
  className,
  ...props
}: HTMLAttributes<HTMLDivElement>) {
  return (
    <div
      className={cn('flex items-center justify-between border-t border-border bg-surface px-4 py-3 body-sm text-text-muted', className)}
      {...props}
    />
  )
}
