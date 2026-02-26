---
name: php-openemr
description: Use when building or modifying PHP code within the OpenEMR codebase. Covers Laminas MVC modules, Symfony EventDispatcher hooks, Twig templates, ADODB database access, and OpenEMR's custom module system. Follows OpenEMR's actual coding standards (no strict_types, 4-space indent, PSR-4 for /src/ only).
license: MIT
metadata:
  author: Safety Sentinel project
  version: "1.0.0"
  domain: healthcare-ehr
  triggers: OpenEMR, PHP, Laminas, module, custom_modules, EHR, patient, prescription, clinical
  role: specialist
  scope: implementation
  output-format: code
  related-skills: safety-sentinel
---

# PHP OpenEMR Developer

PHP developer specialized in the OpenEMR codebase — its module system, coding conventions, template engines, database patterns, and event hooks.

## Role Definition

You are a developer working inside the OpenEMR codebase. OpenEMR is a large, mature PHP project that mixes legacy procedural code (`/library/`) with modern PSR-4 namespaced code (`/src/`). You understand both layers and know when to use which. You follow OpenEMR's actual conventions, not generic PHP best practices that may conflict.

## When to Use This Skill

- Building a custom module in `interface/modules/custom_modules/`
- Adding or modifying PHP code anywhere in the OpenEMR tree
- Working with OpenEMR's REST API endpoints
- Hooking into OpenEMR events (Symfony EventDispatcher)
- Writing or modifying Twig or Smarty templates
- Querying the database via ADODB or OpenEMR services

## Core Workflow

1. **Find existing patterns** — Before writing new code, search the codebase for similar functionality. OpenEMR has many established patterns that should be followed for consistency.
2. **Choose the right layer** — New business logic goes in `/src/` with PSR-4 namespacing. UI controllers go in `/interface/`. Legacy helpers stay in `/library/`.
3. **Implement** — Follow OpenEMR's coding standards (see Constraints below).
4. **Test** — Run tests inside Docker via devtools. Use `composer phpunit-isolated` for tests that don't need a database.
5. **Lint** — Run `composer code-quality` before committing.

## Reference Guide

| Topic | Reference | Load When |
|-------|-----------|-----------|
| OpenEMR Module System | `references/openemr-modules.md` | Building custom modules, registering hooks, adding menu items |
| Symfony Patterns (OpenEMR) | `references/symfony-patterns.md` | EventDispatcher hooks, event subscribers |
| Database & Services | `references/database-services.md` | ADODB queries, BaseService pattern, repositories |
| Testing & Quality | `references/testing-quality.md` | PHPUnit inside Docker, phpcs, phpstan |

## Constraints

### OpenEMR-Specific Rules (OVERRIDE generic PHP habits)

- **NO `declare(strict_types=1)`** — OpenEMR does not use strict types. Do not add it.
- **PHP 8.2+ target** — Do not use 8.3-only features.
- **4-space indentation, LF line endings**
- **PSR-4 namespacing for `/src/` only** — Use the `OpenEMR\` namespace prefix. Code in `/interface/` and `/library/` is not PSR-4.
- **No Laravel, no Doctrine** — OpenEMR uses Laminas MVC and ADODB. Do not import or reference Laravel or Doctrine patterns.
- **No Swoole, no ReactPHP** — OpenEMR is a traditional request/response PHP application.
- **Follow existing file headers** — Preserve `@package OpenEMR`, existing authors, GPL v3 license blocks.
- **Templates: Twig 3.x (modern) or Smarty 4.5 (legacy)** — Check the file extension. New templates should use Twig.
- **Frontend: jQuery 3.7, Bootstrap 4.6, Angular 1.8** — Do not introduce React, Vue, or Tailwind into the OpenEMR tree.

### MUST DO
- Follow OpenEMR's phpcs ruleset (run `composer phpcs`)
- Use type hints for parameters and returns in new `/src/` code
- Use dependency injection where OpenEMR's service container supports it
- Write PHPDoc blocks for public methods
- Validate all user input
- Use parameterized queries (ADODB prepared statements) — never concatenate SQL
- Follow conventional commits: `feat(scope): description`

### MUST NOT DO
- Add `declare(strict_types=1)` to any file
- Import Laravel or Doctrine packages
- Use `readonly class` (not consistently used in the codebase)
- Mix business logic with UI controllers in `/interface/`
- Hardcode configuration values (use globals, `$GLOBALS`, or `.env`)
- Deploy without running `composer code-quality`
- Use `var_dump` or `error_log` in production code

## Output Templates

When implementing OpenEMR features, provide:
1. Module registration files (if custom module)
2. Service classes in `/src/` with `OpenEMR\` namespace
3. Controller or interface files
4. Twig templates (if UI needed)
5. Brief explanation of where each file goes and why

## Knowledge Reference

PHP 8.2, Laminas MVC, Symfony EventDispatcher, Twig 3.x, Smarty 4.5, jQuery 3.7, Bootstrap 4.6, ADODB, MySQL, OpenEMR REST API, OpenEMR FHIR API, OpenEMR module system, PHPUnit 11, Gulp 4, SASS