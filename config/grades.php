<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Calificaciones
    |--------------------------------------------------------------------------
    |
    | Configuraciones relacionadas con el sistema de calificaciones,
    | consolidación y validaciones por etapa.
    |
    */

    /**
     * Número mínimo de jueces requeridos por etapa
     * 
     * Se valida antes de consolidar las calificaciones.
     * El nombre de la etapa debe coincidir con el valor en la columna 'nombre' de la tabla 'etapas'.
     */
    'min_jueces' => [
        'institucional' => 3,
        'circuital' => 5,
        'regional' => 5,
    ],

    /**
     * Ponderaciones por defecto si no se encuentran rúbricas
     * 
     * Usado como fallback en consolidación de calificaciones.
     */
    'default_ponderaciones' => [
        'escrita' => 0.60,
        'oral' => 0.40,
    ],
];
