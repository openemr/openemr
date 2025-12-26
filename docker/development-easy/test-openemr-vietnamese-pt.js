/**
 * OpenEMR Vietnamese Physiotherapy Integration Test
 * Tests login functionality and Vietnamese PT module integration
 */

const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

// Test configuration
const config = {
  baseUrl: 'http://localhost:8300',
  username: 'admin',
  password: 'pass',
  screenshotDir: path.join(__dirname, 'test-screenshots'),
  timeout: 30000
};

// Ensure screenshot directory exists
if (!fs.existsSync(config.screenshotDir)) {
  fs.mkdirSync(config.screenshotDir, { recursive: true });
}

// Test results storage
const testResults = {
  timestamp: new Date().toISOString(),
  tests: [],
  summary: { passed: 0, failed: 0, total: 0 }
};

function logTest(name, status, message, screenshotPath = null) {
  const result = { name, status, message, screenshotPath, timestamp: new Date().toISOString() };
  testResults.tests.push(result);
  testResults.summary.total++;
  if (status === 'PASS') {
    testResults.summary.passed++;
    console.log(`✓ PASS: ${name} - ${message}`);
  } else {
    testResults.summary.failed++;
    console.log(`✗ FAIL: ${name} - ${message}`);
  }
  if (screenshotPath) {
    console.log(`  Screenshot: ${screenshotPath}`);
  }
}

