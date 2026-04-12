import { test, expect, type Page } from '@playwright/test'
import { installAuthenticatedAppMocks } from './support/mock-api'

/**
 * This suite is intentionally mock-backed.
 * It proves protected-shell routing, page composition, and client wiring with deterministic fixtures.
 * Live API/runtime proof belongs to seeded PHPUnit feature coverage and release-candidate server checks.
 */
async function openMockedProtectedShell(page: Page, path = '/app/dashboard') {
  await installAuthenticatedAppMocks(page)
  await page.goto(path)
  await expect(page).toHaveURL(new RegExp(path.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')))
}

test.describe('release critical journeys (mocked shell smoke)', () => {
  test('mock-backed protected shell resolves for authenticated owner context', async ({ page }) => {
    await openMockedProtectedShell(page)

    await expect(page.getByRole('heading', { name: 'Homepage' })).toBeVisible()
    await expect(page.getByRole('link', { name: 'View all clients' })).toBeVisible()
  })

  test('mock-backed client workspace surfaces are reachable', async ({ page }) => {
    await openMockedProtectedShell(page, '/app/clients/client-1/overview')

    await expect(page).toHaveURL(/\/app\/clients\/client-1\/overview/)
    await page.goto('/app/clients/client-1/communications')
    await expect(page.getByText('Communications hub')).toBeVisible()

    await page.goto('/app/clients/client-1/events')
    await expect(page.getByText('Client events')).toBeVisible()
    await expect(page.getByRole('button', { name: 'Open event' }).first()).toBeVisible()

    await page.goto('/app/clients/client-1/applications')
    await expect(page.getByRole('link', { name: 'Applications' })).toBeVisible()
  })

  test('mock-backed workflow detail surfaces publish blockers and durable run evidence', async ({ page }) => {
    await openMockedProtectedShell(page, '/app/workflows/workflow-1')

    await expect(page.getByRole('heading', { name: 'Application review follow-up' })).toBeVisible()
    await expect(page.getByText('Draft validation')).toBeVisible()
    await expect(page.getByText('Publish is currently blocked by 2 issues.')).toBeVisible()
    await expect(page.getByText('workflow.step.missing_to')).toBeVisible()
    await expect(page.getByText('Workflow created a client note through the governed client note service.')).toBeVisible()
  })

  test('mock-backed operational release surfaces load from the protected shell', async ({ page }) => {
    await openMockedProtectedShell(page, '/app/communications')
    await expect(page.getByRole('heading', { name: 'Communications inbox' })).toBeVisible()

    await page.goto('/app/imports')
    await expect(page.getByText('Import ledger')).toBeVisible()

    await page.goto('/app/calendar')
    await expect(page.getByRole('heading', { name: 'Calendar & Tasks' })).toBeVisible()

    await page.goto('/app/audit')
    await expect(page.getByRole('heading', { name: 'Audit' })).toBeVisible()
  })
})
