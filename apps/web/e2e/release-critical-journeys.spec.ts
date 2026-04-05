import { test, expect, type Page } from '@playwright/test'
import { installAuthenticatedAppMocks } from './support/mock-api'

async function openProtectedShell(page: Page, path = '/app/dashboard') {
  await installAuthenticatedAppMocks(page)
  await page.goto(path)
  await expect(page).toHaveURL(new RegExp(path.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')))
}

test.describe('release critical journeys', () => {
  test('protected shell resolves for authenticated owner context', async ({ page }) => {
    await openProtectedShell(page)

    await expect(page.getByRole('heading', { name: 'Homepage' })).toBeVisible()
    await expect(page.getByText('Clients')).toBeVisible()
  })

  test('mocked client workspace surfaces are reachable', async ({ page }) => {
    await openProtectedShell(page, '/app/clients/client-1/overview')

    await expect(page.getByRole('heading', { name: 'Acme Mortgage' })).toBeVisible()
    await expect(page.getByText('Overview')).toBeVisible()
    await expect(page.getByText('Communications')).toBeVisible()
    await expect(page.getByText('Events')).toBeVisible()
    await expect(page.getByText('Applications')).toBeVisible()

    await page.goto('/app/clients/client-1/events')
    await expect(page.getByText('Client events')).toBeVisible()

    await page.goto('/app/clients/client-1/applications')
    await expect(page.getByText('Applications')).toBeVisible()
  })

  test('operational release surfaces load from the protected shell', async ({ page }) => {
    await openProtectedShell(page, '/app/communications')
    await expect(page.getByRole('heading', { name: 'Communications inbox' })).toBeVisible()

    await page.goto('/app/imports')
    await expect(page.getByText('Import ledger')).toBeVisible()

    await page.goto('/app/calendar')
    await expect(page.getByText('Calendar')).toBeVisible()

    await page.goto('/app/audit')
    await expect(page.getByText('Audit')).toBeVisible()
  })
})
