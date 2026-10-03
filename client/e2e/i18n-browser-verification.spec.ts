import { test, expect } from '@playwright/test'

test.describe('Section 7: I18N Browser Verification Across All Key Pages', () => {
  const pagesToTest = [
    { name: 'landing', path: '/', role: 'guest' },
    { name: 'login', path: '/login', role: 'guest' },
    { name: 'register', path: '/register', role: 'guest' },
    { name: 'dashboard', path: '/student/dashboard', role: 'student' },
    { name: 'notifications', path: '/student/notifications', role: 'student' },
    { name: 'reports', path: '/student/my-items', role: 'student' },
    { name: 'claims', path: '/student/my-claims', role: 'student' },
    { name: 'custody', path: '/staff/custody', role: 'staff' },
    { name: 'returns', path: '/staff/returns', role: 'staff' },
    { name: 'admin-users', path: '/admin/users', role: 'admin' },
    { name: 'settings', path: '/admin/settings', role: 'admin' },
  ]

  const rawKeyRegex = /\b(?:nav|common|home|auth|claims|items|notifications|admin|reports|custody|returns|validation|dashboard)\.[a-zA-Z0-9_-]+\b/g

  for (const pageInfo of pagesToTest) {
    test(`Verify ${pageInfo.name} has no raw translation keys in EN and AM`, async ({ browser }) => {
      test.setTimeout(60000)
      const context = await browser.newContext()
      const page = await context.newPage()

      // Authenticate if needed
      if (pageInfo.role === 'student') {
        await page.goto('/login')
        await page.fill('#login-email', 'student@wu.edu.et')
        await page.fill('#login-password', 'student@Wollo2026!')
        await page.click('button[type="submit"]')
        await expect(page).toHaveURL(/student\/dashboard/i, { timeout: 15000 })
      } else if (pageInfo.role === 'staff') {
        await page.goto('/login')
        await page.fill('#login-email', 'security.dessie@wu.edu.et')
        await page.fill('#login-password', 'Staff@Dessie2026!')
        await page.click('button[type="submit"]')
        await expect(page).toHaveURL(/staff\/dashboard/i, { timeout: 15000 })
      } else if (pageInfo.role === 'admin') {
        await page.goto('/login')
        await page.fill('#login-email', 'admin@wu.edu.et')
        await page.fill('#login-password', 'Admin@Wollo2026!')
        await page.click('button[type="submit"]')
        await expect(page).toHaveURL(/admin\/dashboard/i, { timeout: 15000 })
      }

      // 1. TEST ENGLISH
      await page.goto(pageInfo.path)
      await page.waitForLoadState('networkidle')
      await page.evaluate(() => localStorage.setItem('wu_locale', 'en'))
      await page.reload()
      await page.waitForLoadState('networkidle')

      const enBodyText = await page.innerText('body')
      const enRawKeys = enBodyText.match(rawKeyRegex) || []
      console.log(`[${pageInfo.name} - EN] raw translation keys found:`, enRawKeys)
      expect(enRawKeys).toEqual([])

      // 2. TEST AMHARIC
      await page.evaluate(() => {
        localStorage.setItem('wu_locale', 'am')
      })
      await page.reload()
      await page.waitForLoadState('networkidle')

      const amBodyText = await page.innerText('body')
      const amRawKeys = amBodyText.match(rawKeyRegex) || []
      console.log(`[${pageInfo.name} - AM] raw translation keys found:`, amRawKeys)
      expect(amRawKeys).toEqual([])

      // Check document lang attribute
      const langAttr = await page.getAttribute('html', 'lang')
      expect(langAttr).toBe('am')

      // Check for layout overflow (scrollWidth <= clientWidth)
      const hasHorizontalOverflow = await page.evaluate(() => {
        return document.documentElement.scrollWidth > document.documentElement.clientWidth
      })
      expect(hasHorizontalOverflow).toBe(false)

      await context.close()
    })
  }
})