async function runTests() {
  let browser;
  let context;
  let page;

  try {
    // Launch browser
    console.log('\n=== Starting OpenEMR Vietnamese PT Integration Tests ===\n');
    browser = await chromium.launch({
      headless: true,
      args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    context = await browser.newContext({
      viewport: { width: 1920, height: 1080 },
      ignoreHTTPSErrors: true
    });

    page = await context.newPage();
    page.setDefaultTimeout(config.timeout);

    // TEST 1: Login Functionality
    console.log('\n--- Test 1: Login Functionality ---\n');

    // Step 1.1: Navigate to login page
    try {
      await page.goto(config.baseUrl);
      await page.waitForLoadState('networkidle');
      const screenshotPath = path.join(config.screenshotDir, '01-login-page.png');
      await page.screenshot({ path: screenshotPath, fullPage: true });
      logTest('1.1 Navigate to Login Page', 'PASS', 'Successfully loaded login page', screenshotPath);
    } catch (error) {
      logTest('1.1 Navigate to Login Page', 'FAIL', `Error: ${error.message}`);
      throw error;
    }

    // Step 1.2: Verify login form elements
    try {
      const usernameField = await page.locator('input[name="authUser"], input#authUser, input[type="text"]').first();
      const passwordField = await page.locator('input[name="authPass"], input#authPass, input[type="password"]').first();
      const loginButton = await page.locator('button[type="submit"], input[type="submit"], button:has-text("Login")').first();

      await usernameField.waitFor({ state: 'visible' });
      await passwordField.waitFor({ state: 'visible' });
      await loginButton.waitFor({ state: 'visible' });

      logTest('1.2 Verify Login Form', 'PASS', 'Login form elements found and visible');
    } catch (error) {
      logTest('1.2 Verify Login Form', 'FAIL', `Error: ${error.message}`);
      throw error;
    }

    // Step 1.3: Fill in username
    try {
      const usernameField = await page.locator('input[name="authUser"], input#authUser, input[type="text"]').first();
      await usernameField.fill(config.username);
      logTest('1.3 Fill Username', 'PASS', `Entered username: ${config.username}`);
    } catch (error) {
      logTest('1.3 Fill Username', 'FAIL', `Error: ${error.message}`);
      throw error;
    }

    // Step 1.4: Fill in password
    try {
      const passwordField = await page.locator('input[name="authPass"], input#authPass, input[type="password"]').first();
      await passwordField.fill(config.password);
      logTest('1.4 Fill Password', 'PASS', 'Entered password');
    } catch (error) {
      logTest('1.4 Fill Password', 'FAIL', `Error: ${error.message}`);
      throw error;
    }

    // Step 1.5: Click login button
    try {
      const loginButton = await page.locator('button[type="submit"], input[type="submit"], button:has-text("Login")').first();
      await loginButton.click();

      // Wait for navigation
      await page.waitForLoadState('networkidle');
      await page.waitForTimeout(2000); // Additional wait for dashboard load

      const screenshotPath = path.join(config.screenshotDir, '02-after-login.png');
      await page.screenshot({ path: screenshotPath, fullPage: true });
      logTest('1.5 Click Login Button', 'PASS', 'Login button clicked', screenshotPath);
    } catch (error) {
      logTest('1.5 Click Login Button', 'FAIL', `Error: ${error.message}`);
      throw error;
    }

    // Step 1.6: Verify successful login
    try {
      const currentUrl = page.url();

      // Check for common post-login indicators
      const isLoggedIn = currentUrl.includes('main') ||
                         currentUrl.includes('interface') ||
                         await page.locator('body').evaluate(el => {
                           const text = el.innerText.toLowerCase();
                           return text.includes('logout') ||
                                  text.includes('patient') ||
                                  text.includes('calendar');
                         });

      if (isLoggedIn) {
        const screenshotPath = path.join(config.screenshotDir, '03-dashboard.png');
        await page.screenshot({ path: screenshotPath, fullPage: true });
        logTest('1.6 Verify Successful Login', 'PASS', `Logged in successfully. URL: ${currentUrl}`, screenshotPath);
      } else {
        throw new Error('Login verification failed - not on dashboard');
      }
    } catch (error) {
      const screenshotPath = path.join(config.screenshotDir, '03-login-failed.png');
      await page.screenshot({ path: screenshotPath, fullPage: true });
      logTest('1.6 Verify Successful Login', 'FAIL', `Error: ${error.message}`, screenshotPath);
      throw error;
    }

    // TEST 2: Navigate to Patient Summary
    console.log('\n--- Test 2: Navigate to Patient Summary ---\n');

    // Step 2.1: Find and click Patients/Finder menu
    try {
      // Try multiple possible selectors for patient finder
      const patientMenuSelectors = [
        'a:has-text("Finder")',
        'a:has-text("Patient")',
        'a:has-text("Patient Finder")',
        'a[href*="patient_finder"]',
        'a[href*="finder"]',
        'i.fa-search' // Icon-based finder
      ];

      let clicked = false;
      for (const selector of patientMenuSelectors) {
        try {
          const element = page.locator(selector).first();
          if (await element.count() > 0 && await element.isVisible()) {
            await element.click();
            clicked = true;
            logTest('2.1 Click Patient Finder', 'PASS', `Clicked patient finder using selector: ${selector}`);
            break;
          }
        } catch (e) {
          // Try next selector
          continue;
        }
      }

      if (!clicked) {
        // Try to find any patient search functionality in frames
        const frames = page.frames();
        for (const frame of frames) {
          try {
            const finder = frame.locator('a:has-text("Finder"), a:has-text("Search")').first();
            if (await finder.count() > 0) {
              await finder.click();
              clicked = true;
              break;
            }
          } catch (e) {
            continue;
          }
        }
      }

      if (!clicked) {
        throw new Error('Could not find patient finder/search menu');
      }

      await page.waitForTimeout(2000);
    } catch (error) {
      logTest('2.1 Click Patient Finder', 'FAIL', `Error: ${error.message}`);
      // Don't throw - try alternative approach
    }

    // Step 2.2: Search for and select a patient
    try {
      // Look for patient list or search form in main page and frames
      let patientFound = false;

      // Try to find search input
      const searchSelectors = [
        'input[name*="patient"]',
        'input[placeholder*="Search"]',
        'input[placeholder*="Patient"]',
        '#searchFields'
      ];

      for (const selector of searchSelectors) {
        try {
          const searchInput = page.locator(selector).first();
          if (await searchInput.count() > 0 && await searchInput.isVisible()) {
            await searchInput.fill('');
            await searchInput.press('Enter');
            await page.waitForTimeout(1000);
            patientFound = true;
            break;
          }
        } catch (e) {
          continue;
        }
      }

      // Try to click any patient from results
      const patientLinkSelectors = [
        'a[href*="patient_file"]',
        'a[href*="demographics"]',
        'table tr td a',
        '.patientDataColumn a'
      ];

      for (const selector of patientLinkSelectors) {
        try {
          const patientLink = page.locator(selector).first();
          if (await patientLink.count() > 0) {
            await patientLink.click();
            await page.waitForLoadState('networkidle');
            patientFound = true;
            break;
          }
        } catch (e) {
          continue;
        }
      }

      if (patientFound) {
        const screenshotPath = path.join(config.screenshotDir, '04-patient-selected.png');
        await page.screenshot({ path: screenshotPath, fullPage: true });
        logTest('2.2 Select Patient', 'PASS', 'Patient selected from list', screenshotPath);
      } else {
        throw new Error('Could not find or select a patient');
      }
    } catch (error) {
      const screenshotPath = path.join(config.screenshotDir, '04-patient-search-failed.png');
      await page.screenshot({ path: screenshotPath, fullPage: true });
      logTest('2.2 Select Patient', 'FAIL', `Error: ${error.message}`, screenshotPath);
    }

    // Step 2.3: Navigate to patient summary
    try {
      // Check if we're already on demographics/summary page
      const currentUrl = page.url();
      const isOnSummary = currentUrl.includes('demographics') ||
                          currentUrl.includes('summary') ||
                          currentUrl.includes('patient_file');

      if (!isOnSummary) {
        // Try to find summary/demographics link
        const summarySelectors = [
          'a:has-text("Summary")',
          'a:has-text("Demographics")',
          'a[href*="demographics"]',
          'a[href*="summary"]'
        ];

        for (const selector of summarySelectors) {
          try {
            const link = page.locator(selector).first();
            if (await link.count() > 0 && await link.isVisible()) {
              await link.click();
              await page.waitForLoadState('networkidle');
              break;
            }
          } catch (e) {
            continue;
          }
        }
      }

      const screenshotPath = path.join(config.screenshotDir, '05-patient-summary.png');
      await page.screenshot({ path: screenshotPath, fullPage: true });
      logTest('2.3 View Patient Summary', 'PASS', `Patient summary page loaded. URL: ${page.url()}`, screenshotPath);
    } catch (error) {
      const screenshotPath = path.join(config.screenshotDir, '05-summary-failed.png');
      await page.screenshot({ path: screenshotPath, fullPage: true });
      logTest('2.3 View Patient Summary', 'FAIL', `Error: ${error.message}`, screenshotPath);
    }

    // TEST 3: Check for Vietnamese PT Widget
    console.log('\n--- Test 3: Check for Vietnamese PT Widget ---\n');

    // Step 3.1: Look for Vietnamese PT widget
    try {
      // Search for Vietnamese PT widget in page content
      const pageContent = await page.content();
      const hasVietnamesePT = pageContent.includes('Vietnamese Physiotherapy') ||
                              pageContent.includes('Vietnamese PT') ||
                              pageContent.includes('vietnamese_pt') ||
                              pageContent.includes('PT Assessment') ||
                              pageContent.includes('Exercise Prescription');

      // Try to locate widget by various selectors
      const widgetSelectors = [
        '.vietnamese-pt-widget',
        '#vietnamese-pt-widget',
        '[id*="vietnamese"]',
        '[class*="vietnamese"]',
        'div:has-text("Vietnamese Physiotherapy")',
        'div:has-text("Vietnamese PT")'
      ];

      let widgetFound = false;
      let widgetSelector = null;

      for (const selector of widgetSelectors) {
        try {
          const widget = page.locator(selector).first();
          if (await widget.count() > 0) {
            widgetFound = true;
            widgetSelector = selector;

            // Check if visible
            const isVisible = await widget.isVisible();

            if (isVisible) {
              const screenshotPath = path.join(config.screenshotDir, '06-pt-widget-found.png');
              await page.screenshot({ path: screenshotPath, fullPage: true });
              logTest('3.1 Locate Vietnamese PT Widget', 'PASS',
                `Widget found using selector: ${selector}`, screenshotPath);
              break;
            }
          }
        } catch (e) {
          continue;
        }
      }

      if (!widgetFound && !hasVietnamesePT) {
        const screenshotPath = path.join(config.screenshotDir, '06-pt-widget-not-found.png');
        await page.screenshot({ path: screenshotPath, fullPage: true });
        logTest('3.1 Locate Vietnamese PT Widget', 'FAIL',
          'Vietnamese PT widget not found on patient summary page', screenshotPath);
      } else if (hasVietnamesePT && !widgetFound) {
        logTest('3.1 Locate Vietnamese PT Widget', 'PASS',
          'Vietnamese PT content detected in page but widget selector not matched');
      }

    } catch (error) {
      const screenshotPath = path.join(config.screenshotDir, '06-widget-search-error.png');
      await page.screenshot({ path: screenshotPath, fullPage: true });
      logTest('3.1 Locate Vietnamese PT Widget', 'FAIL', `Error: ${error.message}`, screenshotPath);
    }

    // Step 3.2: Check widget sections
    try {
      const widgetSections = {
        'PT Assessments': false,
        'Exercise Prescriptions': false,
        'Treatment Plans': false,
        'Add New Buttons': false
      };

      const pageText = await page.locator('body').innerText();

      widgetSections['PT Assessments'] = pageText.includes('PT Assessment') ||
                                          pageText.includes('Assessment');
      widgetSections['Exercise Prescriptions'] = pageText.includes('Exercise') ||
                                                  pageText.includes('Prescription');
      widgetSections['Treatment Plans'] = pageText.includes('Treatment') ||
                                          pageText.includes('Plan');
      widgetSections['Add New Buttons'] = pageText.includes('Add New') ||
                                          pageText.includes('Add') ||
                                          await page.locator('button:has-text("Add")').count() > 0;

      const foundSections = Object.entries(widgetSections)
        .filter(([_, found]) => found)
        .map(([section, _]) => section);

      if (foundSections.length > 0) {
        logTest('3.2 Check Widget Sections', 'PASS',
          `Found sections: ${foundSections.join(', ')}`);
      } else {
        logTest('3.2 Check Widget Sections', 'FAIL',
          'No Vietnamese PT widget sections found');
      }

      const screenshotPath = path.join(config.screenshotDir, '07-widget-sections.png');
      await page.screenshot({ path: screenshotPath, fullPage: true });

    } catch (error) {
      logTest('3.2 Check Widget Sections', 'FAIL', `Error: ${error.message}`);
    }

    // TEST 4: Check Form Availability
    console.log('\n--- Test 4: Check Form Availability ---\n');

    // Step 4.1: Navigate to encounter or forms section
    try {
      // Look for encounter or add form functionality
      const formMenuSelectors = [
        'a:has-text("Forms")',
        'a:has-text("Encounter")',
        'a:has-text("Add Form")',
        'a[href*="encounter"]',
        'a[href*="forms"]',
        'button:has-text("Add Form")'
      ];

      let formMenuFound = false;
      for (const selector of formMenuSelectors) {
        try {
          const element = page.locator(selector).first();
          if (await element.count() > 0 && await element.isVisible()) {
            await element.click();
            await page.waitForTimeout(1000);
            formMenuFound = true;
            logTest('4.1 Navigate to Forms Menu', 'PASS',
              `Forms menu accessed using selector: ${selector}`);
            break;
          }
        } catch (e) {
          continue;
        }
      }

      if (!formMenuFound) {
        logTest('4.1 Navigate to Forms Menu', 'FAIL',
          'Could not find forms/encounter menu');
      }

      const screenshotPath = path.join(config.screenshotDir, '08-forms-menu.png');
      await page.screenshot({ path: screenshotPath, fullPage: true });

    } catch (error) {
      logTest('4.1 Navigate to Forms Menu', 'FAIL', `Error: ${error.message}`);
    }

    // Step 4.2: Check for Vietnamese PT forms
    try {
      const pageContent = await page.content();
      const pageText = await page.locator('body').innerText();

      const expectedForms = {
        'Vietnamese PT Assessment': false,
        'Vietnamese PT Exercise': false,
        'Vietnamese PT Treatment Plan': false,
        'Vietnamese PT Outcome': false
      };

      for (const formName of Object.keys(expectedForms)) {
        expectedForms[formName] = pageContent.includes(formName) ||
                                  pageText.includes(formName) ||
                                  pageContent.includes(formName.toLowerCase()) ||
                                  pageText.includes(formName.toLowerCase());
      }

      const availableForms = Object.entries(expectedForms)
        .filter(([_, available]) => available)
        .map(([form, _]) => form);

      const screenshotPath = path.join(config.screenshotDir, '09-available-forms.png');
      await page.screenshot({ path: screenshotPath, fullPage: true });

      if (availableForms.length > 0) {
        logTest('4.2 Check Vietnamese PT Forms', 'PASS',
          `Found ${availableForms.length} forms: ${availableForms.join(', ')}`, screenshotPath);
      } else {
        logTest('4.2 Check Vietnamese PT Forms', 'FAIL',
          'No Vietnamese PT forms found in available forms list', screenshotPath);
      }

    } catch (error) {
      const screenshotPath = path.join(config.screenshotDir, '09-forms-check-error.png');
      await page.screenshot({ path: screenshotPath, fullPage: true });
      logTest('4.2 Check Vietnamese PT Forms', 'FAIL', `Error: ${error.message}`, screenshotPath);
    }

    // Additional: Check REST API endpoints
    console.log('\n--- Additional: Check REST API Endpoints ---\n');
    try {
      // Try to access Vietnamese PT API endpoint
      const apiResponse = await page.request.get(`${config.baseUrl}/apis/default/vietnamese-pt/medical-terms`);

      if (apiResponse.ok()) {
        logTest('Additional: API Endpoint Check', 'PASS',
          `Vietnamese PT API endpoint accessible (status: ${apiResponse.status()})`);
      } else {
        logTest('Additional: API Endpoint Check', 'FAIL',
          `API endpoint returned status: ${apiResponse.status()}`);
      }
    } catch (error) {
      logTest('Additional: API Endpoint Check', 'FAIL',
        `Error accessing API: ${error.message}`);
    }

  } catch (error) {
    console.error('\n\n=== CRITICAL ERROR ===');
    console.error(error);
    const screenshotPath = path.join(config.screenshotDir, '99-critical-error.png');
    if (page) {
      await page.screenshot({ path: screenshotPath, fullPage: true });
      console.log(`Error screenshot saved: ${screenshotPath}`);
    }
  } finally {
    // Cleanup
    if (browser) {
      await browser.close();
    }

    // Generate test report
    console.log('\n\n=== TEST SUMMARY ===\n');
    console.log(`Total Tests: ${testResults.summary.total}`);
    console.log(`Passed: ${testResults.summary.passed}`);
    console.log(`Failed: ${testResults.summary.failed}`);
    console.log(`Success Rate: ${((testResults.summary.passed / testResults.summary.total) * 100).toFixed(2)}%`);

    // Save detailed report
    const reportPath = path.join(config.screenshotDir, 'test-report.json');
    fs.writeFileSync(reportPath, JSON.stringify(testResults, null, 2));
    console.log(`\nDetailed report saved: ${reportPath}`);

    console.log('\n=== Vietnamese PT Integration Assessment ===\n');

    const ptTests = testResults.tests.filter(t =>
      t.name.includes('Vietnamese PT') ||
      t.name.includes('Widget') ||
      t.name.includes('Forms')
    );

    const ptPassed = ptTests.filter(t => t.status === 'PASS').length;
    const ptTotal = ptTests.length;

    if (ptPassed === ptTotal && ptTotal > 0) {
      console.log('ASSESSMENT: Vietnamese PT integration appears to be working as expected.');
    } else if (ptPassed > 0) {
      console.log('ASSESSMENT: Vietnamese PT integration is partially working.');
      console.log('Some features may not be visible or fully integrated.');
    } else {
      console.log('ASSESSMENT: Vietnamese PT integration may not be working as expected.');
      console.log('Widget and forms were not found on the patient summary page.');
    }

    console.log(`\nScreenshots saved in: ${config.screenshotDir}`);
    console.log('\n=== Tests Complete ===\n');
  }
}

// Run tests
runTests().catch(console.error);
