<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
	title: 'AgentoGo API',
	version: '0.3.0',
	description: 'API REST multi-tenant con autenticación, auditoría y gestión de negocios',
	contact: new OA\Contact(name: 'AgentoGo Support', email: 'support@agentogo.com')
)]
#[OA\Server(
	url: 'http://localhost:8000/api',
	description: 'Servidor de desarrollo'
)]
#[OA\SecurityScheme(
	securityScheme: 'sanctum',
	type: 'http',
	scheme: 'bearer',
	bearerFormat: 'token',
	description: 'Token de autenticación Sanctum'
)]
#[OA\Components(
	schemas: [
		new OA\Schema(
			schema: 'Error403',
			type: 'object',
			properties: [
				new OA\Property(property: 'code', type: 'integer', example: 403),
				new OA\Property(property: 'message', type: 'string', example: 'No autorizado')
			]
			, example: ['code' => 403, 'message' => 'No tiene permisos para realizar esta acción.']
		),
		new OA\Schema(
			schema: 'ValidationError',
			type: 'object',
			properties: [
				new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
				new OA\Property(property: 'errors', type: 'object', description: 'Mapa de errores por campo')
			]
			, example: ['message' => 'The given data was invalid.', 'errors' => ['name' => ['El campo nombre es requerido.'], 'email' => ['El email debe ser una dirección válida.']]]
		)
	]
)]
final class Documentation
{
}