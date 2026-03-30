import { apiHttpClient } from '@/lib/api/http'
import {
  deleteSettingsAccount,
  getAuthMe,
  getClients,
  getClient,
  getDashboardProduction,
  getDashboardSummary,
  getOnboardingState,
  getSettingsAccounts,
  getSettingsIndustryConfigurations,
  getSettingsProfile,
  getSettingsTheme,
  patchClient,
  patchOnboardingIndustrySelection,
  patchOnboardingProfileConfirmation,
  patchSettingsAccount,
  patchSettingsProfile,
  patchSettingsTheme,
  postAuthSignIn,
  postAuthSignOut,
  postClientDocuments,
  postClientNotes,
  postClients,
  postOnboardingComplete,
  postSettingsAccounts,
  postSettingsIndustryConfigurations,
  type CreateAccountRequest,
  type CreateClientNoteRequest,
  type CreateIndustryConfigurationRequest,
  type CreateOrUpdateClientRequest,
  type DashboardProductionEnvelope,
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

export const dashboardApi = {
  summary: () => getDashboardSummary(apiHttpClient),
  production: (queryParams?: Parameters<typeof getDashboardProduction>[1]): Promise<DashboardProductionEnvelope> =>
    getDashboardProduction(apiHttpClient, queryParams)
}

export const clientsApi = {
  list: (queryParams?: Parameters<typeof getClients>[1]) => getClients(apiHttpClient, queryParams),
  create: (body: CreateOrUpdateClientRequest) => postClients(apiHttpClient, body),
  get: (clientId: string) => getClient(apiHttpClient, { clientId }),
  update: (clientId: string, body: CreateOrUpdateClientRequest) => patchClient(apiHttpClient, { clientId }, body),
  createNote: (clientId: string, body: CreateClientNoteRequest) => postClientNotes(apiHttpClient, { clientId }, body),
  uploadDocument: (clientId: string, body: FormData) => postClientDocuments(apiHttpClient, { clientId }, body)
}
