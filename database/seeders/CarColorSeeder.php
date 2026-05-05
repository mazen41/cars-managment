<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CarColor;
use App\Models\CarColorTranslation;

class CarColorSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $colors = [
            [
                'name' => 'White',
                'hex_code' => '#FFFFFF',
                'status' => 'active',
                'translations' => [
                    'ar' => 'أبيض'
                ]
            ],
            [
                'name' => 'Black',
                'hex_code' => '#000000',
                'status' => 'active',
                'translations' => [
                    'ar' => 'أسود'
                ]
            ],
            [
                'name' => 'Silver',
                'hex_code' => '#C0C0C0',
                'status' => 'active',
                'translations' => [
                    'ar' => 'فضي'
                ]
            ],
            [
                'name' => 'Gray',
                'hex_code' => '#808080',
                'status' => 'active',
                'translations' => [
                    'ar' => 'رمادي'
                ]
            ],
            [
                'name' => 'Red',
                'hex_code' => '#FF0000',
                'status' => 'active',
                'translations' => [
                    'ar' => 'أحمر'
                ]
            ],
            [
                'name' => 'Blue',
                'hex_code' => '#0000FF',
                'status' => 'active',
                'translations' => [
                    'ar' => 'أزرق'
                ]
            ],
            [
                'name' => 'Green',
                'hex_code' => '#008000',
                'status' => 'active',
                'translations' => [
                    'ar' => 'أخضر'
                ]
            ],
            [
                'name' => 'Yellow',
                'hex_code' => '#FFFF00',
                'status' => 'active',
                'translations' => [
                    'ar' => 'أصفر'
                ]
            ],
            [
                'name' => 'Orange',
                'hex_code' => '#FFA500',
                'status' => 'active',
                'translations' => [
                    'ar' => 'برتقالي'
                ]
            ],
            [
                'name' => 'Brown',
                'hex_code' => '#A52A2A',
                'status' => 'active',
                'translations' => [
                    'ar' => 'بني'
                ]
            ],
            [
                'name' => 'Purple',
                'hex_code' => '#800080',
                'status' => 'active',
                'translations' => [
                    'ar' => 'بنفسجي'
                ]
            ],
            [
                'name' => 'Pink',
                'hex_code' => '#FFC0CB',
                'status' => 'active',
                'translations' => [
                    'ar' => 'وردي'
                ]
            ],
            [
                'name' => 'Gold',
                'hex_code' => '#FFD700',
                'status' => 'active',
                'translations' => [
                    'ar' => 'ذهبي'
                ]
            ],
            [
                'name' => 'Beige',
                'hex_code' => '#F5F5DC',
                'status' => 'active',
                'translations' => [
                    'ar' => 'بيج'
                ]
            ],
            [
                'name' => 'Maroon',
                'hex_code' => '#800000',
                'status' => 'active',
                'translations' => [
                    'ar' => 'كستنائي'
                ]
            ]
        ];

        foreach ($colors as $colorData) {
            // Create the color
            $color = CarColor::create([
                'name' => $colorData['name'],
                'hex_code' => $colorData['hex_code'],
                'status' => $colorData['status'],
            ]);

            // Create translations
            if (isset($colorData['translations'])) {
                foreach ($colorData['translations'] as $lang => $translatedName) {
                    CarColorTranslation::create([
                        'car_color_id' => $color->id,
                        'lang' => $lang,
                        'name' => $translatedName,
                    ]);
                }
            }
        }

        $this->command->info('Car colors seeded successfully!');
    }
}
