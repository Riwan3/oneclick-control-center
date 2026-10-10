// Change Log business logic — Page -> Composable -> local mock data,
// the same layering useKpiCatalogue.js/useApiIntegration.js already
// use (no backend exists for this domain). Product history only —
// never touches Audit Trail, Releases, or Deployments data/composables.
import { ref, computed } from 'vue';
import { changeLog, CHANGE_LOG_TYPE_LABEL, CHANGE_LOG_TYPE_VARIANT } from '../data/mock/changeLog';

export function useChangeLog() {
    const typeFilter = ref('all');

    // Newest first — sorts by date then id, both already descending in
    // the source data, but sorted defensively rather than assumed.
    const sortedChangeLog = computed(() =>
        [...changeLog].sort((a, b) => (a.date === b.date ? b.id - a.id : b.date.localeCompare(a.date)))
    );

    const filteredChangeLog = computed(() =>
        typeFilter.value === 'all'
            ? sortedChangeLog.value
            : sortedChangeLog.value.filter((entry) => entry.type === typeFilter.value)
    );

    // Executive summary always reflects the full log, independent of
    // the active filter — same convention as Sites.vue's
    // SiteStatusSummary (summary unaffected by the toolbar filters).
    const summary = computed(() => ({
        total: changeLog.length,
        features: changeLog.filter((entry) => entry.type === 'feature').length,
        improvements: changeLog.filter((entry) => entry.type === 'improvement').length,
        bugFixes: changeLog.filter((entry) => entry.type === 'bug_fix').length,
    }));

    return {
        typeFilter,
        changeLog: sortedChangeLog,
        filteredChangeLog,
        summary,
        typeLabel: CHANGE_LOG_TYPE_LABEL,
        typeVariant: CHANGE_LOG_TYPE_VARIANT,
    };
}
