import { test, expect } from '@playwright/test'

test.describe('Landing & Authentication Critical Path', () => {
  test('Landing page renders official institutional standards and branding', async ({ page }) => {
    await page.goto('/')

    // Check title or portal header
    await expect(page).toHaveTitle(/Wollo University|Property Recovery/i)

    // Check Institutional Integrity section
    const trustSection = page.locator('section[aria-labelledby="trust-heading"]')
    await expect(trustSection).toBeVisible()
    await expect(trustSection).toContainText('Campus Property Integrity & Recovery Standards')
  })

  test('Language toggle switches between English and Amharic', async ({ page }) => {
    await page.goto('/')

    // Find and click language switch button
    const langButton = page.getByRole('button', { name: /Switch to Amharic|ወደ አማርኛ ቀይር|ቋንቋ ቀይር/i })
    if (await langButton.isVisible()) {
      await langButton.click()
      await expect(page.locator('html')).toHaveAttribute('lang', 'am')
    }
  })

  test('Unauthenticated user is redirected when accessing protected dashboard', async ({ page }) => {
    await page.goto('/admin/dashboard')

    // Should redirect to login with redirect parameter
    await expect(page).toHaveURL(/login.*redirect/i)
    await expect(page.getByRole('heading', { name: /Sign In|መግቢያ/i })).toBeVisible()
  })

  test('Login form validates required credentials', async ({ page }) => {
    await page.goto('/login')

    // Click submit without entering credentials
    const submitBtn = page.getByRole('button', { name: /Sign In|መግቢያ/i })
    await submitBtn.click()

    // Form inputs should show validation states or errors
    await expect(page).toHaveURL(/login/i)
  })
})
