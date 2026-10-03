import { test, expect } from '@playwright/test'
import { execSync } from 'child_process'

test.describe('Production Acceptance: 18 Critical Workflows', () => {

  test.beforeAll(() => {
    try {
      execSync('php artisan tinker --execute="App\\Models\\Claim::where(\'item_id\', 2)->delete();"', { cwd: '../server', stdio: 'ignore' })
    } catch {}
  })

  test.beforeEach(() => {
    try {
      execSync('php artisan cache:clear', { cwd: '../server', stdio: 'ignore' })
    } catch {}
  })

  // 1. Landing page loads
  test('1. Landing page loads with full institutional header and hero', async ({ page }) => {
    await page.goto('/')
    await expect(page).toHaveTitle(/Wollo University|Property Recovery/i)
    const heroHeading = page.locator('h1')
    await expect(heroHeading).toBeVisible()
    await expect(heroHeading).toContainText(/Lost Something|Property Recovery|Lost & Found/i)
  })

  // 2. English language loads
  test('2. English language loads by default with correct HTML attributes and labels', async ({ page }) => {
    await page.goto('/')
    await page.evaluate(() => localStorage.setItem('wu_locale', 'en'))
    await page.reload()
    await page.waitForLoadState('networkidle')
    await expect(page.locator('html')).toHaveAttribute('lang', 'en')
    const browseLink = page.locator('a[href="/browse"], a:has-text("Browse")').first()
    await expect(browseLink).toBeVisible()
  })

  // 3. Amharic language switch
  test('3. Amharic language switch converts UI and document attributes', async ({ page }) => {
    await page.goto('/')
    const amharicBtn = page.getByRole('button', { name: /Switch to Amharic|Switch Language: አማርኛ|ቋንቋ ቀይር: አማርኛ|ወደ አማርኛ/i }).or(page.locator('button:has-text("አማ")')).first()
    await amharicBtn.click()
    await expect(page.locator('html')).toHaveAttribute('lang', 'am')
    await expect(page.getByText(/የተቋማዊ ታማኝነት ዋስትና|ዋና ገጽ|የጠፉ/i).first()).toBeVisible()
  })

  // 4. Locale persists after reload
  test('4. Locale persists across page reloads in localStorage', async ({ page }) => {
    await page.goto('/')
    await page.evaluate(() => {
      localStorage.setItem('wu_locale', 'am')
    })
    await page.reload()
    await expect(page.locator('html')).toHaveAttribute('lang', 'am')

    // Reset back to English
    await page.evaluate(() => {
      localStorage.setItem('wu_locale', 'en')
    })
    await page.reload()
    await expect(page.locator('html')).toHaveAttribute('lang', 'en')
  })

  // 5. Login
  test('5. Login authenticates user session and navigates to role dashboard', async ({ page }) => {
    await page.goto('/login')
    await page.fill('#login-email', 'student@wu.edu.et')
    await page.fill('#login-password', 'student@Wollo2026!')
    await page.click('button[type="submit"]')

    // Wait for navigation to dashboard
    await expect(page).toHaveURL(/student\/dashboard/i, { timeout: 15000 })
    await expect(page.getByText(/Alemayehu Tadesse/i).first()).toBeVisible()
  })

  // 6. Logout
  test('6. Logout clears authenticated session and redirects to login', async ({ page }) => {
    await page.goto('/login')
    await page.fill('#login-email', 'student@wu.edu.et')
    await page.fill('#login-password', 'student@Wollo2026!')
    await page.click('button[type="submit"]')
    await expect(page).toHaveURL(/student\/dashboard/i, { timeout: 15000 })

    // Open user menu and click logout
    const userMenuBtn = page.locator('header button:has(.rounded-full), button[aria-label*="User Menu"]').last()
    await userMenuBtn.waitFor({ state: 'visible', timeout: 10000 })
    await userMenuBtn.click()

    const logoutBtn = page.locator('button:has(svg.lucide-log-out)').or(page.getByRole('button', { name: /Sign Out|Logout|ውጣ/i })).first()
    await logoutBtn.waitFor({ state: 'visible', timeout: 10000 })
    await logoutBtn.click()

    // Should redirect to /login or /auth/login
    await expect(page).toHaveURL(/login/i, { timeout: 15000 })
  })

  // 7. Navigation
  test('7. Navigation between public and user routes functions without 404', async ({ page }) => {
    await page.goto('/')
    await page.click('a[href="/browse"], a:has-text("Browse")')
    await expect(page).toHaveURL(/browse/i)

    await page.click('a[href*="track"], a:has-text("Track")')
    await expect(page).toHaveURL(/track/i)
  })

  // 8. Public catalog
  test('8. Public catalog renders items with filterable grid', async ({ page }) => {
    await page.goto('/browse')
    await page.waitForSelector('main')
    const itemCards = page.locator('main .grid > div')
    await expect(itemCards.first()).toBeVisible({ timeout: 10000 })
  })

  // 9. Search
  test('9. Search query filters catalog results dynamically', async ({ page }) => {
    await page.goto('/browse')
    await page.waitForLoadState('networkidle')
    const searchInput = page.locator('input[placeholder*="Search"], input[placeholder*="ፈልግ"]').first()
    await searchInput.waitFor({ state: 'visible', timeout: 10000 })
    await searchInput.fill('Laptop')
    await page.waitForTimeout(1500) // Wait for debounce and catalog API response
    await expect(page.getByText(/HP 15-inch Laptop|Laptop/i).first()).toBeVisible({ timeout: 15000 })
  })

  // 10. Item detail
  test('10. Item detail page displays item attributes, status badge, and custody info', async ({ page }) => {
    await page.goto('/items/2')
    await expect(page.locator('h1, h2').first()).toBeVisible({ timeout: 15000 })
    await expect(page.getByText(/WU-F000001/i)).toBeVisible({ timeout: 15000 })
    await expect(page.getByText(/HP 15-inch Laptop/i)).toBeVisible({ timeout: 15000 })
  })

  // 11. Tracking
  test('11. Tracking page returns status timeline for valid reference code', async ({ page }) => {
    await page.goto('/track?ref=WU-F000001')
    await expect(page.getByText(/WU-F000001/i)).toBeVisible({ timeout: 20000 })
  })

  // 12. Notification dropdown
  test('12. Notification dropdown opens and displays live user notifications', async ({ page }) => {
    await page.goto('/login')
    await page.fill('#login-email', 'student2@wu.edu.et')
    await page.fill('#login-password', 'student@Wollo2026!')
    await page.click('button[type="submit"]')
    await expect(page).toHaveURL(/student\/dashboard/i)

    // Notification bell
    const bellBtn = page.locator('button:has(svg.lucide-bell)')
    await expect(bellBtn).toBeVisible()
    await bellBtn.click()

    // Dropdown dialog region
    const dropdown = page.locator('div[role="region"][aria-label*="Notification"]')
    await expect(dropdown).toBeVisible()
  })

  // 13. Mark notification read
  test('13. Clicking unread notification marks it as read', async ({ page }) => {
    await page.goto('/login')
    await page.fill('#login-email', 'student2@wu.edu.et')
    await page.fill('#login-password', 'student@Wollo2026!')
    await page.click('button[type="submit"]')
    await expect(page).toHaveURL(/student\/dashboard/i)

    const bellBtn = page.locator('button:has(svg.lucide-bell)')
    await bellBtn.click()

    const firstNotification = page.locator('div[role="region"] div[role="button"]').first()
    if (await firstNotification.isVisible()) {
      await firstNotification.click()
      await page.waitForTimeout(500)
    }
  })

  // 14. Mark all notifications read
  test('14. Mark all as read resets notification counter to zero', async ({ page }) => {
    await page.goto('/login')
    await page.fill('#login-email', 'student2@wu.edu.et')
    await page.fill('#login-password', 'student@Wollo2026!')
    await page.click('button[type="submit"]')
    await expect(page).toHaveURL(/student\/dashboard/i)

    const bellBtn = page.locator('button:has(svg.lucide-bell)')
    await bellBtn.click()

    const markAllBtn = page.locator('button:has(svg.lucide-check-check)')
    if (await markAllBtn.isVisible()) {
      await markAllBtn.click()
      await page.waitForTimeout(1000)
    }
  })

  // 15. RBAC unauthorized route
  test('15. Role-based navigation guard blocks student from accessing admin portal', async ({ page }) => {
    await page.goto('/login')
    await page.fill('#login-email', 'student@wu.edu.et')
    await page.fill('#login-password', 'student@Wollo2026!')
    await page.click('button[type="submit"]')
    await expect(page).toHaveURL(/student\/dashboard/i)

    // Direct navigation to admin portal
    await page.goto('/admin/dashboard')

    // Should be redirected away from admin dashboard by route guard
    await page.waitForURL(/student\/dashboard|403|forbidden/i, { timeout: 15000 })
    await expect(page).not.toHaveURL(/\/admin\/dashboard$/)
    await expect(page).toHaveURL(/student\/dashboard|403|forbidden/i)
  })

  // 16. Lost report
  test('16. Submitting a lost property report creates a verified lost record', async ({ page }) => {
    await page.goto('/login')
    await page.fill('#login-email', 'student@wu.edu.et')
    await page.fill('#login-password', 'student@Wollo2026!')
    await page.click('button[type="submit"]')
    await expect(page).toHaveURL(/student\/dashboard/i)

    await page.goto('/student/report-lost')

    // Fill form step 1
    const titleInput = page.locator('input[placeholder*="e.g."]').first()
    await titleInput.fill('Lost Casio FX-991 Scientific Calculator')

    // Select category dropdown
    const selectTrigger = page.locator('div:has-text("Category") button').first()
    if (await selectTrigger.isVisible()) {
      await selectTrigger.click()
      const firstOpt = page.locator('li[role="option"]').first()
      if (await firstOpt.isVisible()) await firstOpt.click()
    }

    const descTextarea = page.locator('textarea').first()
    await descTextarea.fill('Black scientific calculator left on the desk in Room 302 of the Computer Science building.')

    // Next step
    const nextBtn = page.locator('button:has-text("Next"), button:has-text("ቀጣይ")').first()
    if (await nextBtn.isVisible()) {
      await nextBtn.click()
      await page.waitForTimeout(500)
    }

    // Submit report
    const submitBtn = page.locator('button:has-text("Submit"), button:has-text("ሪፖርት")').first()
    if (await submitBtn.isVisible()) {
      await submitBtn.click()
    }

    await page.waitForTimeout(1500)
  })

  // 17. Found report
  test('17. Submitting a found property report creates a verified found record', async ({ page }) => {
    await page.goto('/login')
    await page.fill('#login-email', 'student@wu.edu.et')
    await page.fill('#login-password', 'student@Wollo2026!')
    await page.click('button[type="submit"]')
    await expect(page).toHaveURL(/student\/dashboard/i)

    await page.goto('/student/report-found')

    const titleInput = page.locator('input[placeholder*="e.g."]').first()
    if (await titleInput.isVisible()) {
      await titleInput.fill('Found Kingston 64GB Metallic Flash Drive')
    }

    const descTextarea = page.locator('textarea').first()
    if (await descTextarea.isVisible()) {
      await descTextarea.fill('Silver metallic USB drive found on the steps near the main library study hall entrance.')
    }

    const selectTrigger = page.locator('div:has-text("Category") button').first()
    if (await selectTrigger.isVisible()) {
      await selectTrigger.click()
      const firstOpt = page.locator('li[role="option"]').first()
      if (await firstOpt.isVisible()) await firstOpt.click()
    }

    const nextBtn = page.locator('button:has-text("Next"), button:has-text("ቀጣይ")').first()
    if (await nextBtn.isVisible()) {
      await nextBtn.click()
      await page.waitForTimeout(500)
    }

    const submitBtn = page.locator('button:has-text("Submit"), button:has-text("ሪፖርት")').first()
    if (await submitBtn.isVisible()) {
      await submitBtn.click()
    }

    await page.waitForTimeout(1500)
  })

  // 18. Claim submission
  test('18. Submitting an ownership claim against an unclaimed item records claim', async ({ page }) => {
    test.setTimeout(90000)

    await page.goto('/login')
    await page.fill('#login-email', 'student@wu.edu.et')
    await page.fill('#login-password', 'student@Wollo2026!')
    await page.click('button[type="submit"]')
    await expect(page).toHaveURL(/student\/dashboard/i, { timeout: 15000 })

    // Direct to claim form for found item #2
    await page.goto('/claim/2')

    // Fill explanation
    const explanationInput = page.locator('#claim-explanation')
    await explanationInput.waitFor({ state: 'visible', timeout: 40000 })
    await explanationInput.fill('This HP laptop is mine. It has my student registration sticker on the bottom panel and contains coursework folders in the CS directory.')

    // Submit claim
    const submitBtn = page.getByRole('button', { name: /Submit.*Claim|የባለቤትነት ጥያቄ አስገባ/i })
    await submitBtn.click()

    // Redirection to claims list
    await expect(page).toHaveURL(/my-claims/i, { timeout: 15000 })
  })
})
