# OneClick Control Center — API & Operational Integration Architecture

This document details the architectural standards and conventions for the backend API and external integrations as established in Step 3P.

## 1. Authentication & Session Compatibility
All API endpoints rely on standard Laravel cookie-based session state (`web` guard) and stateful CSRF verification.
- **Login**: `POST /login` establishes the session.
- **Logout**: `POST /logout` invalidates the session.
- **Current User**: `GET /api/user` fetches the active session payload.
- **CSRF Token**: Passed via the standard `X-XSRF-TOKEN` header on all non-safe HTTP methods.

## 2. API Route Prefixing & Versioning
To protect working features from version updates, API endpoints are split:
- **Legacy/Core Endpoints**: Located under `/api/...` (e.g., `/api/user`, `/api/users`, `/api/notifications`, `/api/audit-logs`).
- **Future Operational APIs**: Gated under versioned prefix `/api/v1/...` (e.g., `/api/v1/manpower/...`, `/api/v1/fleet/...`).

## 3. Response Conventions
All API responses must strictly follow these JSON envelopes:

### Single Entity
```json
{
    "data": {
        "id": 1,
        "name": "Project Alpha"
    }
}
```

### Collection (Paginated)
```json
{
    "data": [
        { "id": 1, "name": "Item A" }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 20,
        "total": 100
    }
}
```

### Error Responses
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "nrp": ["The nrp field is required."]
    }
}
```

## 4. HTTP Error Handling & Classifications
The frontend HTTP utility (`resources/js/lib/http.js`) maps HTTP statuses consistently to handle errors gracefully:
- **401**: Authentication/Session expired. Frontend redirects to `/login`.
- **403**: Forbidden capability or access scope. Shows Access Denied card/banner.
- **422**: Form validation failures. Renders error message per input field.
- **5xx**: Server or integration communication issue. Displays generic retry/integration error toast.

## 5. Site & Module Contexts
Future APIs must identify the active site and active module programmatically to prevent hardcoding.
- **Site Context**: Follow resource-oriented URL patterns where possible:
  `GET /api/v1/sites/{site}/manpower/roster`
- **Module Context**: Endpoint routes are grouped by their module keys:
  `GET /api/v1/manpower/...` -> Gated by `one-manpower` configuration.
  `GET /api/v1/fleet/...` -> Gated by `one-fleet` configuration.

## 6. External Integration Boundary & Adapters
Frontend components must never call external integrations directly. External endpoints must be wrapped behind a Laravel backend boundary:
1. **Client Client (`App\Services\Integrations\ExternalIntegrationClient`)**: Handles tokens, timeouts, connection retry logic, whitelists, request correlation, and connection failures.
2. **Adapter (`App\Services\Integrations\NormalizedDataExample`)**: Maps third-party response schemas into consistent OneClick DTO structures.

## 7. Request Correlation (Correlation ID)
Every request triggers a global middleware (`App\Http\Middleware\AddCorrelationId`) that:
1. Resolves/generates an `X-Request-Id` UUID.
2. Appends it to the request and response headers.
3. Propagates this ID to all downstream external client calls.
This allows end-to-end trace correlation in server logs for troubleshooting.

## 8. Configuration & Environment Variables
- Secrets, tokens, and third-party endpoints must only live on the server via `config(...)` resolving from `.env`.
- **Allowed Environment Variables**:
  - `EXTERNAL_API_BASE_URL`
  - `EXTERNAL_API_TOKEN`
  - `EXTERNAL_API_TIMEOUT`
- **Forbidden**: Exposing backend client secret keys, tokens, or raw credentials to Vite/Vue frontend assets.
