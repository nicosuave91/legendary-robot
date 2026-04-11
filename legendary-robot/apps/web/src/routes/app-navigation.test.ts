import { describe, expect, it } from 'vitest'
import { appNavigationItems, navigationKeysWithRouteMeta } from '@/routes/app-navigation'
import { routeMeta } from '@/routes/route-meta'

describe('appNavigationItems', () => {
  it('stays aligned with route metadata permissions and nav visibility', () => {
    for (const item of appNavigationItems) {
      const meta = routeMeta[item.routeKey]

      expect(meta.navVisible).toBe(true)
      expect(item.permissions).toEqual(meta.permissions ?? [])
    }
  })

  it('covers every route metadata entry that is marked navVisible', () => {
    const metaKeys = Object.entries(routeMeta)
      .filter(([, meta]) => meta.navVisible)
      .map(([key]) => key)
      .sort()

    expect([...navigationKeysWithRouteMeta()].sort()).toEqual(metaKeys)
  })
})
