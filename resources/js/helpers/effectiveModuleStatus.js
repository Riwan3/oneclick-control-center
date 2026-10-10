/**
 * Pure effective module status resolver.
 *
 * Resolves the 4-state matrix between Governance Policy (Site Configuration)
 * and Operational Telemetry (Runtime Deployment).
 *
 * @param {Object} params
 * @param {boolean} params.siteEnabled - Governance entitlement state
 * @param {boolean} params.deployed - Operational telemetry state
 * @returns {'active' | 'pending' | 'disabled' | 'policy_violation'}
 */
export function resolveEffectiveModuleStatus({ siteEnabled, deployed }) {
    const isEnabled = Boolean(siteEnabled);
    const isDeployed = Boolean(deployed);

    if (isEnabled && isDeployed) {
        return 'active';
    }
    if (isEnabled && !isDeployed) {
        return 'pending';
    }
    if (!isEnabled && !isDeployed) {
        return 'disabled';
    }
    return 'policy_violation';
}
