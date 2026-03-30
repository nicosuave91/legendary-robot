export type PermissionCode =
  | 'identity-access.auth.read-self'
  | 'identity-access.auth.sign-out'
  | 'settings.profile.read'
  | 'settings.profile.update'

export function hasPermission(permissions: string[], permission: PermissionCode) {
  return permissions.includes(permission)
}
