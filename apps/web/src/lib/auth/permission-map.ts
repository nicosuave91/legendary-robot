export type PermissionCode =
  | 'identity-access.auth.read-self'
  | 'identity-access.auth.sign-out'
  | 'settings.profile.read'
  | 'settings.profile.update'
  | 'settings.accounts.read'
  | 'settings.accounts.create'
  | 'settings.accounts.update'
  | 'settings.accounts.decommission'
  | 'settings.theme.read'
  | 'settings.theme.update'
  | 'settings.industry-configurations.read'
  | 'settings.industry-configurations.create'
  | 'dashboard.summary.read'
  | 'dashboard.production.read'
  | 'clients.read'
  | 'clients.read.all'
  | 'clients.create'
  | 'clients.update'
  | 'clients.notes.create'
  | 'clients.documents.create'
  | 'onboarding.state.read'
  | 'onboarding.profile.confirm'
  | 'onboarding.industry.select'
  | 'onboarding.complete'

export function hasPermission(permissions: readonly string[], permission: PermissionCode) {
  return permissions.includes(permission)
}

export function hasAllPermissions(permissions: readonly string[], required: readonly PermissionCode[] = []) {
  return required.every((permission) => hasPermission(permissions, permission))
}
