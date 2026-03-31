import { useForm } from 'react-hook-form'
import { z } from 'zod'
import { zodResolver } from '@hookform/resolvers/zod'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { useNavigate } from 'react-router-dom'
import { PageHeader, AppButton, AppCard, AppCardBody, AppCardHeader, AppInput, AppSelect } from '@/components/ui'
import { clientsApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import { useToast } from '@/components/shell/toast-host'

const clientSchema = z.object({
  displayName: z.string().min(2),
  firstName: z.string().optional().or(z.literal('')),
  lastName: z.string().optional().or(z.literal('')),
  companyName: z.string().optional().or(z.literal('')),
  primaryEmail: z.string().email().optional().or(z.literal('')),
  primaryPhone: z.string().optional().or(z.literal('')),
  preferredContactChannel: z.enum(['email', 'sms', 'phone']).optional(),
  dateOfBirth: z.string().optional().or(z.literal('')),
  addressLine1: z.string().optional().or(z.literal('')),
  addressLine2: z.string().optional().or(z.literal('')),
  city: z.string().optional().or(z.literal('')),
  stateCode: z.string().max(2).optional().or(z.literal('')),
  postalCode: z.string().optional().or(z.literal(''))
})

type ClientValues = z.infer<typeof clientSchema>

export function ClientCreatePage() {
  const navigate = useNavigate()
  const queryClient = useQueryClient()
  const { notify } = useToast()
  const form = useForm<ClientValues>({
    resolver: zodResolver(clientSchema),
    defaultValues: {
      preferredContactChannel: 'email'
    }
  })

  const createMutation = useMutation({
    mutationFn: clientsApi.create,
    onSuccess: async (response) => {
      await queryClient.invalidateQueries({ queryKey: queryKeys.clients.all })
      notify({
        title: 'Client created',
        description: 'The new client record is now available in the governed workspace.',
        tone: 'success'
      })
      navigate(`/app/clients/${response.data.id}/overview`)
    }
  })

  return (
    <div className="space-y-6">
      <PageHeader
        title="Create client"
        description="Create a tenant-scoped client master record through the generated API client. Lifecycle starts under server-owned disposition governance."
      />

      <AppCard>
        <AppCardHeader>
          <div className="heading-md">Client profile</div>
          <div className="body-sm text-text-muted">Lifecycle state is no longer entered here. Sprint 7 transitions happen through the disposition engine after creation.</div>
        </AppCardHeader>
        <AppCardBody>
          <form
            className="grid gap-4 lg:grid-cols-2"
            onSubmit={form.handleSubmit(async (values) => {
              await createMutation.mutateAsync(values)
            })}
          >
            <div className="space-y-2 lg:col-span-2">
              <label className="label-sm text-text" htmlFor="displayName">Display name</label>
              <AppInput id="displayName" {...form.register('displayName')} />
            </div>

            <div className="space-y-2">
              <label className="label-sm text-text" htmlFor="firstName">First name</label>
              <AppInput id="firstName" {...form.register('firstName')} />
            </div>
            <div className="space-y-2">
              <label className="label-sm text-text" htmlFor="lastName">Last name</label>
              <AppInput id="lastName" {...form.register('lastName')} />
            </div>
            <div className="space-y-2 lg:col-span-2">
              <label className="label-sm text-text" htmlFor="companyName">Company name</label>
              <AppInput id="companyName" {...form.register('companyName')} />
            </div>
            <div className="space-y-2">
              <label className="label-sm text-text" htmlFor="primaryEmail">Email</label>
              <AppInput id="primaryEmail" type="email" {...form.register('primaryEmail')} />
            </div>
            <div className="space-y-2">
              <label className="label-sm text-text" htmlFor="primaryPhone">Phone</label>
              <AppInput id="primaryPhone" {...form.register('primaryPhone')} />
            </div>
            <div className="space-y-2">
              <label className="label-sm text-text" htmlFor="preferredContactChannel">Preferred contact channel</label>
              <AppSelect id="preferredContactChannel" {...form.register('preferredContactChannel')}>
                <option value="email">Email</option>
                <option value="sms">SMS</option>
                <option value="phone">Phone</option>
              </AppSelect>
            </div>
            <div className="space-y-2">
              <label className="label-sm text-text" htmlFor="dateOfBirth">Date of birth</label>
              <AppInput id="dateOfBirth" type="date" {...form.register('dateOfBirth')} />
            </div>
            <div className="space-y-2 lg:col-span-2">
              <label className="label-sm text-text" htmlFor="addressLine1">Address line 1</label>
              <AppInput id="addressLine1" {...form.register('addressLine1')} />
            </div>
            <div className="space-y-2 lg:col-span-2">
              <label className="label-sm text-text" htmlFor="addressLine2">Address line 2</label>
              <AppInput id="addressLine2" {...form.register('addressLine2')} />
            </div>
            <div className="space-y-2">
              <label className="label-sm text-text" htmlFor="city">City</label>
              <AppInput id="city" {...form.register('city')} />
            </div>
            <div className="space-y-2">
              <label className="label-sm text-text" htmlFor="stateCode">State</label>
              <AppInput id="stateCode" {...form.register('stateCode')} />
            </div>
            <div className="space-y-2">
              <label className="label-sm text-text" htmlFor="postalCode">Postal code</label>
              <AppInput id="postalCode" {...form.register('postalCode')} />
            </div>

            <div className="flex gap-3 lg:col-span-2">
              <AppButton type="submit" disabled={createMutation.isPending}>{createMutation.isPending ? 'Creating…' : 'Create client'}</AppButton>
              <AppButton type="button" variant="secondary" onClick={() => navigate('/app/clients')}>Cancel</AppButton>
            </div>
          </form>
        </AppCardBody>
      </AppCard>
    </div>
  )
}
