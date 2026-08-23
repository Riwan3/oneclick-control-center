// Conceptual OneClick module catalogue.
//
// This is a static lookup of module identity/labels only. Which modules
// are actually enabled is a per-site concern — see the mock `modules` map
// on each site in stores/site.js. Real module metadata (versioning,
// entitlements, etc.) will come from the backend once it exists.
export const modules = [
    { key: 'one-manpower', label: 'One-Manpower' },
    { key: 'one-fleet', label: 'One-Fleet' },
    { key: 'one-dms', label: 'One-DMS' },
    { key: 'one-qcc', label: 'One-QCC' },
];
