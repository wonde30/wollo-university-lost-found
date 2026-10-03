# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: production-acceptance.spec.ts >> Production Acceptance: 18 Critical Workflows >> 18. Submitting an ownership claim against an unclaimed item records claim
- Location: e2e\production-acceptance.spec.ts:294:3

# Error details

```
Test timeout of 90000ms exceeded.
```

```
Error: page.fill: Test timeout of 90000ms exceeded.
Call log:
  - waiting for locator('#login-email')

```

# Test source

```ts
  198 |     await page.fill('#login-password', 'student@Wollo2026!')
  199 |     await page.click('button[type="submit"]')
  200 |     await expect(page).toHaveURL(/student\/dashboard/i)
  201 | 
  202 |     // Direct navigation to admin portal
  203 |     await page.goto('/admin/dashboard')
  204 | 
  205 |     // Should be redirected away from admin dashboard by route guard
  206 |     await page.waitForURL(/student\/dashboard|403|forbidden/i, { timeout: 15000 })
  207 |     await expect(page).not.toHaveURL(/\/admin\/dashboard$/)
  208 |     await expect(page).toHaveURL(/student\/dashboard|403|forbidden/i)
  209 |   })
  210 | 
  211 |   // 16. Lost report
  212 |   test('16. Submitting a lost property report creates a verified lost record', async ({ page }) => {
  213 |     await page.goto('/login')
  214 |     await page.fill('#login-email', 'student@wu.edu.et')
  215 |     await page.fill('#login-password', 'student@Wollo2026!')
  216 |     await page.click('button[type="submit"]')
  217 |     await expect(page).toHaveURL(/student\/dashboard/i)
  218 | 
  219 |     await page.goto('/student/report-lost')
  220 | 
  221 |     // Fill form step 1
  222 |     const titleInput = page.locator('input[placeholder*="e.g."]').first()
  223 |     await titleInput.fill('Lost Casio FX-991 Scientific Calculator')
  224 | 
  225 |     // Select category dropdown
  226 |     const selectTrigger = page.locator('div:has-text("Category") button').first()
  227 |     if (await selectTrigger.isVisible()) {
  228 |       await selectTrigger.click()
  229 |       const firstOpt = page.locator('li[role="option"]').first()
  230 |       if (await firstOpt.isVisible()) await firstOpt.click()
  231 |     }
  232 | 
  233 |     const descTextarea = page.locator('textarea').first()
  234 |     await descTextarea.fill('Black scientific calculator left on the desk in Room 302 of the Computer Science building.')
  235 | 
  236 |     // Next step
  237 |     const nextBtn = page.locator('button:has-text("Next"), button:has-text("ቀጣይ")').first()
  238 |     if (await nextBtn.isVisible()) {
  239 |       await nextBtn.click()
  240 |       await page.waitForTimeout(500)
  241 |     }
  242 | 
  243 |     // Submit report
  244 |     const submitBtn = page.locator('button:has-text("Submit"), button:has-text("ሪፖርት")').first()
  245 |     if (await submitBtn.isVisible()) {
  246 |       await submitBtn.click()
  247 |     }
  248 | 
  249 |     await page.waitForTimeout(1500)
  250 |   })
  251 | 
  252 |   // 17. Found report
  253 |   test('17. Submitting a found property report creates a verified found record', async ({ page }) => {
  254 |     await page.goto('/login')
  255 |     await page.fill('#login-email', 'student@wu.edu.et')
  256 |     await page.fill('#login-password', 'student@Wollo2026!')
  257 |     await page.click('button[type="submit"]')
  258 |     await expect(page).toHaveURL(/student\/dashboard/i)
  259 | 
  260 |     await page.goto('/student/report-found')
  261 | 
  262 |     const titleInput = page.locator('input[placeholder*="e.g."]').first()
  263 |     if (await titleInput.isVisible()) {
  264 |       await titleInput.fill('Found Kingston 64GB Metallic Flash Drive')
  265 |     }
  266 | 
  267 |     const descTextarea = page.locator('textarea').first()
  268 |     if (await descTextarea.isVisible()) {
  269 |       await descTextarea.fill('Silver metallic USB drive found on the steps near the main library study hall entrance.')
  270 |     }
  271 | 
  272 |     const selectTrigger = page.locator('div:has-text("Category") button').first()
  273 |     if (await selectTrigger.isVisible()) {
  274 |       await selectTrigger.click()
  275 |       const firstOpt = page.locator('li[role="option"]').first()
  276 |       if (await firstOpt.isVisible()) await firstOpt.click()
  277 |     }
  278 | 
  279 |     const nextBtn = page.locator('button:has-text("Next"), button:has-text("ቀጣይ")').first()
  280 |     if (await nextBtn.isVisible()) {
  281 |       await nextBtn.click()
  282 |       await page.waitForTimeout(500)
  283 |     }
  284 | 
  285 |     const submitBtn = page.locator('button:has-text("Submit"), button:has-text("ሪፖርት")').first()
  286 |     if (await submitBtn.isVisible()) {
  287 |       await submitBtn.click()
  288 |     }
  289 | 
  290 |     await page.waitForTimeout(1500)
  291 |   })
  292 | 
  293 |   // 18. Claim submission
  294 |   test('18. Submitting an ownership claim against an unclaimed item records claim', async ({ page }) => {
  295 |     test.setTimeout(90000)
  296 | 
  297 |     await page.goto('/login')
> 298 |     await page.fill('#login-email', 'student@wu.edu.et')
      |                ^ Error: page.fill: Test timeout of 90000ms exceeded.
  299 |     await page.fill('#login-password', 'student@Wollo2026!')
  300 |     await page.click('button[type="submit"]')
  301 |     await expect(page).toHaveURL(/student\/dashboard/i, { timeout: 15000 })
  302 | 
  303 |     // Direct to claim form for found item #2
  304 |     await page.goto('/claim/2')
  305 | 
  306 |     // Fill explanation
  307 |     const explanationInput = page.locator('#claim-explanation')
  308 |     await explanationInput.waitFor({ state: 'visible', timeout: 40000 })
  309 |     await explanationInput.fill('This HP laptop is mine. It has my student registration sticker on the bottom panel and contains coursework folders in the CS directory.')
  310 | 
  311 |     // Submit claim
  312 |     const submitBtn = page.getByRole('button', { name: /Submit.*Claim|የባለቤትነት ጥያቄ አስገባ/i })
  313 |     await submitBtn.click()
  314 | 
  315 |     // Redirection to claims list
  316 |     await expect(page).toHaveURL(/my-claims/i, { timeout: 15000 })
  317 |   })
  318 | })
  319 | 
```