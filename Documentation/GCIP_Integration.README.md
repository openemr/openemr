# Google Cloud Identity Platform (GCIP) Integration Plan

This document outlines the plan for integrating Google Cloud Identity Platform (GCIP) with OpenEMR for authentication.

## Phase 1: Database and Configuration

- [x] **Modify `oauth_clients` table:**
  - [ ] Add `identity_provider` column (VARCHAR(255), default 'local').
  - [ ] Add `google_client_id` column (VARCHAR(255), nullable).
  - [ ] Add `google_client_secret` column (VARCHAR(255), nullable).
  - [ ] Create a new SQL patch file for this change.
- [x] **Add Global Configuration:**
  - [ ] Introduce a new global setting in `library/globals.inc.php` to enable/disable the GCIP integration feature.

## Phase 2: Backend Implementation - OIDC Flow

- [x] **Create new routes in `_rest_routes.inc.php`:**
  - [ ] `/authorize/google`: To initiate the GCIP authentication flow.
  - [ ] `/callback/google`: To handle the callback from GCIP.
- [x] **Modify `src/RestControllers/AuthorizationController.php`:**
  - [x] Create a new method `redirectToGoogle()` to handle the `/authorize/google` route. This method will build the OIDC request and redirect the user to Google.
  - [x] Create a new method `handleGoogleCallback()` to handle the `/callback/google` route. This method will:
    - [x] Exchange the authorization code for an access token and ID token from Google.
    - [x] Validate the ID token.
    - [x] Extract user information from the ID token.
    - [x] Look up the user in the `users` table by email.
    - [x] If the user does not exist, create a new user (user provisioning).
    - [x] Log the user in and create a session.
    - [x] Redirect the user back to the original authorization flow.
  - [x] Modify the `oauthAuthorizationFlow()` method to check the `identity_provider` of the client. If it's 'google', redirect to `/authorize/google`.

## Phase 3: Frontend and User Interface

- [x] **Modify Client Registration/Management UI:**
  - [x] Add fields to the client management UI to configure the GCIP settings (`identity_provider`, `google_client_id`, `google_client_secret`).
- [x] **Modify Login Page:**
  - [x] The login page should seamlessly redirect to Google for authentication when a client is configured to use GCIP.

## Phase 4: Testing

- [x] **Unit Tests:**
  - [x] Write unit tests for the new methods in `AuthorizationController`.
  - [ ] Research and understand the existing testing framework.
- [ ] **Integration Tests:**
  - [ ] Create an integration test that simulates the entire GCIP login flow.

## Phase 5: Documentation

- [x] **Update Developer Documentation:**
  - [x] Document the new GCIP integration feature.
  - [x] Provide instructions on how to configure and use it.
- [ ] **Update User Documentation:**
  - [ ] Explain how to use the GCIP login feature.
