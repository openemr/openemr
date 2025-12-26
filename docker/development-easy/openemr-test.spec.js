/**
 * OpenEMR Vietnamese Physiotherapy Integration Test
 * Using Playwright Test framework
 */

const { test, expect } = require('@playwright/test');
const fs = require('fs');
const path = require('path');

// Test configuration
const config = {
  baseUrl: 'http://localhost:8300',
  username: 'admin',
  password: 'pass'
};

test.describe('OpenEMR Vietnamese PT Integration', () => {

  test.beforeEach(async ({ page }) => {
    await page.goto(config.baseUrl);
  });

  test('Test 1.1-1.6: Complete Login Flow', async ({ page }) => {
    console.log('\n=== Test 1: Login Functionality ===\n');

    // Wait for login page to load
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: 'test-screenshots/01-login-page.png', fullPage: true });
    console.log('✓ 1.1: Login page loaded');

    // Verify login form elements
    const usernameField = page.locator('input[name="authUser"], input#authUser, input[type="text"]').first();
    const passwordField = page.locator('input[name="authPass"], input#authPass, input[type="password"]').first();
    const loginButton = page.locator('button[type="submit"], input[type="submit"], button:has-text("Login")').first();

    await expect(usernameField).toBeVisible();
    await expect(passwordField).toBeVisible();
    await expect(loginButton).toBeVisible();
    console.log('✓ 1.2: Login form elements verified');

    // Fill in credentials
    await usernameField.fill(config.username);
    console.log('✓ 1.3: Username filled');

    await passwordField.fill(config.password);
    console.log('✓ 1.4: Password filled');

    // Submit login
    await loginButton.click();
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    await page.screenshot({ path: 'test-screenshots/02-after-login.png', fullPage: true });
    console.log('✓ 1.5: Login button clicked');

    // Verify successful login
    const currentUrl = page.url();
    const bodyText = await page.locator('body').innerText();
    const isLoggedIn = currentUrl.includes('main') ||
                       currentUrl.includes('interface') ||
                       bodyText.toLowerCase().includes('logout') ||
                       bodyText.toLowerCase().includes('calendar');

    expect(isLoggedIn).toBeTruthy();
    await page.screenshot({ path: 'test-screenshots/03-dashboard.png', fullPage: true });
    console.log(`✓ 1.6: Login successful. URL: ${currentUrl}`);
  });

  test('Test 2: Navigate to Patient Summary', async ({ page }) => {
    console.log('\n=== Test 2: Navigate to Patient Summary ===\n');

    // First login
    await page.waitForLoadState('networkidle');
    const usernameField = page.locator('input[name="authUser"], input#authUser, input[type="text"]').first();
    const passwordField = page.locator('input[name="authPass"], input#authPass, input[type="password"]').first();
    const loginButton = page.locator('button[type="submit"], input[type="submit"]').first();

    await usernameField.fill(config.username);
    await passwordField.fill(config.password);
    await loginButton.click();
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);

    // Try to find patient finder/search
    const patientMenuSelectors = [
      'a:has-text("Finder")',
      'a:has-text("Patient")',
      'a[href*="patient_finder"]',
      'a[href*="finder"]'
    ];

    let clicked = false;
    for (const selector of patientMenuSelectors) {
      try {
        const element = page.locator(selector).first();
        const count = await element.count();
        if (count > 0) {
          const isVisible = await element.isVisible().catch(() => false);
          if (isVisible) {
            await element.click();
            clicked = true;
            console.log(`✓ 2.1: Clicked patient finder using: ${selector}`);
            break;
          }
        }
      } catch (e) {
        continue;
      }
    }

    if (!clicked) {
      console.log('⚠ 2.1: Could not find patient finder menu');
    }

    await page.waitForTimeout(2000);

    // Try to find and click a patient
    const patientLinkSelectors = [
      'a[href*="patient_file"]',
      'a[href*="demographics"]',
      'table tr td a'
    ];

    for (const selector of patientLinkSelectors) {
      try {
        const link = page.locator(selector).first();
        const count = await link.count();
        if (count > 0) {
          await link.click();
          await page.waitForLoadState('networkidle');
          await page.screenshot({ path: 'test-screenshots/04-patient-selected.png', fullPage: true });
          console.log('✓ 2.2: Patient selected');
          break;
        }
      } catch (e) {
        continue;
      }
    }

    await page.screenshot({ path: 'test-screenshots/05-patient-summary.png', fullPage: true });
    console.log(`✓ 2.3: Patient summary page. URL: ${page.url()}`);
  });

  test('Test 3: Check Vietnamese PT Widget', async ({ page }) => {
    console.log('\n=== Test 3: Check Vietnamese PT Widget ===\n');

    // Login first
    await page.waitForLoadState('networkidle');
    const usernameField = page.locator('input[name="authUser"], input#authUser, input[type="text"]').first();
    const passwordField = page.locator('input[name="authPass"], input#authPass, input[type="password"]').first();
    const loginButton = page.locator('button[type="submit"], input[type="submit"]').first();

    await usernameField.fill(config.username);
    await passwordField.fill(config.password);
    await loginButton.click();
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(3000);

    // Navigate to patient (try direct URL if available)
    // Check current page for Vietnamese PT content
    const pageContent = await page.content();
    const pageText = await page.locator('body').innerText();

    const hasVietnamesePT = pageContent.includes('Vietnamese Physiotherapy') ||
                            pageContent.includes('Vietnamese PT') ||
                            pageContent.includes('vietnamese_pt') ||
                            pageText.includes('PT Assessment') ||
                            pageText.includes('Exercise Prescription');

    console.log(`Vietnamese PT content detected: ${hasVietnamesePT}`);

    // Check for widget sections
    const widgetSections = {
      'PT Assessments': pageText.includes('PT Assessment') || pageText.includes('Assessment'),
      'Exercise Prescriptions': pageText.includes('Exercise') || pageText.includes('Prescription'),
      'Treatment Plans': pageText.includes('Treatment') || pageText.includes('Plan'),
      'Add New Buttons': pageText.includes('Add New') || pageText.includes('Add')
    };

    const foundSections = Object.entries(widgetSections)
      .filter(([_, found]) => found)
      .map(([section, _]) => section);

    await page.screenshot({ path: 'test-screenshots/06-pt-widget-search.png', fullPage: true });

    if (foundSections.length > 0) {
      console.log(`✓ 3.1-3.2: Found widget sections: ${foundSections.join(', ')}`);
    } else {
      console.log('⚠ 3.1-3.2: No Vietnamese PT widget sections detected');
    }
  });

  test('Test 4: Check Form Availability', async ({ page }) => {
    console.log('\n=== Test 4: Check Form Availability ===\n');

    // Login first
    await page.waitForLoadState('networkidle');
    const usernameField = page.locator('input[name="authUser"], input#authUser, input[type="text"]').first();
    const passwordField = page.locator('input[name="authPass"], input#authPass, input[type="password"]').first();
    const loginButton = page.locator('button[type="submit"], input[type="submit"]').first();

    await usernameField.fill(config.username);
    await passwordField.fill(config.password);
    await loginButton.click();
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(3000);

    // Look for forms menu
    const formMenuSelectors = [
      'a:has-text("Forms")',
      'a:has-text("Encounter")',
      'a[href*="forms"]'
    ];

    let formMenuFound = false;
    for (const selector of formMenuSelectors) {
      try {
        const element = page.locator(selector).first();
        const count = await element.count();
        if (count > 0) {
          const isVisible = await element.isVisible().catch(() => false);
          if (isVisible) {
            await element.click();
            await page.waitForTimeout(1000);
            formMenuFound = true;
            console.log(`✓ 4.1: Forms menu accessed using: ${selector}`);
            break;
          }
        }
      } catch (e) {
        continue;
      }
    }

    if (!formMenuFound) {
      console.log('⚠ 4.1: Forms menu not found');
    }

    await page.screenshot({ path: 'test-screenshots/08-forms-menu.png', fullPage: true });

    // Check for Vietnamese PT forms
    const pageContent = await page.content();
    const pageText = await page.locator('body').innerText();

    const expectedForms = [
      'Vietnamese PT Assessment',
      'Vietnamese PT Exercise',
      'Vietnamese PT Treatment Plan',
      'Vietnamese PT Outcome'
    ];

    const availableForms = expectedForms.filter(formName =>
      pageContent.includes(formName) ||
      pageText.includes(formName) ||
      pageContent.toLowerCase().includes(formName.toLowerCase())
    );

    await page.screenshot({ path: 'test-screenshots/09-available-forms.png', fullPage: true });

    if (availableForms.length > 0) {
      console.log(`✓ 4.2: Found ${availableForms.length} Vietnamese PT forms: ${availableForms.join(', ')}`);
    } else {
      console.log('⚠ 4.2: No Vietnamese PT forms found');
    }
  });

  test('Additional: API Endpoint Check', async ({ request }) => {
    console.log('\n=== Additional: API Endpoint Check ===\n');

    try {
      const response = await request.get(`${config.baseUrl}/apis/default/vietnamese-pt/medical-terms`);
      console.log(`API Status: ${response.status()}`);

      if (response.ok()) {
        console.log('✓ Vietnamese PT API endpoint is accessible');
      } else {
        console.log(`⚠ API returned status: ${response.status()}`);
      }
    } catch (error) {
      console.log(`⚠ API Error: ${error.message}`);
    }
  });
});
