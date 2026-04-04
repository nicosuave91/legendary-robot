import { useState } from 'react'
import { Eye, EyeOff } from 'lucide-react'
import { useForm } from 'react-hook-form'
import { z } from 'zod'
import { zodResolver } from '@hookform/resolvers/zod'
import { useNavigate } from 'react-router-dom'
import { AppButton, AppCard, AppCardBody, AppCardHeader, AppInput } from '@/components/ui'
import { useAuth } from '@/lib/auth/auth-hooks'
import { useToast } from '@/components/shell/toast-host'
import { ApiError } from '@/lib/api/http'

const signInSchema = z.object({
  email: z.string().email(),
  password: z.string().min(8)
})

type SignInValues = z.infer<typeof signInSchema>

export function SignInPage() {
  const navigate = useNavigate()
  const { signIn } = useAuth()
  const { notify } = useToast()
  const [showPassword, setShowPassword] = useState(false)
  const [serverError, setServerError] = useState<string | null>(null)

  const form = useForm<SignInValues>({
    resolver: zodResolver(signInSchema),
    defaultValues: {
      email: 'owner@example.com',
      password: 'Password123!'
    }
  })

  const onSubmit = async (values: SignInValues) => {
    setServerError(null)

    try {
      const response = await signIn(values)
      notify({
        title: 'Signed in',
        description: 'Identity and onboarding routing now come from the server-owned auth context.',
        tone: 'success'
      })
      navigate(response.data.landingRoute, { replace: true })
    } catch (error) {
      const message = error instanceof ApiError ? error.message : 'Unable to sign in.'
      setServerError(message)
      notify({ title: 'Sign-in failed', description: message, tone: 'danger' })
    }
  }

  return (
    <AppCard>
      <AppCardHeader>
        <div className="flex items-start justify-between gap-3">
          <div>
            <div className="display-md">Snowball</div>
            <p className="body-md mt-2 text-text-muted">
              Secure sign-in with policy-aware routing, owner bypass, and onboarding enforcement.
            </p>
          </div>
        </div>
      </AppCardHeader>
      <AppCardBody>
        <form className="space-y-4" onSubmit={form.handleSubmit(onSubmit)}>
          <div className="space-y-2">
            <label className="label-sm text-text" htmlFor="email">Email</label>
            <AppInput id="email" type="email" autoComplete="email" {...form.register('email')} />
            {form.formState.errors.email ? (
              <p className="label-sm text-danger">{form.formState.errors.email.message}</p>
            ) : null}
          </div>

          <div className="space-y-2">
            <label className="label-sm text-text" htmlFor="password">Password</label>
            <div className="relative">
              <AppInput
                id="password"
                type={showPassword ? 'text' : 'password'}
                autoComplete="current-password"
                className="pr-11"
                {...form.register('password')}
              />
              <button
                type="button"
                onClick={() => setShowPassword((current) => !current)}
                className="absolute right-3 top-1/2 -translate-y-1/2 text-text-muted"
                aria-label={showPassword ? 'Hide password' : 'Show password'}
              >
                {showPassword ? <EyeOff size={16} /> : <Eye size={16} />}
              </button>
            </div>
            {form.formState.errors.password ? (
              <p className="label-sm text-danger">{form.formState.errors.password.message}</p>
            ) : null}
          </div>

          {serverError ? <p className="label-sm text-danger">{serverError}</p> : null}

          <AppButton className="w-full" type="submit" disabled={form.formState.isSubmitting}>
            {form.formState.isSubmitting ? 'Signing in…' : 'Sign in'}
          </AppButton>

          <div className="rounded-lg border border-border bg-muted p-3">
            <div className="label-sm uppercase tracking-[0.12em] text-text-muted">Demo accounts</div>
            <div className="body-sm mt-2 text-text-muted">owner@example.com, admin@example.com, user@example.com</div>
          </div>
        </form>
      </AppCardBody>
    </AppCard>
  )
}
