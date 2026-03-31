import { expect, test } from '@playwright/test'
import { installAuthenticatedAppMocks } from './support/mock-api'

test('homepage supports selected-day drilldown and event detail actions', async ({ page }) => {
  await installAuthenticatedAppMocks(page)
  await page.goto('/app/dashboard')

  await expect(page.getByRole('heading', { name: 'Homepage' })).toBeVisible()
  await expect(page.getByText('Client review')).toBeVisible()

  await page.getByRole('button', { name: 'Open event' }).click()

  await expect(page.getByText('Event detail')).toBeVisible()
  await expect(page.getByRole('button', { name: 'Completed' })).toBeVisible()
})

test('calendar page supports event drilldown and task status mutation', async ({ page }) => {
  await installAuthenticatedAppMocks(page)
  await page.goto('/app/calendar?date=2026-03-31')

  await expect(page.getByRole('heading', { name: 'Calendar & Tasks' })).toBeVisible()
  await page.getByRole('button', { name: 'Open event' }).click()
  await expect(page.getByText('Required tasks')).toBeVisible()

  await page.getByRole('button', { name: 'Completed' }).click()
})
