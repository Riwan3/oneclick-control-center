<?php

namespace App\Http\Controllers;

use App\Models\Module;

/**
 * Read-only listing of the existing modules catalogue, for the User
 * Management module-access picker. Not a second module catalogue — the
 * same keys/labels as resources/js/config/modules.js, sourced from the
 * same App\Models\Module table user_module_access already references.
 */
class ModuleController extends Controller
{
    public function index()
    {
        return Module::orderBy('label')->get(['id', 'key', 'label']);
    }
}
