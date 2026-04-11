import { useEffect } from 'react'
import { useForm } from 'react-hook-form'
import { z } from 'zod'
import { zodResolver } from '@hookform/resolvers/zod'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { PageHeader, AppButton, AppCard, AppCardBody, AppCardHeader, AppInput } from '@/components/ui'
import { themeApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import { useToast } from '@/components/shell/toast-host'

const themeSchema = z.object({
  primary: z.string().regex(/^#[0-9A-Fa-f]{6}$/),
  secondary: z.string().regex(/^#[0-9A-Fa-f]{6}$/),
  tertiary: z.string().regex(/^#[0-9A-Fa-f]{6}$/)
})

type ThemeValues = z.infer<typeof themeSchema>

function ColorSwatch({ value }: { value: string }) {
  return <div className="h-10 w-full rounded-md border border-border" style={{ backgroundColor: value }} />
}

export function SettingsThemePage() {
  const queryClient = useQueryClient()
  const { notify } = useToast()

  const themeQuery = useQuery({
    queryKey: queryKeys.settings.theme(),
    queryFn: themeApi.get
  })

  const form = useForm<ThemeValues>({
    resolver: zodResolver(themeSchema),
    defaultValues: {
      primary: '#1d4ed8',
      secondary: '#0f172a',
      tertiary: '#64748b'
    }
  })

  useEffect(() => {
    const theme = themeQuery.data?.data
    if (!theme) return
    form.reset(theme)
  }, [form, themeQuery.data])

  const updateThemeMutation = useMutation({
    mutationFn: themeApi.update,
    onSuccess: async () => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.settings.theme() }),
        queryClient.invalidateQueries({ queryKey: queryKeys.auth.me() })
      ])

      notify({
        title: 'Theme updated',
        description: 'Tenant branding tokens now flow from the centralized theme settings API.',
        tone: 'success'
      })
    }
  })

  const values = form.watch()

  const onSubmit = async (payload: ThemeValues) => {
    await updateThemeMutation.mutateAsync(payload)
  }

  return (
    <div className="space-y-6">
      <PageHeader
        title="Branding tokens"
        description="Update primary, secondary, and tertiary colors without introducing page-level styling drift."
      />

      <div className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(320px,0.8fr)]">
        <AppCard>
          <AppCardHeader>
            <div className="heading-md">Tenant theme settings</div>
            <div className="body-sm text-text-muted">
              These values are written once and consumed through the app theme contract.
            </div>
          </AppCardHeader>
          <AppCardBody>
            <form className="space-y-4" onSubmit={form.handleSubmit(onSubmit)}>
              {(['primary', 'secondary', 'tertiary'] as const).map((field) => (
                <div key={field} className="grid gap-3 sm:grid-cols-[140px_minmax(0,1fr)] sm:items-center">
                  <label className="label-sm text-text capitalize" htmlFor={field}>{field}</label>
                  <AppInput id={field} {...form.register(field)} />
                </div>
              ))}

              <AppButton type="submit" disabled={updateThemeMutation.isPending}>
                {updateThemeMutation.isPending ? 'Saving…' : 'Save branding tokens'}
              </AppButton>
            </form>
          </AppCardBody>
        </AppCard>

        <AppCard>
          <AppCardHeader>
            <div className="heading-md">Live preview</div>
            <div className="body-sm text-text-muted">
              Preview uses the same token values the server returns in auth bootstrap.
            </div>
          </AppCardHeader>
          <AppCardBody>
            <div className="space-y-4">
              <div>
                <div className="label-sm mb-2 uppercase tracking-[0.12em] text-text-muted">Primary</div>
                <ColorSwatch value={values.primary} />
              </div>
              <div>
                <div className="label-sm mb-2 uppercase tracking-[0.12em] text-text-muted">Secondary</div>
                <ColorSwatch value={values.secondary} />
              </div>
              <div>
                <div className="label-sm mb-2 uppercase tracking-[0.12em] text-text-muted">Tertiary</div>
                <ColorSwatch value={values.tertiary} />
              </div>
            </div>
          </AppCardBody>
        </AppCard>
      </div>
    </div>
  )
}
