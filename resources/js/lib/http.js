// Minimal same-origin fetch wrapper for the Laravel session + CSRF
// pattern this app uses. No axios, no HTTP client dependency — native
// fetch is sufficient for the requests this app makes.
//
// Laravel's default 'web' middleware group sets an XSRF-TOKEN cookie on
// every response (no separate "get csrf cookie" round trip is needed
// here, since the SPA shell itself is served through that same
// middleware group on first load). We read that cookie fresh on every
// call — not cached — so it stays correct even after login/logout
// regenerate the session and its token.
function getCsrfToken() {
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : null;
}

// Centralized session-expiry hook. http.js deliberately never imports
// stores/auth.js (that would create a cycle: http.js -> auth store ->
// authService.js -> http.js) — instead it exposes this one registration
// point, and resources/js/app.js (the composition root, which already
// imports both the auth store and this module) supplies the actual
// handler once at startup. http.js itself stays a dumb transport layer
// with no app-specific decisions (e.g. whether the current user was
// actually authenticated) — that judgment lives in the registered
// handler, not here.
let sessionExpiredHandler = null;

export function registerSessionExpiredHandler(handler) {
    sessionExpiredHandler = handler;
}

export async function apiFetch(url, options = {}) {
    const headers = {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-XSRF-TOKEN': getCsrfToken() ?? '',
        ...(options.headers ?? {}),
    };
    // A FormData body needs the browser to set its own multipart boundary.
    if (options.body instanceof FormData) delete headers['Content-Type'];

    const response = await fetch(url, {
        ...options,
        credentials: 'same-origin',
        headers,
    });

    if (response.status === 401) {
        sessionExpiredHandler?.('session_expired');
    } else if (response.status === 419) {
        sessionExpiredHandler?.('csrf_expired');
    }

    return response;
}

// Convenience wrapper that also parses the JSON body (if any) and never
// throws on a non-2xx response — callers check `ok`.
export async function apiJson(url, options = {}) {
    const response = await apiFetch(url, options);
    let data = null;
    try {
        data = await response.json();
    } catch {
        data = null;
    }
    return { ok: response.ok, status: response.status, data };
}

/**
 * Classifies HTTP response status codes into consistent categories.
 */
export function getHttpErrorCategory(status) {
    if (status === 401 || status === 419) return 'auth';
    if (status === 403) return 'forbidden';
    if (status === 422) return 'validation';
    if (status >= 500) return 'server';
    return 'other';
}

/**
 * Parses and returns a consistent, user-friendly error message based on status and data.
 */
export function parseHttpErrorMessage(status, data) {
    if (status === 401) {
        return 'Your session has expired. Please log in again.';
    }
    if (status === 419) {
        return 'Your session has expired or the page was open too long. Please log in again.';
    }
    if (status === 403) {
        return 'You do not have permission to access this resource.';
    }
    if (status === 422) {
        return data?.message ?? 'Validation failed. Please check your inputs.';
    }
    if (status >= 500) {
        return 'A server or integration error occurred. Please try again later.';
    }
    return data?.message ?? 'An unexpected error occurred.';
}

