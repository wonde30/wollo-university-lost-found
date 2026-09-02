<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
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
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['name' => $cat['name']], $cat);
        }
    }
}
