import { useEffect } from 'react'
import { useForm } from 'react-hook-form'
import { z } from 'zod'
import { zodResolver } from '@hookform/resolvers/zod'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useNavigate } from 'react-router-dom'
import { AppBadge, AppButton, AppCard, AppCardBody, AppCardHeader, AppInput, AppSelectionTile } from '@/components/ui'
import { onboardingApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import { useAuth } from '@/lib/auth/auth-hooks'
import { useToast } from '@/components/shell/toast-host'

const profileSchema = z.object({
  firstName: z.string().min(1),
  lastName: z.string().min(1),
  phone: z.string().min(7),
  birthday: z.string().optional().nullable(),
  addressLine1: z.string().min(1),
  addressLine2: z.string().optional(),
  city: z.string().min(1),
  stateCode: z.string().min(2).max(2),
  postalCode: z.string().min(3)
})

type ProfileValues = z.infer<typeof profileSchema>

export function OnboardingPage() {
  const navigate = useNavigate()
  const queryClient = useQueryClient()
  const { data: authData, refresh } = useAuth()
  const { notify } = useToast()

  const onboardingQuery = useQuery({
    queryKey: queryKeys.onboarding.state(),
    queryFn: onboardingApi.state
  })

  const profileForm = useForm<ProfileValues>({
    resolver: zodResolver(profileSchema),
    values: {
      firstName: onboardingQuery.data?.data.profile.firstName ?? '',
      lastName: onboardingQuery.data?.data.profile.lastName ?? '',
      phone: onboardingQuery.data?.data.profile.phone ?? '',
      birthday: onboardingQuery.data?.data.profile.birthday ?? '',
      addressLine1: onboardingQuery.data?.data.profile.addressLine1 ?? '',
      addressLine2: onboardingQuery.data?.data.profile.addressLine2 ?? '',
      city: onboardingQuery.data?.data.profile.city ?? '',
      stateCode: onboardingQuery.data?.data.profile.stateCode ?? '',
      postalCode: onboardingQuery.data?.data.profile.postalCode ?? ''
    }
  })

  const confirmProfileMutation = useMutation({
    mutationFn: onboardingApi.confirmProfile,
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: queryKeys.onboarding.state() })
      await refresh()
      notify({
        title: 'Profile confirmed',
        description: 'Onboarding can now move to industry selection.',
        tone: 'success'
      })
    }
  })

  const selectIndustryMutation = useMutation({
    mutationFn: onboardingApi.selectIndustry,
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: queryKeys.onboarding.state() })
      await refresh()
      notify({
        title: 'Industry selected',
        description: 'Your onboarding state advanced to completion review.',
        tone: 'success'
      })
    }
  })

  const completeMutation = useMutation({
    mutationFn: onboardingApi.complete,
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: queryKeys.onboarding.state() })
      const context = await refresh()
      notify({
        title: 'Onboarding complete',
        description: 'You now have access to the protected application shell.',
        tone: 'success'
      })
      navigate(context?.landingRoute ?? '/app/dashboard', { replace: true })
    }
  })

  useEffect(() => {
    if (authData?.onboardingState === 'not_applicable' || authData?.onboardingState === 'completed') {
      navigate(authData.landingRoute, { replace: true })
    }
  }, [authData, navigate])

  if (onboardingQuery.isLoading) {
    return (
      <div className="mx-auto flex min-h-screen max-w-4xl items-center justify-center px-6 py-10 text-text">
        <div className="body-md text-text-muted">Loading onboarding state…</div>
      </div>
    )
  }

  const onboarding = onboardingQuery.data?.data

  if (!onboarding) {
    return null
  }

  const submitProfile = async (values: ProfileValues) => {
    await confirmProfileMutation.mutateAsync({
      ...values,
      birthday: values.birthday || undefined,
      addressLine2: values.addressLine2 || undefined
    })
  }

  return (
    <div className="mx-auto flex min-h-screen max-w-5xl items-center px-6 py-10 text-text">
      <div className="grid w-full gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(280px,0.8fr)]">
        <AppCard>
          <AppCardHeader>
            <div className="flex items-start justify-between gap-3">
              <div>
                <div className="display-md">Welcome to Snowball</div>
                <p className="body-md mt-2 text-text-muted">
                  Complete your one-time onboarding so access decisions remain server-enforced and auditable.
                </p>
              </div>
              <AppBadge variant="info">{onboarding.state}</AppBadge>
            </div>
          </AppCardHeader>
          <AppCardBody>
            {onboarding.currentStep === 'profile_confirmation' ? (
              <form className="space-y-4" onSubmit={profileForm.handleSubmit(submitProfile)}>
                <div className="grid gap-4 sm:grid-cols-2">
                  <div className="space-y-2">
                    <label className="label-sm text-text" htmlFor="firstName">First name</label>
                    <AppInput id="firstName" {...profileForm.register('firstName')} />
                  </div>
                  <div className="space-y-2">
                    <label className="label-sm text-text" htmlFor="lastName">Last name</label>
                    <AppInput id="lastName" {...profileForm.register('lastName')} />
                  </div>
                </div>
                <div className="grid gap-4 sm:grid-cols-2">
                  <div className="space-y-2">
                    <label className="label-sm text-text" htmlFor="phone">Phone</label>
                    <AppInput id="phone" {...profileForm.register('phone')} />
                  </div>
                  <div className="space-y-2">
                    <label className="label-sm text-text" htmlFor="birthday">Birthday</label>
                    <AppInput id="birthday" type="date" {...profileForm.register('birthday')} />
                  </div>
                </div>
                <div className="space-y-2">
                  <label className="label-sm text-text" htmlFor="addressLine1">Address line 1</label>
                  <AppInput id="addressLine1" {...profileForm.register('addressLine1')} />
                </div>
                <div className="space-y-2">
                  <label className="label-sm text-text" htmlFor="addressLine2">Address line 2</label>
                  <AppInput id="addressLine2" {...profileForm.register('addressLine2')} />
                </div>
                <div className="grid gap-4 sm:grid-cols-3">
                  <div className="space-y-2 sm:col-span-1">
                    <label className="label-sm text-text" htmlFor="city">City</label>
                    <AppInput id="city" {...profileForm.register('city')} />
                  </div>
                  <div className="space-y-2">
                    <label className="label-sm text-text" htmlFor="stateCode">State</label>
                    <AppInput id="stateCode" maxLength={2} {...profileForm.register('stateCode')} />
                  </div>
                  <div className="space-y-2">
                    <label className="label-sm text-text" htmlFor="postalCode">ZIP</label>
                    <AppInput id="postalCode" {...profileForm.register('postalCode')} />
                  </div>
                </div>
                <AppButton type="submit" disabled={confirmProfileMutation.isPending}>
                  {confirmProfileMutation.isPending ? 'Saving…' : 'Confirm profile'}
                </AppButton>
              </form>
            ) : null}

            {onboarding.currentStep === 'industry_selection' ? (
              <div className="space-y-4">
                <div className="body-md text-text-muted">
                  Select the industry profile that should govern your tenant-scoped capabilities.
                </div>
                <div className="grid gap-4 sm:grid-cols-3">
                  {onboarding.availableIndustries.map((industry) => (
                    <AppSelectionTile
                      key={industry}
                      onClick={() => void selectIndustryMutation.mutateAsync({ industry })}
                      title={industry}
                      description="Assign this runtime profile to your account."
                    />
                  ))}
                </div>
              </div>
            ) : null}

            {onboarding.currentStep === 'completion' ? (
              <div className="space-y-4">
                <div className="heading-md">Review complete</div>
                <div className="body-md text-text-muted">
                  Profile confirmation and industry selection are complete. Finalize onboarding to enter the application shell.
                </div>
                <div className="rounded-lg border border-border bg-muted p-4">
                  <div className="body-sm text-text-muted">Selected industry</div>
                  <div className="heading-md mt-1">{onboarding.selectedIndustry}</div>
                  <div className="body-sm mt-1 text-text-muted">Version {onboarding.selectedIndustryConfigVersion ?? 'pending'}</div>
                </div>
                <AppButton type="button" onClick={() => void completeMutation.mutateAsync()} disabled={completeMutation.isPending || !onboarding.canComplete}>
                  {completeMutation.isPending ? 'Completing…' : 'Complete onboarding'}
                </AppButton>
              </div>
            ) : null}
          </AppCardBody>
        </AppCard>

        <AppCard>
          <AppCardHeader>
            <div className="heading-md">Governance notes</div>
            <div className="body-sm text-text-muted">
              Sprint 3 keeps this flow intentionally constrained so the browser never becomes the source of truth.
            </div>
          </AppCardHeader>
          <AppCardBody>
            <ul className="body-sm list-disc space-y-2 pl-5 text-text-muted">
              <li>Owners bypass onboarding entirely.</li>
              <li>Admin-created users default to onboarding required.</li>
              <li>Profile confirmation and industry selection persist on the server against an active tenant configuration version.</li>
              <li>Completion is tracked and auditable before app-shell access is granted.</li>
            </ul>
          </AppCardBody>
        </AppCard>
      </div>
    </div>
  )
}
