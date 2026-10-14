import { apiJson } from '../lib/http';

// Read-only description of how sites connect to this Control Center (see
// IntegrationSettingsController): the report endpoint, authentication, the
// timing rules and the live state of the connections. Super admin only.
export const integrationSettingsService = {
    fetch() {
        return apiJson('/api/integration-settings');
    },
};
