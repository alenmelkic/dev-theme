import { test, expect } from '@playwright/test';

test('homepage has title and loads React components', async ({ page }) => {
    await page.goto('/');

    // Expect a title "to contain" a substring.
    await expect(page).toHaveTitle(/AntsNet/);

    // Check if React header is rendered
    const header = page.locator('#react-header');
    await expect(header).toBeVisible();

    // Check if navigation exists
    const nav = page.locator('.navbar');
    await expect(nav).toBeVisible();
});
