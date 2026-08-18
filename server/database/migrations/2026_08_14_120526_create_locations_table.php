<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')
                  ->constrained('campuses')
                  ->restrictOnDelete();
            $table->string('name', 120);
            $table->string('name_am', 120)->nullable();
            $table->string('code', 40)->unique();
            $table->string('building', 80)->nullable();
            $table->enum('zone', [
                'academic',
                'library',
                'dormitory',
                'cafeteria',
                'sports',
                'administrative',
                'gate',
                'outdoor',
                'other',
            ])->default('other');
            $table->boolean('is_active')->default(true);
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['campus_id', 'name']);
        });

        $now = now();

        // Dessie Campus locations (campus_id = 1)
        $dessie = [
            ['campus_id'=>1,'name'=>'Main Library — Ground Floor',        'name_am'=>'ዋና ቤተ-መጻሕፍት — ታችኛ ፎቅ',  'code'=>'DSS-LIB-GF',   'building'=>'Main Library',          'zone'=>'library',        'sort_order'=>1],
            ['campus_id'=>1,'name'=>'Main Library — 2nd Floor',           'name_am'=>'ዋና ቤተ-መጻሕፍት — 2ኛ ፎቅ',   'code'=>'DSS-LIB-2F',   'building'=>'Main Library',          'zone'=>'library',        'sort_order'=>2],
            ['campus_id'=>1,'name'=>'Main Library — 3rd Floor',           'name_am'=>'ዋና ቤተ-መጻሕፍት — 3ኛ ፎቅ',   'code'=>'DSS-LIB-3F',   'building'=>'Main Library',          'zone'=>'library',        'sort_order'=>3],
            ['campus_id'=>1,'name'=>'Student Cafeteria',                  'name_am'=>'የተማሪዎች ካፌቴሪያ',           'code'=>'DSS-CAFE',      'building'=>'Cafeteria Block',       'zone'=>'cafeteria',      'sort_order'=>4],
            ['campus_id'=>1,'name'=>'Main Gate — Security Post',          'name_am'=>'ዋና በር — ፀጥታ ጣቢያ',        'code'=>'DSS-GATE',      'building'=>'Main Gate',             'zone'=>'gate',           'sort_order'=>5],
            ['campus_id'=>1,'name'=>'Registrar Office',                   'name_am'=>'ምዝገባ ቢሮ',                'code'=>'DSS-REG',       'building'=>'Administration Block',  'zone'=>'administrative', 'sort_order'=>6],
            ['campus_id'=>1,'name'=>'Administration Block',               'name_am'=>'አስተዳደር ህንጻ',             'code'=>'DSS-ADMIN',     'building'=>'Administration Block',  'zone'=>'administrative', 'sort_order'=>7],
            ['campus_id'=>1,'name'=>'College of Agriculture Block',       'name_am'=>'የግብርና ኮሌጅ ህንጻ',         'code'=>'DSS-AGR',       'building'=>'Agriculture Block',     'zone'=>'academic',       'sort_order'=>8],
            ['campus_id'=>1,'name'=>'College of Business Block',          'name_am'=>'የቢዝነስ ኮሌጅ ህንጻ',         'code'=>'DSS-BEC',       'building'=>'Business Block',        'zone'=>'academic',       'sort_order'=>9],
            ['campus_id'=>1,'name'=>'College of Medicine Block',          'name_am'=>'የህክምና ኮሌጅ ህንጻ',         'code'=>'DSS-MED',       'building'=>'Medicine Block',        'zone'=>'academic',       'sort_order'=>10],
            ['campus_id'=>1,'name'=>'Natural Sciences Block',             'name_am'=>'የተፈጥሮ ሳይንስ ህንጻ',        'code'=>'DSS-NCS',       'building'=>'Science Block',         'zone'=>'academic',       'sort_order'=>11],
            ['campus_id'=>1,'name'=>'Law School Block',                   'name_am'=>'የህግ ትምህርት ቤት',           'code'=>'DSS-LAW',       'building'=>'Law Block',             'zone'=>'academic',       'sort_order'=>12],
            ['campus_id'=>1,'name'=>'Male Dormitory — Block A',           'name_am'=>'የወንድ ተማሪዎች መኝታ — A',   'code'=>'DSS-DORM-MA',   'building'=>'Male Dormitory',        'zone'=>'dormitory',      'sort_order'=>13],
            ['campus_id'=>1,'name'=>'Male Dormitory — Block B',           'name_am'=>'የወንድ ተማሪዎች መኝታ — B',   'code'=>'DSS-DORM-MB',   'building'=>'Male Dormitory',        'zone'=>'dormitory',      'sort_order'=>14],
            ['campus_id'=>1,'name'=>'Female Dormitory',                   'name_am'=>'የሴት ተማሪዎች መኝታ',        'code'=>'DSS-DORM-F',    'building'=>'Female Dormitory',      'zone'=>'dormitory',      'sort_order'=>15],
            ['campus_id'=>1,'name'=>'Sports Field',                       'name_am'=>'የስፖርት ሜዳ',              'code'=>'DSS-SPORT',     'building'=>null,                    'zone'=>'sports',         'sort_order'=>16],
            ['campus_id'=>1,'name'=>'Health Center',                      'name_am'=>'የጤና ጣቢያ',               'code'=>'DSS-HEALTH',    'building'=>'Health Center',         'zone'=>'administrative', 'sort_order'=>17],
            ['campus_id'=>1,'name'=>'Student Union Building',             'name_am'=>'የተማሪዎች ማህበር ህንጻ',      'code'=>'DSS-UNION',     'building'=>'Student Union',         'zone'=>'administrative', 'sort_order'=>18],
        ];

        // KIoT Campus locations (campus_id = 2)
        $kiot = [
            ['campus_id'=>2,'name'=>'KIoT Library',                       'name_am'=>'ኪዮቲ ቤተ-መጻሕፍት',          'code'=>'KIT-LIB',       'building'=>'Library',               'zone'=>'library',        'sort_order'=>1],
            ['campus_id'=>2,'name'=>'KIoT Main Gate',                     'name_am'=>'ኪዮቲ ዋና በር',             'code'=>'KIT-GATE',      'building'=>'Main Gate',             'zone'=>'gate',           'sort_order'=>2],
            ['campus_id'=>2,'name'=>'Engineering Block A',                 'name_am'=>'ኢንጂነሪንግ ህንጻ A',         'code'=>'KIT-ENG-A',     'building'=>'Engineering Block A',   'zone'=>'academic',       'sort_order'=>3],
            ['campus_id'=>2,'name'=>'Engineering Block B',                 'name_am'=>'ኢንጂነሪንግ ህንጻ B',         'code'=>'KIT-ENG-B',     'building'=>'Engineering Block B',   'zone'=>'academic',       'sort_order'=>4],
            ['campus_id'=>2,'name'=>'Informatics Block',                   'name_am'=>'ኢንፎርማቲክስ ህንጻ',         'code'=>'KIT-INF',       'building'=>'Informatics Block',     'zone'=>'academic',       'sort_order'=>5],
            ['campus_id'=>2,'name'=>'Computer Lab — Block A',              'name_am'=>'ኮምፒውተር ክፍል — A',        'code'=>'KIT-COMP-A',    'building'=>'Informatics Block',     'zone'=>'academic',       'sort_order'=>6],
            ['campus_id'=>2,'name'=>'Computer Lab — Block B',              'name_am'=>'ኮምፒውተር ክፍል — B',        'code'=>'KIT-COMP-B',    'building'=>'Informatics Block',     'zone'=>'academic',       'sort_order'=>7],
            ['campus_id'=>2,'name'=>'Textile and Fashion Block',           'name_am'=>'ጨርቃ ጨርቅ ትምህርት ቤት',     'code'=>'KIT-TFD',       'building'=>'Textile Block',         'zone'=>'academic',       'sort_order'=>8],
            ['campus_id'=>2,'name'=>'KIoT Cafeteria',                     'name_am'=>'ኪዮቲ ካፌቴሪያ',             'code'=>'KIT-CAFE',      'building'=>'Cafeteria',             'zone'=>'cafeteria',      'sort_order'=>9],
            ['campus_id'=>2,'name'=>'KIoT Male Dormitory',                'name_am'=>'ኪዮቲ የወንድ ተማሪዎች መኝታ',  'code'=>'KIT-DORM-M',    'building'=>'Male Dormitory',        'zone'=>'dormitory',      'sort_order'=>10],
            ['campus_id'=>2,'name'=>'KIoT Female Dormitory',              'name_am'=>'ኪዮቲ የሴት ተማሪዎች መኝታ',   'code'=>'KIT-DORM-F',    'building'=>'Female Dormitory',      'zone'=>'dormitory',      'sort_order'=>11],
            ['campus_id'=>2,'name'=>'KIoT Administration Office',         'name_am'=>'ኪዮቲ አስተዳደር ቢሮ',        'code'=>'KIT-ADMIN',     'building'=>'Admin Building',        'zone'=>'administrative', 'sort_order'=>12],
            ['campus_id'=>2,'name'=>'KIoT Sports Complex',                'name_am'=>'ኪዮቲ የስፖርት ቦታ',         'code'=>'KIT-SPORT',     'building'=>null,                    'zone'=>'sports',         'sort_order'=>13],
        ];

        foreach (array_merge($dessie, $kiot) as $row) {
            $row['is_active'] = true;
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
            DB::table('locations')->insert($row);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
