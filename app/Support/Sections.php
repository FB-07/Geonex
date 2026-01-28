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
                    'link' => 'tecnical',
                    'sub-routes' => [],
                ],
            ],
        ];
        ;
    }
}
