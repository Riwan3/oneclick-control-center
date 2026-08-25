<?php

namespace Database\Seeders;

use App\Models\Site;
use Illuminate\Database\Seeder;

/**
 * Mirrors the identity of resources/js/data/mock/sites.js (code + name
 * only) so the relational site-access structure has real rows to
 * reference. Does NOT wire the frontend Sites/Site Detail pages to the
 * database — those continue reading the JS mock data unchanged.
 */
class SiteSeeder extends Seeder
{
    private const SITES = [
        ['code' => 'BIB', 'name' => 'PT Borneo Indobara'],
        ['code' => 'MHU', 'name' => 'PT Mitra Hutama Utama'],
        ['code' => 'SKS', 'name' => 'PT Sarana Karya Sejahtera'],
        ['code' => 'WARA', 'name' => 'PT Wahana Raya Abadi'],
        ['code' => 'MIP', 'name' => 'PT Mitra Industri Persada'],
        ['code' => 'BA', 'name' => 'PT Bara Abadi'],
        ['code' => 'MIFA', 'name' => 'PT Mitra Fajar Abadi'],
        ['code' => 'BCP', 'name' => 'PT Bumi Cipta Persada'],
        ['code' => 'AMI', 'name' => 'PT Andalan Mitra Indonesia'],
        ['code' => 'MLP', 'name' => 'PT Mitra Logistik Persada'],
        ['code' => 'PIK', 'name' => 'PT Prima Investasi Karya'],
        ['code' => 'BGE', 'name' => 'PT Bara Global Energi'],
        ['code' => 'SBS', 'name' => 'PT Sinar Bara Sejahtera'],
        ['code' => 'KES', 'name' => 'PT Karya Energi Sejahtera'],
    ];

    public function run(): void
    {
        foreach (self::SITES as $site) {
            Site::updateOrCreate(['code' => $site['code']], $site);
        }
    }
}
