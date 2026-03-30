import { apiHttpClient } from '@/lib/api/http'
import {
  deleteSettingsAccount,
  getAuthMe,
  getOnboardingState,
  getSettingsAccounts,
  getSettingsIndustryConfigurations,
  getSettingsProfile,
  getSettingsTheme,
  patchOnboardingIndustrySelection,
  patchOnboardingProfileConfirmation,
  patchSettingsAccount,
  patchSettingsProfile,
  patchSettingsTheme,
  postAuthSignIn,
  postAuthSignOut,
  postOnboardingComplete,
  postSettingsAccounts,
  postSettingsIndustryConfigurations,
  type CreateAccountRequest,
  type CreateIndustryConfigurationRequest,
  type IndustrySelectionRequest,
  type ProfileConfirmationRequest,
  type SignInRequest,
  type ThemeSummary,
  type UpdateAccountRequest,
  type UpdateProfileRequest
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

export const profileApi = {
  get: () => getSettingsProfile(apiHttpClient),
  update: (body: UpdateProfileRequest) => patchSettingsProfile(apiHttpClient, body)
}

export const themeApi = {
  get: () => getSettingsTheme(apiHttpClient),
  update: (body: ThemeSummary) => patchSettingsTheme(apiHttpClient, body)
}

export const accountsApi = {
  list: () => getSettingsAccounts(apiHttpClient),
  create: (body: CreateAccountRequest) => postSettingsAccounts(apiHttpClient, body),
  update: (userId: string, body: UpdateAccountRequest) => patchSettingsAccount(apiHttpClient, { userId }, body),
  decommission: (userId: string) => deleteSettingsAccount(apiHttpClient, { userId })
}

export const industryConfigurationsApi = {
  list: () => getSettingsIndustryConfigurations(apiHttpClient),
  create: (body: CreateIndustryConfigurationRequest) => postSettingsIndustryConfigurations(apiHttpClient, body)
}
