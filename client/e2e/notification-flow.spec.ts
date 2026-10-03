import { test, expect } from '@playwright/test'
import { execSync } from 'child_process'
import path from 'path'
import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const serverDir = path.resolve(__dirname, '../../server')

test.describe('Section 3: Real Notification Browser Workflow', () => {
  test.beforeAll(async () => {
    // Reset DB state: item 2 reporter set to student2 (User B), prior claims and notifications cleared
    try {
      execSync('php scripts/cleanup_notifications.php', { cwd: serverDir })
    } catch (e) {
      console.warn('DB cleanup warning:', e)
    }
  })

  test('User A actions notify User B; User B views, clicks, marks all read, and persists', async ({ browser }) => {
    test.setTimeout(90000)

    // -------------------------------------------------------------
    // STEP 1: USER A (student@wu.edu.et) logs in and submits a claim on Item 2 (reported by student2@wu.edu.et)
    // -------------------------------------------------------------
    const contextA = await browser.newContext()
    const pageA = await contextA.newPage()

    await pageA.goto('/login')
    await pageA.fill('#login-email', 'student@wu.edu.et')
    await pageA.fill('#login-password', 'student@Wollo2026!')
    await pageA.click('button[type="submit"]')
    await expect(pageA).toHaveURL(/student\/dashboard/i, { timeout: 15000 })

    // Navigate to claim form for item 2
    await pageA.goto('/student/claim/2')
    const explanationInput = pageA.locator('#claim-explanation')
    await expect(explanationInput).toBeVisible({ timeout: 15000 })
    await explanationInput.fill('This HP laptop is mine. Serial check verified by student claimant.')

    // Submit claim
    const submitBtn = pageA.getByRole('button', { name: /Submit.*Claim|የባለቤትነት ጥያቄ አስገባ/i })
    await submitBtn.click()
    await expect(pageA).toHaveURL(/my-claims/i, { timeout: 15000 })

    await contextA.close()

    // -------------------------------------------------------------
    // STEP 2: USER B (student2@wu.edu.et) logs in and checks notification
    // -------------------------------------------------------------
    const contextB = await browser.newContext()
    const pageB = await contextB.newPage()

    await pageB.goto('/login')
    await pageB.fill('#login-email', 'student2@wu.edu.et')
    await pageB.fill('#login-password', 'student@Wollo2026!')
    await pageB.click('button[type="submit"]')
    await expect(pageB).toHaveURL(/student\/dashboard/i, { timeout: 15000 })

    // User B checks notification bell
    const bellBtn = pageB.locator('button:has(svg.lucide-bell)')
    await expect(bellBtn).toBeVisible({ timeout: 10000 })

    // Unread badge should be visible (> 0)
    await expect(pageB.locator('button:has(svg.lucide-bell) span.bg-rose-500')).toBeVisible({ timeout: 10000 })

    // Open notification dropdown
    await bellBtn.click()
    const dropdown = pageB.locator('div[role="region"][aria-label*="Notification"]')
    await expect(dropdown).toBeVisible({ timeout: 5000 })

    // Verify real notification exists
    const notificationItems = dropdown.locator('div[role="button"]')
    await expect(notificationItems.first()).toBeVisible({ timeout: 10000 })

    // Check notification content
    const notificationText = await notificationItems.first().textContent()
    console.log('Real notification received by User B (student2):', notificationText)
    expect(notificationText).toBeTruthy()

    // Click notification to mark as read and verify navigation
    await notificationItems.first().click()
    await pageB.waitForTimeout(1000)

    // -------------------------------------------------------------
    // STEP 3: Create multiple notifications for User B and test Mark All Read
    // -------------------------------------------------------------
    execSync('php scripts/create_test_notifications.php', { cwd: serverDir })

    // Re-open bell
    await pageB.goto('/student/dashboard')
    const bellBtn2 = pageB.locator('button:has(svg.lucide-bell)')
    await expect(bellBtn2).toBeVisible({ timeout: 10000 })
    await bellBtn2.click()

    const dropdown2 = pageB.locator('div[role="region"][aria-label*="Notification"]')
    await expect(dropdown2).toBeVisible({ timeout: 10000 })

    // Click Mark All as Read button
    const markAllBtn = dropdown2.locator('button:has(svg.lucide-check-check), button:has-text("Mark all")')
    await expect(markAllBtn).toBeVisible({ timeout: 10000 })
    await markAllBtn.click()
    await pageB.waitForTimeout(1000)

    // Unread count badge (bg-rose-500) should disappear
    await expect(pageB.locator('button:has(svg.lucide-bell) span.bg-rose-500')).not.toBeVisible({ timeout: 5000 })

    // -------------------------------------------------------------
    // STEP 4: Reload page and verify server & UI persistence
    // -------------------------------------------------------------
    await pageB.reload()
    await pageB.waitForLoadState('networkidle')
    await expect(pageB.locator('button:has(svg.lucide-bell) span.bg-rose-500')).not.toBeVisible({ timeout: 5000 })

    // Verify server database state: unread notifications for student2 is 0
    const countOutput = execSync('php scripts/count_unread.php', { cwd: serverDir }).toString()
    console.log('Database count output for student2 after reload:', countOutput.trim())
    expect(countOutput).toContain('UNREAD_COUNT:0')

    await contextB.close()
  })
})
