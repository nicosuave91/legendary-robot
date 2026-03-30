import { apiHttpClient } from '@/lib/api/http'
import {
  getAuthMe,
  getOnboardingState,
  getSettingsAccounts,
  patchOnboardingIndustrySelection,
  patchOnboardingProfileConfirmation,
  postAuthSignIn,
  postAuthSignOut,
  postOnboardingComplete,
  postSettingsAccounts,
  type CreateAccountRequest,
  type IndustrySelectionRequest,
  type ProfileConfirmationRequest,
  type SignInRequest
} from '@/lib/api/generated/client'

export const authApi = {
  me: () => getAuthMe(apiHttpClient),
  signIn: (body: SignInRequest) => postAuthSignIn(apiHttpClient, body),
  signOut: () => postAuthSignOut(apiHttpClient)
}

export const onboardingApi = {
  state: () => getOnboardingState(apiHttpClient),
  confirmProfile: (body: ProfileConfirmationRequest) =>
    patchOnboardingProfileConfirmation(apiHttpClient, body),
  selectIndustry: (body: IndustrySelectionRequest) =>
    patchOnboardingIndustrySelection(apiHttpClient, body),
  complete: () => postOnboardingComplete(apiHttpClient)
}

export const accountsApi = {
  list: () => getSettingsAccounts(apiHttpClient),
  create: (body: CreateAccountRequest) => postSettingsAccounts(apiHttpClient, body)
}
