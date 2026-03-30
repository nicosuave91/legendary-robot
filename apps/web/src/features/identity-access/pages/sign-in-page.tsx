import { useForm } from 'react-hook-form'
import { z } from 'zod'
import { zodResolver } from '@hookform/resolvers/zod'
import { useNavigate } from 'react-router-dom'
import { AppButton, AppCard, AppCardBody, AppCardHeader, AppInput, AppBadge } from '@/components/ui'
import { useAuth } from '@/lib/auth/auth-hooks'
import { useToast } from '@/components/shell/toast-host'

const signInSchema = z.object({
  email: z.string().email(),
  password: z.string().min(8)
})

type SignInValues = z.infer<typeof signInSchema>

export function SignInPage() {
  const navigate = useNavigate()
  const { signIn } = useAuth()
  const { notify } = useToast()
  const form = useForm<SignInValues>({
    resolver: zodResolver(signInSchema),
    defaultValues: {
      email: 'owner@example.com',
      password: 'Password123'
    }
  })

  const onSubmit = async (values: SignInValues) => {
    await signIn(values)
    notify({
      title: 'Session scaffold updated',
      description: 'Auth bootstrap now reloads from the server-owned /auth/me response.',
      tone: 'success'
    })
    navigate('/app/dashboard')
  }

  return (
    <AppCard>
      <AppCardHeader>
        <div className="flex items-start justify-between gap-3">
          <div>
            <div className="display-md">Snowball</div>
            <p className="body-md mt-2 text-text-muted">
              Sprint 1 sign-in scaffold using generated client contracts.
            </p>
          </div>
          <AppBadge variant="info">Auth baseline</AppBadge>
        </div>
      </AppCardHeader>
      <AppCardBody>
        <form className="space-y-4" onSubmit={form.handleSubmit(onSubmit)}>
          <div className="space-y-2">
            <label className="label-sm text-text" htmlFor="email">Email</label>
            <AppInput id="email" type="email" {...form.register('email')} />
            {form.formState.errors.email ? (
              <p className="label-sm text-danger">{form.formState.errors.email.message}</p>
            ) : null}
          </div>

          <div className="space-y-2">
            <label className="label-sm text-text" htmlFor="password">Password</label>
            <AppInput id="password" type="password" {...form.register('password')} />
            {form.formState.errors.password ? (
              <p className="label-sm text-danger">{form.formState.errors.password.message}</p>
            ) : null}
          </div>

          <AppButton className="w-full" type="submit">
            Sign in
          </AppButton>
        </form>
      </AppCardBody>
    </AppCard>
  )
}
