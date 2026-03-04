# OpenEMR Mobile Shell (MVP)

This directory contains a mobile-first, responsive wrapper for OpenEMR intended to feel app-like on iOS/Android while reusing the existing web application.

## Goals
- Native-feeling shell (header, tab bar, fullscreen viewport)
- Touch-friendly controls and safe-area support
- Configurable installation URL for connecting to the correct OpenEMR instance
- Progressive Web App setup (manifest + service worker)

## Entry point
- `index.php`

## URL
- Default base URL uses server `$GLOBALS['web_root']`
- Can be overridden in the mobile settings panel and is persisted in `localStorage`

## Next Iterations
1. Add deep-link route mapping for key workflows (appointments, encounters, patient summary)
2. Add authenticated quick actions and role-based tabs
3. Add mobile-specific CSS overrides for high-traffic screens
4. Add offline queue for drafts (notes/tasks) with retry
5. Package with Capacitor for App Store / Play Store deployment
