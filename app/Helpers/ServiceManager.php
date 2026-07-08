<?php

namespace App\Helpers;

use App\Models\Service;
use App\Models\ServiceField;

class ServiceManager
{
    /**
     * Get or create a service and its associated fields.
     *
     * @param string $serviceName
     * @param array $fields
     * @return Service|null
     */
    public static function getServiceWithFields(string $serviceName, array $fields): ?Service
    {
        $service = Service::firstOrCreate(
            ['name' => $serviceName],
            ['description' => $serviceName . ' Services']
        );

        foreach ($fields as $fieldData) {
            ServiceField::firstOrCreate(
                ['field_code' => $fieldData['code']],
                [
                    'service_id' => $service->id,
                    'field_name' => $fieldData['name'],
                    'base_price' => $fieldData['price'] ?? 0.00,
                    'is_active' => true,
                ]
            );
        }

        return $service;
    }
}
