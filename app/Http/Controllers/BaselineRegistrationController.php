<?php

namespace App\Http\Controllers;

use App\Models\BaselineRegisteredModule;
use App\Models\Site;
use App\Services\AuditLogger;
use App\Services\SiteExtensions;
use App\Support\BaselineKeys;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Register / unregister modules in the Master Baseline.
 *
 * The 9 SKS modules stay locked (App\Support\BaselineKeys). A module a site
 * reports that is not part of the baseline — an extension — can be
 * REGISTERED: it is copied into the baseline together with the sidebar
 * hierarchy the reporting site sent, and from then on every site is
 * compared against it (Site Configuration, snapshots, Observatory).
 * UNREGISTERING turns it back into an extension. Site overrides stay as
 * they are: they are keyed by module/menu key, which does not change.
 *
 * Read: super_admin + management. Write: super_admin (baseline_manage).
 */
class BaselineRegistrationController extends Controller
{
    public function __construct(private AuditLogger $auditLogger)
    {
    }

    public function index()
    {
        return response()->json([
            'data' => BaselineRegisteredModule::with('registeredBy:id,name')->orderBy('id')->get()
                ->map(fn (BaselineRegisteredModule $module) => $this->present($module))->all(),
            'candidates' => $this->candidates(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'module_key' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/'],
            'site_code' => ['nullable', 'string'],
            'label' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $moduleKey = $data['module_key'];

        if (BaselineKeys::isCore($moduleKey) || BaselineKeys::isRegistered($moduleKey)) {
            throw ValidationException::withMessages(['module_key' => 'This module is already part of the baseline.']);
        }

        // The structure comes from what a site really reports — the site with the fullest manifest wins.
        $source = null;
        $sourceSite = null;
        $sites = Site::query()
            ->when($data['site_code'] ?? null, fn ($query, $code) => $query->where('code', $code))
            ->orderBy('code')->get();

        foreach ($sites as $site) {
            $extension = SiteExtensions::find($site, $moduleKey);
            if ($extension !== null && ($source === null || count($extension['menus']) > count($source['menus']))) {
                $source = $extension;
                $sourceSite = $site;
            }
        }

        if ($source === null) {
            throw ValidationException::withMessages(['module_key' => 'No connected site reports this module, so there is nothing to register.']);
        }

        $label = trim((string) ($data['label'] ?? ''));

        $module = BaselineRegisteredModule::create([
            'module_key' => $moduleKey,
            'label' => $label !== '' ? $label : $source['label'],
            'description' => $data['description'] ?? null,
            'menus' => array_map(fn (array $menu) => [
                'menu_key' => $menu['id'],
                'label' => $menu['label'],
                'parent_key' => $menu['parentId'],
                'order' => $menu['order'],
            ], $source['menus']),
            'source_site_code' => $sourceSite->code,
            'registered_by' => $request->user()?->id,
        ]);
        BaselineKeys::flush();

        $this->auditLogger->log(
            action: 'baseline_module_registered',
            description: "Module {$moduleKey} registered into the baseline (structure taken from site {$sourceSite->code})",
            metadata: [
                'module_key' => $moduleKey,
                'label' => $module->label,
                'source_site_code' => $sourceSite->code,
                'menu_count' => count($module->menus),
            ],
            entityType: 'BaselineModule',
            entityId: $module->id,
        );

        return response()->json(['data' => $this->present($module->load('registeredBy:id,name'))], 201);
    }

    public function destroy(string $moduleKey)
    {
        if (BaselineKeys::isCore($moduleKey)) {
            throw ValidationException::withMessages(['module_key' => 'A core baseline module (from the SKS site) is locked and cannot be unregistered.']);
        }

        $module = BaselineRegisteredModule::where('module_key', $moduleKey)->first();
        if ($module === null) {
            throw ValidationException::withMessages(['module_key' => 'This module is not registered in the baseline.']);
        }

        $module->delete();
        BaselineKeys::flush();

        $this->auditLogger->log(
            action: 'baseline_module_unregistered',
            description: "Module {$moduleKey} unregistered from the baseline",
            metadata: ['module_key' => $moduleKey, 'label' => $module->label],
            entityType: 'BaselineModule',
            entityId: $module->id,
        );

        return response()->noContent();
    }

    /** Extensions reported by at least one site that are not in the baseline yet. */
    private function candidates(): array
    {
        $candidates = [];

        foreach (Site::orderBy('code')->get() as $site) {
            foreach (SiteExtensions::forSite($site) as $extension) {
                $candidate = $candidates[$extension['key']] ?? [
                    'moduleKey' => $extension['key'],
                    'label' => $extension['label'],
                    'sites' => [],
                    'menuCount' => 0,
                ];
                $candidate['sites'][] = $site->code;
                $candidate['menuCount'] = max($candidate['menuCount'], count($extension['menus']));
                $candidates[$extension['key']] = $candidate;
            }
        }

        ksort($candidates);

        return array_values($candidates);
    }

    private function present(BaselineRegisteredModule $module): array
    {
        return [
            'moduleKey' => $module->module_key,
            'label' => $module->label,
            'description' => $module->description,
            'menus' => $module->menus,
            'sourceSiteCode' => $module->source_site_code,
            'registeredBy' => $module->registeredBy?->name,
            'registeredAt' => $module->created_at?->toIso8601String(),
        ];
    }
}
