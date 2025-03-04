# Test Automation with testRigor for OpenEMR

This document provides step-by-step instructions for setting up test automation for OpenEMR using testRigor. It covers creating an account, setting up a test suite, and running tests using the testRigor CLI.

## Table of Contents

- [Creating an Account on testRigor](#creating-an-account-on-testrigor)
- [Running Tests with the CLI](#running-tests-with-the-cli)
- [Additional Resources](#additional-resources)

## Creating an Account on testRigor

1. **Visit the testRigor website:**
   - Go to [testRigor](https://www.testrigor.com/).

2. **Sign up for a new account:**
   - Click on the "Sign Up" button on the top right corner.
   - Choose the "Public Open Source" version.
   - Fill in the required details and follow the instructions to complete the registration.

3. **Verify your email and log in:**
   - Check your email inbox for a verification email from testRigor.
   - Click on the verification link to activate your account.
   - Once your account is activated, log in.

4. **Create a test sute:**
   - After logging into your account, create a test suite.

## Running Tests with the CLI

1. **Install testRigor CLI:**
   - Ensure you have Node.js installed on your machine (version 18 or higher).
   - Install the testRigor CLI globally using npm:
     ```bash
     npm install -g testrigor
     ```

2. **Obtain Required Parameters:**
   - **Test Suite ID:** You can obtain the Test Suite ID in the URL of your test suite. If the URL is `https://app.testrigor.com/test-suites/12345`, then `12345` is your Test Suite ID.
   - **Auth Token:** You can obtain your token from the "CI/CD integration" section on testRigor. Look for "auth-token" and copy the value next to it, which will be in the format `########-####-####-####-############`.

3. **Set Parameters in `run_testrigor_tests.sh`:**
   - Before running the tests, open the `run_testrigor_tests.sh` script file and set the following variables with the parameters you obtained:
     - `TEST_SUITE_ID`: Set this variable to your Test Suite ID.
     - `AUTH_TOKEN`: Set this variable to your auth token.
     - `LOCALHOST_URL`: Set this variable to the URL where OpenEMR is running locally.

4. **Run Tests:**
     ```bash
     ./run_testrigor_tests.sh
     ```

5. **View Test Results:**
   - You can view the results on testRigor by opening the link shown in the terminal.

## Additional Resources

- [testRigor Documentation](https://docs.testrigor.com/)
- [testRigor Command Line Documentation](https://testrigor.com/command-line/)
