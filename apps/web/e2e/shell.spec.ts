import { expect, test } from '@playwright/test'

test('sign-in scaffold renders', async ({ page }) => {
  await page.goto('/sign-in')
  await expect(page.getByText('Snowball')).toBeVisible()
  await expect(page.getByLabel('Search scaffold')).toHaveCount(0)
})
