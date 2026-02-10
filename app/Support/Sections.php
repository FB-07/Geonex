<?php

namespace App\Support;

class Sections
{
    public static function all(): array
    {
        return [
            __('navbar.users') => [
                [
                    'name' => __('navbar.users'),
                    'icon' => 'users',
                    'link' => 'users',
                    'sub-routes' => [],
                ],
                // [
                //     'name' => __('navbar.operador'),
                //     'icon' => 'user',
                //     'link' => '#',
                //     'sub-routes' => [],
                // ],
            ],
            __('navbar.features') => [
                [
                    'name' => __('navbar.technicalarea'),
                    'icon' => 'wrench',
                    'link' => '',
                    'sub-routes' => [
                        [
                            'name' => __('navbar.places'),
                            'icon' => 'globe-europe-africa',
                            'link' => 'tecnical-places',
                            'sub-routes' => []
                        ],
                        [
                            'name' => __('navbar.distri'),
                            'icon' => 'map',
                            'link' => 'tecnical-distritos',
                            'sub-routes' => []
                        ],
                        [
                            'name' => __('navbar.munici'),
                            'icon' => 'map',
                            'link' => 'tecnical-municipios',
                            'sub-routes' => []
                        ],
                        [
                            'name' => __('navbar.parish'),
                            'icon' => 'map',
                            'link' => 'tecnical-freguesias',
                            'sub-routes' => []
                        ]
                    ],
                ],
                [
                    'name' => __('navbar.technicalarea'),
                    'icon' => 'wrench',
                    'link' => '',
                    'sub-routes' => [],
                ],
            ],
        ];
        ;
    }
}
