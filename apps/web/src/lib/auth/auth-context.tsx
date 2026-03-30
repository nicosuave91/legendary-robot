import {
  createContext,
  ReactNode,
  useCallback,
  useContext,
  useMemo
} from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { authApi } from '@/lib/api/client'
import { queryKeys } from '@/lib/api/query-keys'
import type { AuthContextEnvelope, AuthContextResponse, SignInRequest } from '@/lib/api/generated/client'

type AuthState = {
  data: AuthContextResponse | null
  isLoading: boolean
  isAuthenticated: boolean
  signIn: (payload: SignInRequest) => Promise<AuthContextEnvelope>
  signOut: () => Promise<void>
}

const AuthContext = createContext<AuthState | undefined>(undefined)

function unwrap(data: AuthContextEnvelope | undefined): AuthContextResponse | null {
  return data?.data ?? null
}

export function AuthProvider({ children }: { children: ReactNode }) {
  const queryClient = useQueryClient()

  const authQuery = useQuery({
    queryKey: queryKeys.auth.me(),
    queryFn: authApi.me
  })

  const signInMutation = useMutation({
    mutationFn: authApi.signIn,
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: queryKeys.auth.me() })
    }
  })

  const signOutMutation = useMutation({
    mutationFn: authApi.signOut,
    onSuccess: async () => {
      await queryClient.removeQueries({ queryKey: queryKeys.auth.me() })
    }
  })

  const signIn = useCallback(async (payload: SignInRequest) => {
    return signInMutation.mutateAsync(payload)
  }, [signInMutation])

  const signOut = useCallback(async () => {
    await signOutMutation.mutateAsync()
  }, [signOutMutation])

  const value = useMemo<AuthState>(() => {
    const data = unwrap(authQuery.data)
    return {
      data,
      isLoading: authQuery.isLoading || signInMutation.isPending,
      isAuthenticated: Boolean(data?.isAuthenticated),
      signIn,
      signOut
    }
  }, [authQuery.data, authQuery.isLoading, signIn, signOut, signInMutation.isPending])

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuthContext() {
  const context = useContext(AuthContext)
  if (!context) {
    throw new Error('useAuthContext must be used within AuthProvider')
  }
  return context
}
