import { expect, test } from '@playwright/test'
import { installAuthenticatedAppMocks } from './support/mock-api'

test('homepage renders governed summary content', async ({ page }) => {
  await installAuthenticatedAppMocks(page)
  await page.goto('/app/dashboard')

  await expect(page.getByRole('heading', { name: 'Homepage' })).toBeVisible()
  await expect(page.getByRole('link', { name: 'View clients' })).toBeVisible()
})

test('calendar page loads governed drilldown surface', async ({ page }) => {
  await installAuthenticatedAppMocks(page)
  await page.goto('/app/calendar?date=2026-03-31')

  await expect(page.getByRole('heading', { name: 'Calendar & Tasks' })).toBeVisible()
  await expect(page.getByText('Client review')).toBeVisible()
})