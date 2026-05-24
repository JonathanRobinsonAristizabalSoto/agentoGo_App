<?php
// Script para configurar L5-Swagger

$base_path = __DIR__;
chdir($base_path);

// Crear directorio de configuración si no existe
if (!is_dir('config/l5-swagger')) {
    mkdir('config/l5-swagger', 0755, true);
}

// Contenido de l5-swagger.php
$config_content = <<<'PHP'
<?php

return [
    'default' => 'default',
    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'AgentoGo API',
            ],
            'routes' => [
                /*
                 * Route for accessing api documentation interface, e.g. `/api/documentation`
                */
                'api' => 'api/documentation',
            ],
            'paths' => [
                /*
                 * Absolute path to location of controller files to be documented. Edit this set the controllers
                 * you want to be included in the documentation, wildcards supported.
                 */
                'app/Http/Controllers',

                /*
                 * Controllers & methods to exclude from documentation
                */
                'excluded_methods' => ['index'],
            ],
            'security' => [
                /*
                 * Examples of Security schemes
                */
            ],
        ],
    ],
    'defaults' => [
        'models_dir' => 'Models',
    ],
];
PHP;

file_put_contents('config/l5-swagger.php', $config_content);
echo "✓ Configuración L5-Swagger creada\n";

// Crear directorio de documentación swagger-ui
if (!is_dir('public/api')) {
    mkdir('public/api', 0755, true);
}

echo "✓ Directorio de documentación creado\n";
?>
