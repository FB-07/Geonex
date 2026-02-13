<?php

namespace App\Support;

class Sections
{
    public static function all(): array
    {
        return [
            __('navbar.p_interest')=>[
                [
                    'name' => __('navbar.p_interest'),
                    'icon' => 'map-pin',
                    'link' => 'interest',
                    'sub-routes' => [],
                ],
            ],
            __('navbar.users') => [
                [
                    'name' => __('navbar.users'),
                    'icon' => 'users',
                    'link' => 'users',
                    'sub-routes' => [],
                ],
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
                            'link' => 'tec-places',
                            'sub-routes' => []
                        ],
                        [
                            'name' => __('navbar.distri'),
                            'icon' => 'map',
                            'link' => 'tec-distritos',
                            'sub-routes' => []
                        ],
                        [
                            'name' => __('navbar.munici'),
                            'icon' => 'map',
                            'link' => 'tec-municipios',
                            'sub-routes' => []
                        ],
                        [
                            'name' => __('navbar.parish'),
                            'icon' => 'map',
                            'link' => 'tec-freguesias',
                            'sub-routes' => []
                        ],
                        [
                            'name' => __('navbar.cp'),
                            'icon' => 'envelope',
                            'link' => 'tec-cp',
                            'sub-routes' => []
                        ]
                    ],
                ],
            ],
        ];
        ;
    }
}
