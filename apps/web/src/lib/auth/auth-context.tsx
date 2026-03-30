import {
  createContext,
  ReactNode,
  useCallback,
  useContext,
  useEffect,
  useMemo
} from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { authApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import type { AuthContextEnvelope, AuthContextResponse, SignInRequest } from '@/lib/api/generated/client'
import { ApiError } from '@/lib/api/http'
import { useThemeContract } from '@/lib/theme/theme-provider'
import { defaultTheme } from '@/lib/theme/tokens'

type AuthState = {
  data: AuthContextResponse | null
  isLoading: boolean
  isAuthenticated: boolean
  signIn: (payload: SignInRequest) => Promise<AuthContextEnvelope>
  signOut: () => Promise<void>
  refresh: () => Promise<AuthContextResponse | null>
}

const AuthContext = createContext<AuthState | undefined>(undefined)

function unwrap(data: AuthContextEnvelope | undefined): AuthContextResponse | null {
  return data?.data ?? null
}

export function AuthProvider({ children }: { children: ReactNode }) {
  const queryClient = useQueryClient()
  const { setTheme } = useThemeContract()

  const authQuery = useQuery({
    queryKey: queryKeys.auth.me(),
    queryFn: async () => {
      try {
        return await authApi.me()
      } catch (error) {
        if (error instanceof ApiError && error.status === 401) {
          return undefined
        }
        throw error
      }
    }
  })

  const signInMutation = useMutation({
    mutationFn: authApi.signIn,
    onSuccess: async (payload) => {
      queryClient.setQueryData(queryKeys.auth.me(), payload)
      await queryClient.invalidateQueries({ queryKey: queryKeys.onboarding.all })
    }
  })

  const signOutMutation = useMutation({
    mutationFn: authApi.signOut,
    onSuccess: async () => {
      queryClient.removeQueries({ queryKey: queryKeys.auth.me() })
      queryClient.removeQueries({ queryKey: queryKeys.onboarding.all })
      queryClient.removeQueries({ queryKey: queryKeys.settings.accounts() })
    }
  })

  const refresh = useCallback(async () => {
    try {
      const result = await queryClient.fetchQuery({
        queryKey: queryKeys.auth.me(),
        queryFn: authApi.me
      })
      return unwrap(result)
    } catch (error) {
      if (error instanceof ApiError && error.status === 401) {
        queryClient.removeQueries({ queryKey: queryKeys.auth.me() })
        return null
      }
      throw error
    }
  }, [queryClient])

  const signIn = useCallback(async (payload: SignInRequest) => {
    return signInMutation.mutateAsync(payload)
  }, [signInMutation])

  const signOut = useCallback(async () => {
    await signOutMutation.mutateAsync()
  }, [signOutMutation])

  const data = unwrap(authQuery.data)

  useEffect(() => {
    if (!data?.theme) {
      setTheme(defaultTheme)
      return
    }

    setTheme({
      ...defaultTheme,
      brand: {
        primary: data.theme.primary,
        secondary: data.theme.secondary,
        tertiary: data.theme.tertiary
      }
    })
  }, [data?.theme, setTheme])

  const value = useMemo<AuthState>(() => {
    return {
      data,
      isLoading: authQuery.isLoading || signInMutation.isPending,
      isAuthenticated: Boolean(data?.isAuthenticated),
      signIn,
      signOut,
      refresh
    }
  }, [authQuery.isLoading, data, refresh, signIn, signInMutation.isPending, signOut])

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuthContext() {
  const context = useContext(AuthContext)
  if (!context) {
    throw new Error('useAuthContext must be used within AuthProvider')
  }
  return context
}
