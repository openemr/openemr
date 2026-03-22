# Changelog - MedEx Communication Module

All notable changes to this project will be documented in this file.

## [1.1.0] - 2026-02-08

### Added
- **SSO Token Generation**: Secure single sign-on for SaaS embedding
  - `MedExAPI::generateSSOToken($ttl)` - HMAC-SHA256 signed tokens
  - `MedExAPI::getSaaSUrl($page, $params)` - Auto-generates SaaS URLs with SSO
  - 1-hour default TTL with replay attack prevention
- **SaaS-First Architecture**: Module now redirects to MedExBank SaaS for all UI
  - Registration: Opens MedExBank registration page
  - Dashboard: Opens MedExBank dashboard with SSO auto-login
  - Settings: Opens MedExBank settings with SSO
- **SSO Implementation Documentation**: Complete spec for SaaS team
  - Token structure and validation steps
  - Security best practices
  - PHP implementation examples

### Changed
- **Status Modal**: Updated to use SaaS links instead of local pages
  - "Register on MedEx" → Opens SaaS registration
  - "Open Dashboard" → Opens SaaS with SSO token
  - Bootstrap 4 styling to match OpenEMR Module Manager UX
- **Module Version**: Bumped from 0.0.9 → 1.1.0
- **Architecture**: Refactored from local UI to SaaS-first approach

### Deprecated
- `admin/splash.php` - Marketing page moved to SaaS
- `admin/onboarding.php` - Multi-step wizard moved to SaaS
- `admin/register.php` - Registration form moved to SaaS
- `admin/settings.php` - Settings UI moved to SaaS (partial)

### Security
- SSO tokens use HMAC-SHA256 signatures
- Time-limited tokens with configurable TTL
- Nonce prevents replay attacks
- HTTPS required for all SaaS communication

### Documentation
- `SSO_IMPLEMENTATION_SPEC.md` - Complete SSO specification for SaaS team
- `DEPRECATED_PAGES.md` - List of deprecated local pages and migration path

## [1.0.0] - 2025-01-XX

### Added
- Initial stable release
- MedEx API integration
- Recall Board integration
- Flow Board (Patient Tracker) integration
- Message notifications
- Module Manager integration
- Help system with modal overlay
- Bootstrap file with event listeners

### Fixed
- Module registration issues
- Enable/Disable button state
- Help button functionality
- Modal display and styling

## [0.0.9] - 2025-01-XX

### Added
- Beta release
- Core API functionality
- Basic UI components

---

**Note**: Dates are approximate. See git commit history for exact dates.

Format based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
