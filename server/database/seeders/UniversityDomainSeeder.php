<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\UniversityDomain;
use Illuminate\Database\Seeder;

class UniversityDomainSeeder extends Seeder
{
    public function run(): void
    {
        $dessieCampus = Campus::where('short_code', 'DSS')->orWhere('name', 'like', '%Dessie%')->first();
        $kiotCampus = Campus::where('short_code', 'KIT')->orWhere('name', 'like', '%Kombolcha%')->first();

        $domains = [
            [
                'domain' => 'wu.edu.et',
                'institution_name' => 'Wollo University',
                'campus_id' => $dessieCampus?->id,
                'is_active' => true,
                'description' => 'Primary institutional email domain for Wollo University students, faculty, and administrative staff.',
            ],
            [
                'domain' => 'kiot.wu.edu.et',
                'institution_name' => 'Kombolcha Institute of Technology',
                'campus_id' => $kiotCampus?->id,
                'is_active' => true,
                'description' => 'Institutional subdomain for Kombolcha Institute of Technology (KIoT).',
            ],
        ];

        foreach ($domains as $domainData) {
            UniversityDomain::updateOrCreate(
                ['domain' => $domainData['domain']],
                $domainData
            );
        }
    }
}
