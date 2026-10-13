// CHANGE LOG — structural product-history records only.
//
// This answers "what changed in the OneClick Control Center product?"
// — a distinct concept from Audit Trail (who did what), Releases
// (formal version catalog), and Deployments (which version runs where).
// This file never links to any of those three; no deployment/site/user
// reference exists anywhere below.
//
// `changeLog` is intentionally empty: the sample entries that used to be
// here were placeholders, not real product history. Add real entries
// using the shape below (the Change Log page shows its empty state until
// then).
//
// Record shape:
// { id, version, date, type, title, description, impact, status }
// type: 'feature' | 'improvement' | 'bug_fix' | 'governance'
export const changeLog = [];

export const CHANGE_LOG_TYPE_LABEL = {
    feature: 'Feature',
    improvement: 'Improvement',
    bug_fix: 'Bug Fix',
    governance: 'Governance',
};

// info = new capability (notable); neutral = routine improvement/fix —
// never amber/red for an ordinary Bug Fix; warning reserved strictly
// for an actual governance exception/change, not used by default.
export const CHANGE_LOG_TYPE_VARIANT = {
    feature: 'info',
    improvement: 'neutral',
    bug_fix: 'neutral',
    governance: 'warning',
};
