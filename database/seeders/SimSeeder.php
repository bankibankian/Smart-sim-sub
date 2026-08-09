<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceField;
use App\Models\ServicePrice;
use Illuminate\Database\Seeder;

class SimSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create or retrieve the 'simcard' service
        $service = Service::firstOrCreate(
            ['name' => 'simcard'],
            [
                'description' => 'SIM Card Activation and Corporate Services',
                'is_active' => true,
            ]
        );

        // 2. Define fields (categories) and pricing configuration
        $categories = [
            [
                'name' => 'POS SIM',
                'code' => 'pos_sim',
                'base' => 1000.00,
                'role_prices' => [
                    'personal'         => 1000.00,
                    'agent'            => 925.00,
                    'business'         => 950.00,
                    'partner'          => 900.00,
                    'coordinator'      => 0.00,
                    'regional_manager' => 0.00,
                ],
                'role_commissions' => [
                    'personal'         => 0.00,
                    'agent'            => 50.00,
                    'business'         => 30.00,
                    'partner'          => 75.00,
                    'coordinator'      => 20.00,
                    'regional_manager' => 35.00,
                ]
            ],
            [
                'name' => 'CAMERA SIM',
                'code' => 'camera_sim',
                'base' => 1500.00,
                'role_prices' => [
                    'personal'         => 1500.00,
                    'agent'            => 1350.00,
                    'business'         => 1400.00,
                    'partner'          => 1300.00,
                    'coordinator'      => 0.00,
                    'regional_manager' => 0.00,
                ],
                'role_commissions' => [
                    'personal'         => 0.00,
                    'agent'            => 75.00,
                    'business'         => 50.00,
                    'partner'          => 100.00,
                    'coordinator'      => 25.00,
                    'regional_manager' => 45.00,
                ]
            ],
            [
                'name' => 'CCTV',
                'code' => 'cctv_sim',
                'base' => 2000.00,
                'role_prices' => [
                    'personal'         => 2000.00,
                    'agent'            => 1850.00,
                    'business'         => 1900.00,
                    'partner'          => 1800.00,
                    'coordinator'      => 0.00,
                    'regional_manager' => 0.00,
                ],
                'role_commissions' => [
                    'personal'         => 0.00,
                    'agent'            => 100.00,
                    'business'         => 70.00,
                    'partner'          => 125.00,
                    'coordinator'      => 35.00,
                    'regional_manager' => 55.00,
                ]
            ],
            [
                'name' => 'ROUTER SIM',
                'code' => 'router_sim',
                'base' => 2500.00,
                'role_prices' => [
                    'personal'         => 2500.00,
                    'agent'            => 2350.00,
                    'business'         => 2400.00,
                    'partner'          => 2300.00,
                    'coordinator'      => 0.00,
                    'regional_manager' => 0.00,
                ],
                'role_commissions' => [
                    'personal'         => 0.00,
                    'agent'            => 125.00,
                    'business'         => 90.00,
                    'partner'          => 150.00,
                    'coordinator'      => 40.00,
                    'regional_manager' => 65.00,
                ]
            ],
            [
                'name' => 'GPS SIM',
                'code' => 'gps_sim',
                'base' => 3000.00,
                'role_prices' => [
                    'personal'         => 3000.00,
                    'agent'            => 2775.00,
                    'business'         => 2850.00,
                    'partner'          => 2700.00,
                    'coordinator'      => 0.00,
                    'regional_manager' => 0.00,
                ],
                'role_commissions' => [
                    'personal'         => 0.00,
                    'agent'            => 150.00,
                    'business'         => 110.00,
                    'partner'          => 175.00,
                    'coordinator'      => 45.00,
                    'regional_manager' => 75.00,
                ]
            ],
        ];

        foreach ($categories as $cat) {
            // Check if field_code is already taken
            $fieldCode = $cat['code'];
            if (ServiceField::where('field_code', $fieldCode)->where('service_id', '!=', $service->id)->exists()) {
                $fieldCode = 'sim_' . $cat['code'];
            }

            // Create/update the service field
            $field = ServiceField::updateOrCreate(
                [
                    'service_id' => $service->id,
                    'field_name' => $cat['name'],
                ],
                [
                    'field_code' => $fieldCode,
                    'base_price' => $cat['base'],
                    'is_active' => true,
                ]
            );

            // Seed/update role-based custom prices
            foreach ($cat['role_prices'] as $role => $priceValue) {
                $commissionValue = $cat['role_commissions'][$role] ?? 0.00;
                ServicePrice::updateOrCreate(
                    [
                        'service_fields_id' => $field->id,
                        'user_type'         => $role,
                        'user_id'           => null,
                    ],
                    [
                        'service_id' => $service->id,
                        'price'      => $priceValue,
                        'commission' => $commissionValue,
                    ]
                );
            }
        }
    }
}
