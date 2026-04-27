import { useMemo } from 'react'
import { useForm } from 'react-hook-form'
import { z } from 'zod'
import { zodResolver } from '@hookform/resolvers/zod'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  AppBadge,
  AppButton,
  AppCard,
  AppCardBody,
  AppCardHeader,
  AppInput,
  AppSelect,
  PageCanvas,
  PageGrid,
  PageHeader,
} from '@/components/ui'
import { accountsApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import { useToast } from '@/components/shell/toast-host'

const createAccountSchema = z.object({
  displayName: z.string().min(2),
  email: z.string().email(),
  role: z.enum(['admin', 'user']),
  password: z.string().min(12),
  firstName: z.string().optional(),
  lastName: z.string().optional(),
})

type CreateAccountValues = z.infer<typeof createAccountSchema>

export function SettingsAccountsPage() {
  const queryClient = useQueryClient()
  const { notify } = useToast()

  const accountsQuery = useQuery({
    queryKey: queryKeys.settings.accounts(),
    queryFn: accountsApi.list,
  })

  const createAccountMutation = useMutation({
    mutationFn: accountsApi.create,
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: queryKeys.settings.accounts() })
      notify({
        title: 'Account created',
        description: 'The new user defaults to onboarding required on first sign-in.',
        tone: 'success',
      })
    },
  })

  const updateAccountMutation = useMutation({
    mutationFn: ({
      userId,
      payload,
    }: {
      userId: string
      payload: Parameters<typeof accountsApi.update>[1]
    }) => accountsApi.update(userId, payload),
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: queryKeys.settings.accounts() })
      notify({
        title: 'Account updated',
        description: 'Account administration changes stayed policy-protected and auditable.',
        tone: 'success',
      })
    },
  })

  const decommissionAccountMutation = useMutation({
    mutationFn: accountsApi.decommission,
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: queryKeys.settings.accounts() })
      notify({
        title: 'Account decommissioned',
        description: 'The account was deactivated without removing historical evidence.',
        tone: 'success',
      })
    },
  })

  const form = useForm<CreateAccountValues>({
    resolver: zodResolver(createAccountSchema),
    defaultValues: {
      role: 'user',
    },
  })

  const accounts = useMemo(() => accountsQuery.data?.data ?? [], [accountsQuery.data])
  const activeCount = accounts.filter((account) => account.status === 'active').length
  const onboardingRequiredCount = accounts.filter(
    (account) => account.onboardingState !== 'completed' && account.onboardingState !== 'not_applicable',
  ).length

  const onSubmit = async (values: CreateAccountValues) => {
    await createAccountMutation.mutateAsync(values)
    form.reset({ role: 'user' })
  }

  return (
    <PageCanvas>
      <PageHeader
        variant="settings"
        eyebrow="Administration & configuration"
        title="Accounts"
        description="Manage tenant users, keep onboarding enforced, and preserve auditable account administration in one settings surface."
        status={
          <>
            <AppBadge variant="neutral">{activeCount} active</AppBadge>
            <AppBadge variant="info">{onboardingRequiredCount} still onboarding</AppBadge>
          </>
        }
      />

      <PageGrid layout="settings">
        <AppCard>
          <AppCardHeader>
            <div className="heading-md">Tenant accounts</div>
            <div className="body-sm text-text-muted">
              Role, status, onboarding, and industry context all come from the server-authoritative settings API.
            </div>
          </AppCardHeader>
          <AppCardBody>
            <div className="space-y-3">
              {accounts.map((account) => (
                <div key={account.id} className="rounded-xl border border-border bg-muted/35 p-4">
                  <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                      <div className="heading-md">{account.displayName}</div>
                      <div className="body-sm text-text-muted">{account.email}</div>
                    </div>
                    <div className="flex flex-wrap gap-2">
                      <AppBadge variant={account.status === 'active' ? 'success' : 'neutral'}>
                        {account.status}
                      </AppBadge>
                      <AppBadge variant="neutral">
                        {account.roles[0] ?? 'user'}
                      </AppBadge>
                    </div>
                  </div>

                  <div className="mt-3 grid gap-3 lg:grid-cols-[minmax(0,1fr)_150px_160px_auto]">
                    <AppInput
                      value={account.displayName}
                      onChange={(event) => {
                        const next = event.currentTarget.value
                        queryClient.setQueryData(queryKeys.settings.accounts(), {
                          ...accountsQuery.data!,
                          data: accounts.map((item) =>
                            item.id === account.id ? { ...item, displayName: next } : item,
                          ),
                        })
                      }}
                    />
                    <AppSelect
                      value={account.roles[0] ?? 'user'}
                      onChange={(event) => {
                        const next = event.currentTarget.value
                        queryClient.setQueryData(queryKeys.settings.accounts(), {
                          ...accountsQuery.data!,
                          data: accounts.map((item) =>
                            item.id === account.id ? { ...item, roles: [next] } : item,
                          ),
                        })
                      }}
                    >
                      <option value="user">User</option>
                      <option value="admin">Admin</option>
                    </AppSelect>
                    <AppSelect
                      value={account.status}
                      onChange={(event) => {
                        const next = event.currentTarget.value as 'active' | 'deactivated'
                        queryClient.setQueryData(queryKeys.settings.accounts(), {
                          ...accountsQuery.data!,
                          data: accounts.map((item) =>
                            item.id === account.id ? { ...item, status: next } : item,
                          ),
                        })
                      }}
                    >
                      <option value="active">Active</option>
                      <option value="deactivated">Deactivated</option>
                    </AppSelect>
                    <div className="flex gap-2">
                      <AppButton
                        type="button"
                        size="sm"
                        onClick={() =>
                          void updateAccountMutation.mutateAsync({
                            userId: account.id,
                            payload: {
                              displayName: account.displayName,
                              role: (account.roles[0] as 'admin' | 'user') ?? 'user',
                              status: account.status,
                              firstName: account.firstName,
                              lastName: account.lastName,
                            },
                          })
                        }
                        disabled={updateAccountMutation.isPending}
                      >
                        Save
                      </AppButton>
                      <AppButton
                        type="button"
                        variant="secondary"
                        size="sm"
                        onClick={() => void decommissionAccountMutation.mutateAsync(account.id)}
                        disabled={decommissionAccountMutation.isPending}
                      >
                        Decommission
                      </AppButton>
                    </div>
                  </div>

                  <div className="body-sm mt-3 text-text-muted">
                    Onboarding: {account.onboardingState}
                    {account.selectedIndustry
                      ? ` • ${account.selectedIndustry} (${account.selectedIndustryConfigVersion ?? 'unversioned'})`
                      : ''}
                  </div>
                </div>
              ))}
            </div>
          </AppCardBody>
        </AppCard>

        <div className="space-y-5">
          <AppCard>
            <AppCardHeader>
              <div className="heading-md">Create account</div>
              <div className="body-sm text-text-muted">
                New accounts remain onboarding-required until the first governed sign-in is complete.
              </div>
            </AppCardHeader>
            <AppCardBody>
              <form className="space-y-4" onSubmit={form.handleSubmit(onSubmit)}>
                <div className="space-y-2">
                  <label className="label-sm text-text" htmlFor="displayName">
                    Display name
                  </label>
                  <AppInput id="displayName" {...form.register('displayName')} />
                </div>
                <div className="space-y-2">
                  <label className="label-sm text-text" htmlFor="email">
                    Email
                  </label>
                  <AppInput id="email" type="email" {...form.register('email')} />
                </div>
                <div className="space-y-2">
                  <label className="label-sm text-text" htmlFor="role">
                    Role
                  </label>
                  <AppSelect id="role" {...form.register('role')}>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                  </AppSelect>
                </div>
                <div className="space-y-2">
                  <label className="label-sm text-text" htmlFor="password">
                    Temporary password
                  </label>
                  <AppInput id="password" type="password" {...form.register('password')} />
                </div>
                <div className="grid gap-4 sm:grid-cols-2">
                  <div className="space-y-2">
                    <label className="label-sm text-text" htmlFor="firstName">
                      First name
                    </label>
                    <AppInput id="firstName" {...form.register('firstName')} />
                  </div>
                  <div className="space-y-2">
                    <label className="label-sm text-text" htmlFor="lastName">
                      Last name
                    </label>
                    <AppInput id="lastName" {...form.register('lastName')} />
                  </div>
                </div>

                <AppButton
                  type="submit"
                  className="w-full"
                  disabled={createAccountMutation.isPending}
                >
                  {createAccountMutation.isPending ? 'Creating…' : 'Create account'}
                </AppButton>
              </form>
            </AppCardBody>
          </AppCard>

          <AppCard>
            <AppCardHeader>
              <div className="heading-md">Settings guardrails</div>
            </AppCardHeader>
            <AppCardBody className="space-y-3 body-sm text-text-muted">
              <p>Accounts are tenant-scoped and remain policy protected.</p>
              <p>Decommissioning never removes prior audit evidence.</p>
              <p>Industry assignment and onboarding state remain server-authoritative.</p>
            </AppCardBody>
          </AppCard>
        </div>
      </PageGrid>
    </PageCanvas>
  )
}
