import { useMemo } from 'react'
import { useForm } from 'react-hook-form'
import { z } from 'zod'
import { zodResolver } from '@hookform/resolvers/zod'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { PageHeader, AppButton, AppCard, AppCardBody, AppCardHeader, AppInput, AppSelect } from '@/components/ui'
import { industryConfigurationsApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import { useToast } from '@/components/shell/toast-host'

const configSchema = z.object({
  industry: z.enum(['Legal', 'Medical', 'Mortgage']),
  status: z.enum(['draft', 'published']),
  capabilities: z.string().min(3),
  notes: z.string().optional()
})

type ConfigValues = z.infer<typeof configSchema>

function formatCapabilities(value: string) {
  return value
    .split(',')
    .map((entry) => entry.trim())
    .filter(Boolean)
}

export function SettingsIndustryConfigurationsPage() {
  const queryClient = useQueryClient()
  const { notify } = useToast()

  const configsQuery = useQuery({
    queryKey: queryKeys.settings.industryConfigurations(),
    queryFn: industryConfigurationsApi.list
  })

  const createConfigMutation = useMutation({
    mutationFn: industryConfigurationsApi.create,
    onSuccess: async () => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.settings.industryConfigurations() }),
        queryClient.invalidateQueries({ queryKey: queryKeys.auth.me() })
      ])

      notify({
        title: 'Configuration version created',
        description: 'Industry behavior now resolves from a versioned tenant configuration.',
        tone: 'success'
      })
    }
  })

  const form = useForm<ConfigValues>({
    resolver: zodResolver(configSchema),
    defaultValues: {
      industry: 'Legal',
      status: 'draft',
      capabilities: 'clients.intake',
      notes: ''
    }
  })

  const configs = useMemo(() => configsQuery.data?.data ?? [], [configsQuery.data])

  const onSubmit = async (values: ConfigValues) => {
    await createConfigMutation.mutateAsync({
      industry: values.industry,
      status: values.status,
      activate: values.status === 'published',
      capabilities: formatCapabilities(values.capabilities),
      notes: values.notes || undefined
    })

    form.reset({
      industry: values.industry,
      status: 'draft',
      capabilities: 'clients.intake',
      notes: ''
    })
  }

  return (
    <div className="space-y-6">
      <PageHeader
        title="Industry configuration versions"
        description="Create auditable tenant-scoped capability versions for Legal, Medical, and Mortgage."
      />

      <div className="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.8fr)]">
        <AppCard>
          <AppCardHeader>
            <div className="heading-md">Version history</div>
            <div className="body-sm text-text-muted">
              Active runtime behavior must trace back to one published tenant configuration version.
            </div>
          </AppCardHeader>
          <AppCardBody>
            <div className="space-y-3">
              {configs.map((config) => (
                <div key={config.id} className="rounded-lg border border-border bg-muted p-4">
                  <div className="flex items-center justify-between gap-3">
                    <div>
                      <div className="heading-md">{config.industry} • {config.version}</div>
                      <div className="body-sm text-text-muted">
                        {config.status}{config.isActive ? ' • active runtime version' : ''}
                      </div>
                    </div>
                    <div className="label-sm uppercase tracking-[0.12em] text-text-muted">
                      {config.activatedAt ? new Date(config.activatedAt).toLocaleDateString() : 'Not active'}
                    </div>
                  </div>
                  <div className="body-sm mt-3 text-text-muted">
                    Capabilities: {config.capabilities.join(', ')}
                  </div>
                  {config.notes ? <div className="body-sm mt-2 text-text-muted">Notes: {config.notes}</div> : null}
                </div>
              ))}
            </div>
          </AppCardBody>
        </AppCard>

        <AppCard>
          <AppCardHeader>
            <div className="heading-md">Create configuration version</div>
            <div className="body-sm text-text-muted">
              Publishing activates the new version for onboarding and runtime capability resolution.
            </div>
          </AppCardHeader>
          <AppCardBody>
            <form className="space-y-4" onSubmit={form.handleSubmit(onSubmit)}>
              <div className="space-y-2">
                <label className="label-sm text-text" htmlFor="industry">Industry</label>
                <AppSelect id="industry" {...form.register('industry')}>
                  <option value="Legal">Legal</option>
                  <option value="Medical">Medical</option>
                  <option value="Mortgage">Mortgage</option>
                </AppSelect>
              </div>

              <div className="space-y-2">
                <label className="label-sm text-text" htmlFor="status">Status</label>
                <AppSelect id="status" {...form.register('status')}>
                  <option value="draft">Draft</option>
                  <option value="published">Published + activate</option>
                </AppSelect>
              </div>

              <div className="space-y-2">
                <label className="label-sm text-text" htmlFor="capabilities">Capabilities</label>
                <AppInput id="capabilities" {...form.register('capabilities')} />
                <div className="body-sm text-text-muted">Comma-separated capability keys, for example clients.intake, communications.email</div>
              </div>

              <div className="space-y-2">
                <label className="label-sm text-text" htmlFor="notes">Notes</label>
                <AppInput id="notes" {...form.register('notes')} />
              </div>

              <AppButton type="submit" disabled={createConfigMutation.isPending}>
                {createConfigMutation.isPending ? 'Creating…' : 'Create version'}
              </AppButton>
            </form>
          </AppCardBody>
        </AppCard>
      </div>
    </div>
  )
}
