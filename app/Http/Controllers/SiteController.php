<?php

namespace App\Http\Controllers;

use App\Models\Site;

/**
 * Read-only listing of the existing sites catalogue, for the User
 * Management site-access picker. This is NOT a second site catalogue —
 * it exposes the same App\Models\Site table that user_site_access
 * already references. The frontend Sites/Site Detail pages remain on
 * their own mock data and do not use this endpoint.
 */
class SiteController extends Controller
{
    public function index()
    {
        return Site::orderBy('name')->get(['id', 'code', 'name']);
    }
}
