import { apiHttpClient } from '@/lib/api/http'
import {
  getAuthMe,
  postAuthSignIn,
  postAuthSignOut,
  type SignInRequest
} from '@/lib/api/generated/client'

export const authApi = {
  me: () => getAuthMe(apiHttpClient),
  signIn: (body: SignInRequest) => postAuthSignIn(apiHttpClient, body),
  signOut: () => postAuthSignOut(apiHttpClient)
}
