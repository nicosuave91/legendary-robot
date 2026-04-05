import { expect, test } from '@playwright/test'
import { installAuthenticatedAppMocks } from './support/mock-api'

test('client workspace events tab reads canonical calendar data', async ({ page }) => {
  await installAuthenticatedAppMocks(page)
  await page.goto('/app/clients/client-1/events')

  await expect(page.getByText('Client events')).toBeVisible()
  await expect(page.getByRole('heading', { name: 'Acme Mortgage' })).toBeVisible()
  await expect(page.getByRole('button', { name: 'Open event' }).first()).toBeVisible()
})
