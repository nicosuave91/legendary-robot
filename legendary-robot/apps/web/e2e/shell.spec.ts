import { expect, test } from '@playwright/test'

test('sign-in screen renders sprint 2 auth copy', async ({ page }) => {
  await page.goto('/sign-in')
  await expect(page.getByText('Snowball')).toBeVisible()
  await expect(page.getByText('Secure sign-in with policy-aware routing')).toBeVisible()
  await expect(page.getByLabel('Search scaffold')).toHaveCount(0)
})
