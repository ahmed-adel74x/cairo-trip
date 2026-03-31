<?php

namespace Database\Seeders;

use App\Models\Place;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlaceSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET NAMES utf8mb4');
        DB::statement('SET CHARACTER SET utf8mb4');

        // Disable foreign key checks to allow truncate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Place::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $places = [

            // ── Place 1: Al-Azhar ──────────────────────────────
            [
                'name_ar'          => 'المشي في الأزهر / المساجد الكبيرة',
                'name_en'          => 'Al-Azhar Walk / Grand Mosques',
                'description_ar'   => 'دخول عام، لكن قد تكون هناك رسوم لبعض المناطق',
                'description_en'   => 'General entry, but some areas may have fees',
                'image_url'        => 'places/azhar.jpg',
                'is_free'          => true,
                'price_ar'         => 'مجاني',
                'price_en'         => 'Free',
                'price_number'     => 0.00,
                'working_hours_ar' => '٩:٠٠ صباحاً - ١٢:٠٠ صباحاً',
                'working_hours_en' => '9:00 AM - 12:00 AM',
                'location_ar'      => 'القاهرة القديمة',
                'location_en'      => 'Old Cairo',
                'rating_avg'       => 0,
                'total_bookings'   => 0,
                'activities_ar'    => [
                    'الاستمتاع بالإطلالة البانورامية',
                    'استكشاف المساجد التاريخية',
                    'زيارة المعبر الفكري',
                ],
                'activities_en'    => [
                    'Enjoy the panoramic view',
                    'Explore historic mosques',
                    'Visit the intellectual passage',
                ],
                'is_active'        => true,
            ],

            // ── Place 2: Khan El-Khalili ───────────────────────
            [
                'name_ar'          => 'خان الخليلي',
                'name_en'          => 'Khan El-Khalili',
                'description_ar'   => 'دخول السوق مجاني، لكن المشتريات بمصاريف',
                'description_en'   => 'Market entry is free, but purchases cost extra',
                'image_url'        => 'places/khan.jpg',
                'is_free'          => true,
                'price_ar'         => 'مجاني',
                'price_en'         => 'Free',
                'price_number'     => 0.00,
                'working_hours_ar' => '١٠:٠٠ صباحاً - ١١:٠٠ مساءً',
                'working_hours_en' => '10:00 AM - 11:00 PM',
                'location_ar'      => 'القاهرة القديمة',
                'location_en'      => 'Old Cairo',
                'rating_avg'       => 0,
                'total_bookings'   => 0,
                'activities_ar'    => [
                    'التسوق في الأسواق التراثية',
                    'تذوق المأكولات الشعبية',
                    'التصوير في الأزقة التاريخية',
                ],
                'activities_en'    => [
                    'Shopping in heritage markets',
                    'Taste traditional food',
                    'Photography in historic alleys',
                ],
                'is_active'        => true,
            ],

            // ── Place 3: Giza Pyramids ─────────────────────────
            [
                'name_ar'          => 'أهرامات الجيزة',
                'name_en'          => 'Giza Pyramids',
                'description_ar'   => 'رسوم دخول تختلف للمصريين والأجانب',
                'description_en'   => 'Entry fees vary for Egyptians and foreigners',
                'image_url'        => 'places/pyramids.jpg',
                'is_free'          => false,
                'price_ar'         => '١٥٠ جنيه',
                'price_en'         => '150 EGP',
                'price_number'     => 150.00,
                'working_hours_ar' => '٨:٠٠ صباحاً - ٥:٠٠ مساءً',
                'working_hours_en' => '8:00 AM - 5:00 PM',
                'location_ar'      => 'الجيزة',
                'location_en'      => 'Giza',
                'rating_avg'       => 0,
                'total_bookings'   => 0,
                'activities_ar'    => [
                    'مشاهدة الأهرامات الثلاثة',
                    'زيارة أبو الهول',
                    'ركوب الجمال حول الأهرامات',
                ],
                'activities_en'    => [
                    'See the three pyramids',
                    'Visit the Sphinx',
                    'Camel ride around the pyramids',
                ],
                'is_active'        => true,
            ],

            // ── Place 4: The Great Sphinx ──────────────────────
            [
                'name_ar'          => 'أبو الهول',
                'name_en'          => 'The Great Sphinx',
                'description_ar'   => 'رسوم دخول تختلف للمصريين والأجانب',
                'description_en'   => 'Entry fees vary for Egyptians and foreigners',
                'image_url'        => 'places/sphinx.jpg',
                'is_free'          => false,
                'price_ar'         => '١٠٠ جنيه',
                'price_en'         => '100 EGP',
                'price_number'     => 100.00,
                'working_hours_ar' => '٨:٠٠ صباحاً - ٥:٠٠ مساءً',
                'working_hours_en' => '8:00 AM - 5:00 PM',
                'location_ar'      => 'الجيزة',
                'location_en'      => 'Giza',
                'rating_avg'       => 0,
                'total_bookings'   => 0,
                'activities_ar'    => [
                    'مشاهدة تمثال أبو الهول عن قرب',
                    'التصوير مع الأهرامات في الخلفية',
                    'الاستماع لشرح المرشد السياحي',
                ],
                'activities_en'    => [
                    'See the Sphinx statue up close',
                    'Photography with pyramids in background',
                    'Listen to tour guide explanation',
                ],
                'is_active'        => true,
            ],

            // ── Place 5: Egyptian Museum ───────────────────────
            [
                'name_ar'          => 'المتحف المصري',
                'name_en'          => 'The Egyptian Museum',
                'description_ar'   => 'رسوم دخول تختلف للمصريين والأجانب',
                'description_en'   => 'Entry fees vary for Egyptians and foreigners',
                'image_url'        => 'places/museum.jpg',
                'is_free'          => false,
                'price_ar'         => '٢٠٠ جنيه',
                'price_en'         => '200 EGP',
                'price_number'     => 200.00,
                'working_hours_ar' => '٩:٠٠ صباحاً - ٥:٠٠ مساءً',
                'working_hours_en' => '9:00 AM - 5:00 PM',
                'location_ar'      => 'التحرير',
                'location_en'      => 'Tahrir',
                'rating_avg'       => 0,
                'total_bookings'   => 0,
                'activities_ar'    => [
                    'مشاهدة مقتنيات توت عنخ آمون',
                    'زيارة قاعة المومياوات',
                    'استكشاف الآثار الفرعونية',
                ],
                'activities_en'    => [
                    'See Tutankhamun\'s treasures',
                    'Visit the Mummies Hall',
                    'Explore Pharaonic artifacts',
                ],
                'is_active'        => true,
            ],

            // ── Place 6: Cairo Tower ───────────────────────────
            [
                'name_ar'          => 'برج القاهرة',
                'name_en'          => 'Cairo Tower',
                'description_ar'   => 'رسوم دخول للصعود لأعلى البرج والاستمتاع بالمنظر',
                'description_en'   => 'Entry fees to go up the tower and enjoy the view',
                'image_url'        => 'places/cairo_tower.jpg',
                'is_free'          => false,
                'price_ar'         => '١٥٠ جنيه',
                'price_en'         => '150 EGP',
                'price_number'     => 150.00,
                'working_hours_ar' => '٩:٠٠ صباحاً - ١٢:٠٠ صباحاً',
                'working_hours_en' => '9:00 AM - 12:00 AM',
                'location_ar'      => 'الزمالك',
                'location_en'      => 'Zamalek',
                'rating_avg'       => 0,
                'total_bookings'   => 0,
                'activities_ar'    => [
                    'الاستمتاع بالإطلالة البانورامية',
                    'استكشاف الطابق الدوار',
                    'زيارة المطعم الفكري',
                ],
                'activities_en'    => [
                    'Enjoy the panoramic view',
                    'Explore the revolving floor',
                    'Visit the rooftop restaurant',
                ],
                'is_active'        => true,
            ],
        ];

        foreach ($places as $place) {
            Place::create($place);
        }

        $this->command->info('✅ 6 places seeded successfully!');
    }
}