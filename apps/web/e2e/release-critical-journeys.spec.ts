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
    await expect(page.getByText('Good morning, Tenant Owner')).toBeVisible()
    await expect(page.getByRole('link', { name: 'View clients' })).toBeVisible()
  })

  test('mocked client workspace surfaces are reachable', async ({ page }) => {
    await openProtectedShell(page, '/app/clients/client-1/overview')

    await expect(page).toHaveURL(/\/app\/clients\/client-1\/overview/)
    await page.goto('/app/clients/client-1/communications')
    await expect(page.getByText('Communications hub')).toBeVisible()

    await page.goto('/app/clients/client-1/events')
    await expect(page.getByText('Client events')).toBeVisible()
    await expect(page.getByRole('button', { name: 'Open event' }).first()).toBeVisible()

    await page.goto('/app/clients/client-1/applications')
    await expect(page.getByText('Applications')).toBeVisible()
  })

  test('operational release surfaces load from the protected shell', async ({ page }) => {
    await openProtectedShell(page, '/app/communications')
    await expect(page.getByRole('heading', { name: 'Communications inbox' })).toBeVisible()

    await page.goto('/app/imports')
    await expect(page.getByText('Import ledger')).toBeVisible()

    await page.goto('/app/calendar')
    await expect(page.getByRole('heading', { name: 'Calendar & Tasks' })).toBeVisible()

    await page.goto('/app/audit')
    await expect(page.getByRole('heading', { name: 'Audit' })).toBeVisible()
  })
})
