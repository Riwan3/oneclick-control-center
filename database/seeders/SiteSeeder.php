<?php

namespace Database\Seeders;

use App\Models\Site;
use Illuminate\Database\Seeder;

/**
 * The site registry: identity of each PPA site (code, name, location).
 * Live status is not stored here — it is pulled from each connected
 * OneClick site (see SiteConnectionService). The frontend reads this
 * table through GET /api/site-connections.
 */
class SiteSeeder extends Seeder
{
    private const SITES = [
        ['code' => 'BIB', 'name' => 'PT Borneo Indobara', 'location' => 'Kalimantan Selatan'],
        ['code' => 'MHU', 'name' => 'PT Mitra Hutama Utama', 'location' => 'Kalimantan Timur'],
        ['code' => 'SKS', 'name' => 'PT Sarana Karya Sejahtera', 'location' => 'Sumatra Selatan'],
        ['code' => 'WARA', 'name' => 'PT Wahana Raya Abadi', 'location' => 'Kalimantan Tengah'],
        ['code' => 'MIP', 'name' => 'PT Mitra Industri Persada', 'location' => 'Sulawesi Selatan'],
        ['code' => 'BA', 'name' => 'PT Bara Abadi', 'location' => 'Kalimantan Timur'],
        ['code' => 'MIFA', 'name' => 'PT Mitra Fajar Abadi', 'location' => 'Aceh'],
        ['code' => 'BCP', 'name' => 'PT Bumi Cipta Persada', 'location' => 'Kalimantan Selatan'],
        ['code' => 'AMI', 'name' => 'PT Andalan Mitra Indonesia', 'location' => 'Jawa Timur'],
        ['code' => 'MLP', 'name' => 'PT Mitra Logistik Persada', 'location' => 'Kalimantan Timur'],
        ['code' => 'PIK', 'name' => 'PT Prima Investasi Karya', 'location' => 'Sumatra Utara'],
        ['code' => 'BGE', 'name' => 'PT Bara Global Energi', 'location' => 'Kalimantan Selatan'],
        ['code' => 'SBS', 'name' => 'PT Sinar Bara Sejahtera', 'location' => 'Kalimantan Tengah'],
        ['code' => 'KES', 'name' => 'PT Karya Energi Sejahtera', 'location' => 'Sulawesi Tenggara'],
    ];

    public function run(): void
    {
        foreach (self::SITES as $site) {
            Site::updateOrCreate(['code' => $site['code']], $site);
        }
    }
}
