<?php

return [
    'id' => 'ID',
    'name' => 'Name',
    'address' => 'Address',
    'district' => 'District',
    'municipality' => 'Municipality',
    'parish' => 'Parish',
    'lat' => 'Lat',
    'lng' => 'Lng',
    'actions' => 'Actions',
    'search' => 'Search...',
    'all' => 'All',
    'clear' => 'Clear',
    'edit' => 'Edit',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'select' => 'Select...',
    'code' => 'Zip code',
    'empty' => 'No results.',
    'confirmDelete' => 'Are you sure you want to delete this record?',

    'flash' => [
        'distrito' => [
            'updated' => 'District updated successfully.',
            'deleted' => 'District deleted successfully.',
            'inUse' => 'Cannot delete: the district has municipalities.',
        ],
        'municipio' => [
            'updated' => 'Municipality updated successfully.',
            'deleted' => 'Municipality deleted successfully.',
            'inUse' => 'Cannot delete: the municipality has parishes.',
            'duplicate' => 'A municipality with this name already exists in this district.',
        ],
        'freguesia' => [
            'updated' => 'Parish updated successfully.',
            'deleted' => 'Parish deleted successfully.',
            'inUse' => 'Cannot delete: the parish has zip codes.',
            'duplicate' => 'A parish with this name already exists in this municipality.',
        ],
        'codigo_postal' => [
            'updated' => 'Zip code updated successfully.',
            'deleted' => 'Zip code deleted successfully.',
        ],
        'place' => [
            'updated' => 'Place updated successfully.',
            'deleted' => 'Place deleted successfully.',
        ],
    ],
];
