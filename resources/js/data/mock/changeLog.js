// CHANGE LOG — structural product-history records only.
//
// This answers "what changed in the OneClick Control Center product?"
// — a distinct concept from Audit Trail (who did what), Releases
// (formal version catalog), and Deployments (which version runs where).
// This file never links to any of those three; no deployment/site/user
// reference exists anywhere below.
//
// Every entry here is a real change made earlier in this same build —
// nothing is invented. `date` is the date this version's changelog was
// recorded (one date per version, matching common CHANGELOG.md
// convention), not a claim about the exact moment each item shipped.
//
// Record shape:
// { id, version, date, type, title, description, impact, status }
// type: 'feature' | 'improvement' | 'bug_fix' | 'governance'
//       ('governance' reserved for an actual governance-related change
//       — none of the entries below are that, so none use it yet)
export const changeLog = [
    {
        id: 1,
        version: 'v3.2.0',
        date: '2026-08-27',
        type: 'feature',
        title: 'API Integration UI Foundation',
        description:
            'Added the initial API Integration UI foundation for managing Site API connections, Control Center API services, and API configuration readiness.',
        impact: 'System integration foundation',
        status: 'Implemented',
    },
    {
        id: 2,
        version: 'v3.2.0',
        date: '2026-08-27',
        type: 'feature',
        title: 'KPI Telemetry Readiness',
        description:
            'Added KPI telemetry readiness presentation, integration boundary, and catalogue readiness states.',
        impact: 'KPI visibility foundation',
        status: 'Implemented',
    },
    {
        id: 3,
        version: 'v3.2.0',
        date: '2026-08-27',
        type: 'improvement',
        title: 'Governance-aware Module Observatory',
        description:
            'Improved module observability presentation using governance-aware module status semantics across the Control Center.',
        impact: 'Governance visibility',
        status: 'Implemented',
    },
    {
        id: 4,
        version: 'v3.2.0',
        date: '2026-08-27',
        type: 'bug_fix',
        title: 'Module Observatory Module Selection',
        description:
            'Fixed Module Observatory so the selected module query parameter determines which module is observed instead of always loading One-Power.',
        impact: 'Navigation accuracy',
        status: 'Fixed',
    },
    {
        id: 5,
        version: 'v3.2.0',
        date: '2026-08-27',
        type: 'improvement',
        title: 'Global UI/UX Status Consistency',
        description:
            'Standardized status colors, pending-state presentation, KPI trend context, and filter controls across the main Control Center pages.',
        impact: 'Management presentation consistency',
        status: 'Implemented',
    },
    {
        id: 6,
        version: 'v3.2.0',
        date: '2026-08-27',
        type: 'improvement',
        title: 'Sites Governance Status Presentation',
        description:
            'Aligned site module status presentation with the shared governance-aware effective module status contract.',
        impact: 'Site registry accuracy',
        status: 'Implemented',
    },
    {
        id: 7,
        version: 'v3.2.0',
        date: '2026-08-27',
        type: 'improvement',
        title: 'Deployment Observatory',
        description:
            'Added governance-aware deployment observatory presentation while preserving existing version and release deployment tracking as a separate concept.',
        impact: 'Deployment visibility',
        status: 'Implemented',
    },
];

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
