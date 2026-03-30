export type PermissionCode =
  | 'identity-access.auth.read-self'
  | 'identity-access.auth.sign-out'
  | 'settings.profile.read'
  | 'settings.profile.update'
  | 'settings.accounts.read'
  | 'settings.accounts.create'
  | 'onboarding.state.read'
  | 'onboarding.profile.confirm'
  | 'onboarding.industry.select'
  | 'onboarding.complete'

export function hasPermission(permissions: string[], permission: PermissionCode) {
  return permissions.includes(permission)
}

export function hasAllPermissions(permissions: string[], required: PermissionCode[] = []) {
  return required.every((permission) => hasPermission(permissions, permission))
}
