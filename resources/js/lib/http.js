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

export async function apiFetch(url, options = {}) {
    return fetch(url, {
        ...options,
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-XSRF-TOKEN': getCsrfToken() ?? '',
            ...(options.headers ?? {}),
        },
    });
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
