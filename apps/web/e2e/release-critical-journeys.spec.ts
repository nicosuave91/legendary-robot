import { test, expect, type Page } from '@playwright/test'

async function signInAsOwner(page: Page) {
  await page.goto('/sign-in')
  await expect(page.getByRole('button', { name: 'Sign in' })).toBeVisible()

  await page.locator('#email').fill('owner@example.com')
  await page.locator('#password').fill('Password123!')
  await page.getByRole('button', { name: 'Sign in' }).click()

  await expect(page).toHaveURL(/\/app\/(dashboard|onboarding)/)
}

test.describe('release critical journeys', () => {
  test('owner sign-in resolves into the protected shell', async ({ page }) => {
    await signInAsOwner(page)

    await expect(page).toHaveURL(/\/app\/dashboard/)
    await expect(page.getByText('Homepage')).toBeVisible()
    await expect(page.getByText('Clients')).toBeVisible()
  })

  test('seeded client workspace surfaces are reachable', async ({ page }) => {
    await signInAsOwner(page)

    await page.goto('/app/clients/client-jamie-foster/overview')
    await expect(page.getByText('Jamie Foster')).toBeVisible()
    await expect(page.getByText('Overview')).toBeVisible()
    await expect(page.getByText('Communications')).toBeVisible()
    await expect(page.getByText('Events')).toBeVisible()
    await expect(page.getByText('Applications')).toBeVisible()
    await expect(page.getByText('Disposition')).toBeVisible()

    await page.goto('/app/clients/client-jamie-foster/communications')
    await expect(page.getByText('Communications hub')).toBeVisible()

    await page.goto('/app/clients/client-jamie-foster/events')
    await expect(page.getByText('Client events')).toBeVisible()

    await page.goto('/app/clients/client-jamie-foster/applications')
    await expect(page.getByText('Applications')).toBeVisible()
  })

  test('operational release surfaces load from the protected shell', async ({ page }) => {
    await signInAsOwner(page)

    await page.goto('/app/communications')
    await expect(page.getByText('Client communication entry points')).toBeVisible()

    await page.goto('/app/imports')
    await expect(page.getByText('Import ledger')).toBeVisible()

    await page.goto('/app/calendar')
    await expect(page.getByText('Calendar')).toBeVisible()

    await page.goto('/app/audit')
    await expect(page.getByText('Audit')).toBeVisible()
  })
})
