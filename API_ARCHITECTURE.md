# OpenEMR REST API Architecture

This document describes the complete REST API architecture, request lifecycle, and extension points.

## Overview

OpenEMR REST API is built on Symfony HttpKernel with an event-driven middleware architecture supporting three API types:

| API Type   | Base Path   | Purpose                      | Route Finder         |
|------------|-------------|------------------------------|----------------------|
| Standard   | `/api/`     | OpenEMR native REST API      | StandardRouteFinder  |
| FHIR       | `/fhir/`    | FHIR R4 US Core 3.1.0        | FhirRouteFinder      |
| Portal     | `/portal/`  | Patient Portal API           | PortalRouteFinder    |

## Entry Point

All API requests enter through `/apis/dispatch.php`:

```
HTTP Request
    ↓
.htaccess RewriteRule
    ↓
dispatch.php?_REWRITE_COMMAND=<path>
    ↓
HttpRestRequest::createFromGlobals()
    ↓
ApiApplication::run($request)
    ↓
OEHttpKernel::handle()
```

## Request Lifecycle

### Event Flow (Priority Order)

| Priority | Event              | Listener                     | Purpose                              |
|----------|--------------------|------------------------------|--------------------------------------|
| -        | kernel.exception   | ExceptionHandlerListener     | Catches and formats all exceptions   |
| 100      | kernel.request     | SiteSetupListener            | Site validation, globals, session    |
| 50       | kernel.request     | OAuth2AuthorizationListener  | Handles /oauth2/* endpoints          |
| 50       | kernel.request     | AuthorizationListener        | Token validation (Phase 1)           |
| 40       | kernel.request     | RoutesExtensionListener      | Route matching and dispatch          |
| 25       | kernel.request     | CORSListener                 | CORS preflight handling              |
| -        | security.check     | AuthorizationListener        | Scope validation (Phase 2)           |
| 50       | kernel.view        | ViewRendererListener         | Response formatting                  |
| -        | kernel.terminate   | TelemetryListener            | API analytics                        |
| -        | kernel.terminate   | ApiResponseLoggerListener    | Audit logging                        |
| -        | kernel.terminate   | SessionCleanupListener       | Session invalidation                 |

### Detailed Flow

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              HTTP REQUEST                                    │
└─────────────────────────────────────────────────────────────────────────────┘
                                     │
                                     ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ SiteSetupListener (priority 100)                                            │
│ ├─ Extract site ID from URL (/default/api/...)                              │
│ ├─ Validate site exists                                                      │
│ ├─ Create session (OAuth2 or API type)                                       │
│ ├─ Include interface/globals.php                                             │
│ └─ Generate OAuth2 keys if missing                                           │
└─────────────────────────────────────────────────────────────────────────────┘
                                     │
                                     ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ OAuth2AuthorizationListener (priority 50)                                   │
│ ├─ Check if path contains /oauth2/                                          │
│ ├─ If yes: route to AuthorizationController, stop propagation               │
│ └─ If no: continue to next listener                                         │
└─────────────────────────────────────────────────────────────────────────────┘
                                     │
                                     ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ AuthorizationListener.onKernelRequest (priority 50) - PHASE 1               │
│ ├─ Try LocalApiAuthorizationController (APICSRFTOKEN header)                │
│ ├─ Try SkipAuthorizationStrategy (public endpoints)                         │
│ ├─ Try BearerTokenAuthorizationStrategy (JWT validation)                    │
│ ├─ Set user identity and role                                               │
│ └─ Store scopes in request                                                  │
└─────────────────────────────────────────────────────────────────────────────┘
                                     │
                                     ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ RoutesExtensionListener (priority 40)                                       │
│ ├─ Detect API type from path (/api/, /fhir/, /portal/)                      │
│ ├─ Load routes via appropriate RouteFinder                                  │
│ ├─ Dispatch RestApiCreateEvent (for extensions)                             │
│ └─ Call HttpRestRouteHandler::dispatch()                                    │
└─────────────────────────────────────────────────────────────────────────────┘
                                     │
                                     ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ HttpRestRouteHandler::dispatch()                                            │
│ ├─ Match request against route patterns                                     │
│ ├─ Parse route parameters                                                   │
│ ├─ Fire RestApiSecurityCheckEvent                                           │
│ │   └─ AuthorizationListener.onRestApiSecurityCheck - PHASE 2               │
│ │       ├─ Skip if local API                                                │
│ │       ├─ Verify user role matches request context                         │
│ │       └─ Verify OAuth2 scopes cover resource+permission                   │
│ ├─ Set _controller attribute on request                                     │
│ └─ Return (kernel invokes controller)                                       │
└─────────────────────────────────────────────────────────────────────────────┘
                                     │
                                     ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ Controller Execution                                                        │
│ ├─ Optional: RestConfig::request_authorization_check() for ACL              │
│ ├─ Business logic                                                           │
│ └─ Return array/Response/Psr7Response                                       │
└─────────────────────────────────────────────────────────────────────────────┘
                                     │
                                     ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ ViewRendererListener (priority 50)                                          │
│ ├─ Psr7Response → Symfony Response                                          │
│ ├─ Array → JsonResponse                                                     │
│ └─ Add content-type headers                                                 │
└─────────────────────────────────────────────────────────────────────────────┘
                                     │
                                     ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                              HTTP RESPONSE                                   │
└─────────────────────────────────────────────────────────────────────────────┘
```

## Route Definition

### Route File Locations

| API Type | Route Files                                                |
|----------|------------------------------------------------------------|
| Standard | `apis/routes/_rest_routes_standard.inc.php`                |
|          | `apis/routes/standard/**/*.inc.php`                        |
| FHIR     | `apis/routes/_rest_routes_fhir_r4_us_core_3_1_0.inc.php`   |
| Portal   | `apis/routes/_rest_routes_portal.inc.php`                  |

### Route Format

```php
return [
    'METHOD /path/:parameter' => static function ($parameter, HttpRestRequest $request): ResponseInterface {
        RestConfig::request_authorization_check($request, 'section', 'value');
        return Controller::getInstance()->method($parameter, $request);
    },
];
```

**Route Pattern Syntax:**
- `:param` - Captured as regex group `([a-zA-Z0-9\-\_\$\:]+)`
- `$operation` - FHIR operations (prefixed with $)

### Route Parsing (HttpRestParsedRoute)

| Property             | Description                          | Example                |
|----------------------|--------------------------------------|------------------------|
| `resource`           | API resource name                    | `Patient`, `facility`  |
| `operation`          | FHIR operation (if present)          | `$export`, `$docref`   |
| `instanceIdentifier` | Last path parameter (ID)             | `uuid-string`          |
| `routeParams`        | All captured parameters              | `['id' => 'uuid']`     |

## Authorization Strategies

Strategies are tried in order until one succeeds:

| Order | Strategy                         | Trigger                  | Effect                        |
|-------|----------------------------------|--------------------------|-------------------------------|
| 1     | LocalApiAuthorizationController  | APICSRFTOKEN header      | Skip scope checks             |
| 2     | SkipAuthorizationStrategy        | Public endpoints         | Skip all auth                 |
| 3     | BearerTokenAuthorizationStrategy | Authorization: Bearer    | Full OAuth2 validation        |

### Public Endpoints (SkipAuthorizationStrategy)

| Endpoint                              | Description                    |
|---------------------------------------|--------------------------------|
| `/fhir/metadata`                      | FHIR Capability Statement      |
| `/fhir/.well-known/smart-configuration` | SMART configuration          |
| `/fhir/OperationDefinition`           | FHIR operations metadata       |
| `/api/version`                        | API version                    |
| `/api/product`                        | Product information            |

## Extension Points

### RestApiCreateEvent

Add custom routes dynamically:

```php
$dispatcher->addListener(RestApiCreateEvent::EVENT_HANDLE, function(RestApiCreateEvent $event) {
    $event->addToRouteMap('GET /api/custom/:id', [CustomController::class, 'getOne']);
    $event->addToFHIRRouteMap('GET /fhir/CustomResource', [FhirController::class, 'search']);
});
```

### RestApiSecurityCheckEvent

Customize authorization logic:

```php
$dispatcher->addListener(RestApiSecurityCheckEvent::EVENT_HANDLE, function(RestApiSecurityCheckEvent $event) {
    // Custom authorization logic
    if ($event->getResource() === 'CustomResource') {
        $event->setSkipSecurityCheck(true);
    }
});
```

## Error Handling

### Exception Pipeline

| Layer                    | Catches                | Response Format               |
|--------------------------|------------------------|-------------------------------|
| dispatch.php             | Throwable              | JSON `{"error": "..."}`       |
| ExceptionHandlerListener | All kernel exceptions  | JSON with trace (if debug)    |
| HttpRestRouteHandler     | AccessDeniedException  | 401/403 HttpException         |

### HTTP Status Codes

| Code | Scenario                          |
|------|-----------------------------------|
| 200  | Successful GET/PATCH              |
| 201  | Successful POST (create)          |
| 204  | Successful DELETE                 |
| 400  | Bad request (validation error)    |
| 401  | Unauthorized (no/invalid token)   |
| 403  | Forbidden (insufficient scopes)   |
| 404  | Route not found                   |
| 405  | Method not allowed                |
| 500  | Internal server error             |

## Key Classes Reference

| Class                          | Location                                      | Purpose                    |
|--------------------------------|-----------------------------------------------|----------------------------|
| `ApiApplication`               | `src/RestControllers/ApiApplication.php`      | Main orchestrator          |
| `OEHttpKernel`                 | `src/Core/OEHttpKernel.php`                   | Symfony kernel extension   |
| `HttpRestRequest`              | `src/Common/Http/HttpRestRequest.php`         | Request object             |
| `HttpRestRouteHandler`         | `src/Common/Http/HttpRestRouteHandler.php`    | Route dispatcher           |
| `HttpRestParsedRoute`          | `src/Common/Http/HttpRestParsedRoute.php`     | Route parser               |
| `AuthorizationListener`        | `src/RestControllers/Subscriber/`             | Auth middleware            |
| `RoutesExtensionListener`      | `src/RestControllers/Subscriber/`             | Route loader               |
| `RestConfig`                   | `src/RestControllers/Config/RestConfig.php`   | Auth/scope utilities       |

## FHIR vs Standard API Differences

| Aspect            | FHIR API                              | Standard API                    |
|-------------------|---------------------------------------|----------------------------------|
| Response Format   | FHIR JSON (Bundles, Resources)        | OpenEMR JSON                     |
| Resources         | FHIR R4 (Patient, Observation, etc.)  | Native (patient, facility, etc.) |
| Operations        | FHIR operations (`$export`)           | Standard REST only               |
| Search            | FHIR search params, _include          | Query parameters                 |
| Patient Context   | SMART launch binding                  | Explicit scope                   |
| Write Access      | Limited (mostly read)                 | Full CRUD                        |
| Controllers       | `FhirXxxRestController`               | `XxxRestController`              |
