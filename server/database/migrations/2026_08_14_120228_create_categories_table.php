<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('name', 60)->unique();
            $table->string('name_am', 60)->nullable(); // Amharic name
            $table->string('icon_slug', 50)->nullable();
            $table->tinyInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
        });

        DB::table('categories')->insert([
            ['name' => 'Electronics',       'name_am' => 'ኤሌክትሮኒክስ',     'icon_slug' => 'ti-device-mobile',  'sort_order' => 1,  'is_active' => true],
            ['name' => 'ID / Documents',    'name_am' => 'መታወቂያ / ሰነድ',  'icon_slug' => 'ti-id',             'sort_order' => 2,  'is_active' => true],
            ['name' => 'Clothing',          'name_am' => 'ልብስ',            'icon_slug' => 'ti-shirt',          'sort_order' => 3,  'is_active' => true],
            ['name' => 'Books / Notes',     'name_am' => 'መጽሐፍ / ማስታወሻ', 'icon_slug' => 'ti-book',           'sort_order' => 4,  'is_active' => true],
            ['name' => 'Keys',              'name_am' => 'ቁልፍ',            'icon_slug' => 'ti-key',            'sort_order' => 5,  'is_active' => true],
            ['name' => 'Bag / Wallet',      'name_am' => 'ቦርሳ / ኪስ',      'icon_slug' => 'ti-briefcase',      'sort_order' => 6,  'is_active' => true],
            ['name' => 'Jewelry',           'name_am' => 'ጌጣጌጥ',           'icon_slug' => 'ti-diamond',        'sort_order' => 7,  'is_active' => true],
            ['name' => 'Sports Equipment',  'name_am' => 'የስፖርት መሳሪያ',   'icon_slug' => 'ti-ball-football',  'sort_order' => 8,  'is_active' => true],
            ['name' => 'Glasses',           'name_am' => 'መነጽር',           'icon_slug' => 'ti-eyeglass',       'sort_order' => 9,  'is_active' => true],
            ['name' => 'Umbrella',          'name_am' => 'ጃንጥላ',           'icon_slug' => 'ti-umbrella',       'sort_order' => 10, 'is_active' => true],
            ['name' => 'Other',             'name_am' => 'ሌላ',              'icon_slug' => 'ti-package',        'sort_order' => 99, 'is_active' => true],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
