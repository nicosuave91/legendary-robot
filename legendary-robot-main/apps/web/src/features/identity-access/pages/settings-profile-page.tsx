import { useEffect } from 'react'
import { useForm } from 'react-hook-form'
import { z } from 'zod'
import { zodResolver } from '@hookform/resolvers/zod'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { PageHeader, AppButton, AppCard, AppCardBody, AppCardHeader, AppInput } from '@/components/ui'
import { profileApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import { useToast } from '@/components/shell/toast-host'

const profileSchema = z.object({
  displayName: z.string().min(2),
  firstName: z.string().min(1),
  lastName: z.string().min(1),
  phone: z.string().min(7),
  birthday: z.string().optional(),
  addressLine1: z.string().min(3),
  addressLine2: z.string().optional(),
  city: z.string().min(2),
  stateCode: z.string().length(2),
  postalCode: z.string().min(5)
})

type ProfileValues = z.infer<typeof profileSchema>

export function SettingsProfilePage() {
  const queryClient = useQueryClient()
  const { notify } = useToast()

  const profileQuery = useQuery({
    queryKey: queryKeys.settings.profile(),
    queryFn: profileApi.get
  })

  const form = useForm<ProfileValues>({
    resolver: zodResolver(profileSchema),
    defaultValues: {
      displayName: '',
      firstName: '',
      lastName: '',
      phone: '',
      birthday: '',
      addressLine1: '',
      addressLine2: '',
      city: '',
      stateCode: '',
      postalCode: ''
    }
  })

  useEffect(() => {
    const profile = profileQuery.data?.data
    if (!profile) return

    form.reset({
      displayName: profile.displayName,
      firstName: profile.firstName,
      lastName: profile.lastName,
      phone: profile.phone,
      birthday: profile.birthday ?? '',
      addressLine1: profile.addressLine1,
      addressLine2: profile.addressLine2,
      city: profile.city,
      stateCode: profile.stateCode,
      postalCode: profile.postalCode
    })
  }, [form, profileQuery.data])

  const updateProfileMutation = useMutation({
    mutationFn: profileApi.update,
    onSuccess: async () => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.settings.profile() }),
        queryClient.invalidateQueries({ queryKey: queryKeys.auth.me() })
      ])

      notify({
        title: 'Profile updated',
        description: 'Profile authority remains server-owned and auditable.',
        tone: 'success'
      })
    }
  })

  const onSubmit = async (values: ProfileValues) => {
    await updateProfileMutation.mutateAsync({
      ...values,
      birthday: values.birthday || undefined,
      addressLine2: values.addressLine2 || undefined
    })
  }

  const profile = profileQuery.data?.data

  return (
    <div className="space-y-6">
      <PageHeader
        title="My Profile"
        description="Personal settings stay server-authoritative through generated clients, form requests, and auditable service flows."
      />

      <div className="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.8fr)]">
        <AppCard>
          <AppCardHeader>
            <div className="heading-md">Editable profile</div>
            <div className="body-sm text-text-muted">
              This continues Sprint 2 profile confirmation into governed settings maintenance.
            </div>
          </AppCardHeader>
          <AppCardBody>
            <form className="space-y-4" onSubmit={form.handleSubmit(onSubmit)}>
              <div className="space-y-2">
                <label className="label-sm text-text" htmlFor="displayName">Display name</label>
                <AppInput id="displayName" {...form.register('displayName')} />
              </div>

              <div className="grid gap-4 sm:grid-cols-2">
                <div className="space-y-2">
                  <label className="label-sm text-text" htmlFor="firstName">First name</label>
                  <AppInput id="firstName" {...form.register('firstName')} />
                </div>
                <div className="space-y-2">
                  <label className="label-sm text-text" htmlFor="lastName">Last name</label>
                  <AppInput id="lastName" {...form.register('lastName')} />
                </div>
              </div>

              <div className="grid gap-4 sm:grid-cols-2">
                <div className="space-y-2">
                  <label className="label-sm text-text" htmlFor="phone">Phone</label>
                  <AppInput id="phone" {...form.register('phone')} />
                </div>
                <div className="space-y-2">
                  <label className="label-sm text-text" htmlFor="birthday">Birthday</label>
                  <AppInput id="birthday" type="date" {...form.register('birthday')} />
                </div>
              </div>

              <div className="space-y-2">
                <label className="label-sm text-text" htmlFor="addressLine1">Address line 1</label>
                <AppInput id="addressLine1" {...form.register('addressLine1')} />
              </div>
              <div className="space-y-2">
                <label className="label-sm text-text" htmlFor="addressLine2">Address line 2</label>
                <AppInput id="addressLine2" {...form.register('addressLine2')} />
              </div>

              <div className="grid gap-4 sm:grid-cols-[minmax(0,1fr)_120px_160px]">
                <div className="space-y-2">
                  <label className="label-sm text-text" htmlFor="city">City</label>
                  <AppInput id="city" {...form.register('city')} />
                </div>
                <div className="space-y-2">
                  <label className="label-sm text-text" htmlFor="stateCode">State</label>
                  <AppInput id="stateCode" maxLength={2} {...form.register('stateCode')} />
                </div>
                <div className="space-y-2">
                  <label className="label-sm text-text" htmlFor="postalCode">Postal code</label>
                  <AppInput id="postalCode" {...form.register('postalCode')} />
                </div>
              </div>

              <AppButton type="submit" disabled={updateProfileMutation.isPending}>
                {updateProfileMutation.isPending ? 'Saving…' : 'Save profile'}
              </AppButton>
            </form>
          </AppCardBody>
        </AppCard>

        <AppCard>
          <AppCardHeader>
            <div className="heading-md">Current server snapshot</div>
            <div className="body-sm text-text-muted">
              Reflects the current persisted profile payload returned by the settings API.
            </div>
          </AppCardHeader>
          <AppCardBody>
            <dl className="grid gap-4 text-sm text-text-muted">
              <div>
                <dt className="label-sm uppercase tracking-[0.12em]">Email</dt>
                <dd className="body-md mt-1 text-text">{profile?.email}</dd>
              </div>
              <div>
                <dt className="label-sm uppercase tracking-[0.12em]">Display name</dt>
                <dd className="body-md mt-1 text-text">{profile?.displayName}</dd>
              </div>
              <div>
                <dt className="label-sm uppercase tracking-[0.12em]">Location</dt>
                <dd className="body-md mt-1 text-text">
                  {[profile?.city, profile?.stateCode, profile?.postalCode].filter(Boolean).join(', ')}
                </dd>
              </div>
            </dl>
          </AppCardBody>
        </AppCard>
      </div>
    </div>
  )
}
