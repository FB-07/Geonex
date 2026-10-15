<?php

return [
    'id' => 'ID',
    'name' => 'Nome',
    'address' => 'Morada',
    'district' => 'Distrito',
    'municipality' => 'Município',
    'parish' => 'Freguesia',
    'lat' => 'Lat',
    'lng' => 'Lng',
    'actions' => 'Ações',
    'search' => 'Pesquisar...',
    'all' => 'Todos',
    'clear' => 'Limpar',
    'edit' => 'Editar',
    'save' => 'Guardar',
    'cancel' => 'Cancelar',
    'select' => 'Selecionar...',
    'code' => 'Código postal',
    'empty' => 'Sem resultados.',
    'confirmDelete' => 'Tem a certeza que quer apagar este registo?',

    'flash' => [
        'distrito' => [
            'updated' => 'Distrito atualizado com sucesso.',
            'deleted' => 'Distrito apagado com sucesso.',
            'inUse' => 'Não é possível apagar: o distrito tem municípios associados.',
        ],
        'municipio' => [
            'updated' => 'Município atualizado com sucesso.',
            'deleted' => 'Município apagado com sucesso.',
            'inUse' => 'Não é possível apagar: o município tem freguesias associadas.',
            'duplicate' => 'Já existe um município com esse nome neste distrito.',
        ],
        'freguesia' => [
            'updated' => 'Freguesia atualizada com sucesso.',
            'deleted' => 'Freguesia apagada com sucesso.',
            'inUse' => 'Não é possível apagar: a freguesia tem códigos postais associados.',
            'duplicate' => 'Já existe uma freguesia com esse nome neste município.',
        ],
        'codigo_postal' => [
            'updated' => 'Código postal atualizado com sucesso.',
            'deleted' => 'Código postal apagado com sucesso.',
        ],
        'place' => [
            'updated' => 'Local atualizado com sucesso.',
            'deleted' => 'Local apagado com sucesso.',
        ],
    ],
];
