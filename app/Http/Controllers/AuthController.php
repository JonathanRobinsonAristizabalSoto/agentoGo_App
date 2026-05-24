<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

/**
 * Controlador de autenticación: registro, inicio de sesión, obtener el usuario autenticado y cierre de sesión.
 */
class AuthController extends Controller
{
    #[OA\Post(
        path: '/auth/register',
        tags: ['Auth'],
        summary: 'Registrar nuevo usuario',
        responses: [new OA\Response(response: 201, description: 'Usuario registrado exitosamente')]
    )]
    /**
     * @OA\Post(
     *     path="/auth/register",
     *     tags={"Auth"},
     *     summary="Registrar nuevo usuario",
     *     description="Crea una nueva cuenta de usuario y retorna un token de autenticación",
     *     @OA\RequestBody(
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 schema={
     *                     @OA\Property(property="name", type="string", example="Juan Pérez"),
     *                     @OA\Property(property="email", type="string", format="email", example="juan@example.com"),
     *                     @OA\Property(property="password", type="string", format="password", minLength=8, example="password123")
     *                 }
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuario registrado exitosamente",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 schema={
     *                     @OA\Property(property="user", ref="#/components/schemas/User"),
     *                     @OA\Property(property="token", type="string", example="1|abcdef...")
     *                 }
     *             )
     *         }
     *     ),
     *     @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('default')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    #[OA\Post(
        path: '/auth/login',
        tags: ['Auth'],
        summary: 'Iniciar sesión',
        responses: [new OA\Response(response: 200, description: 'Login exitoso')]
    )]
    /**
     * @OA\Post(
     *     path="/auth/login",
     *     tags={"Auth"},
     *     summary="Iniciar sesión",
     *     description="Autentica un usuario con email y contraseña, retorna token",
     *     @OA\RequestBody(
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 schema={
     *                     @OA\Property(property="email", type="string", format="email", example="juan@example.com"),
     *                     @OA\Property(property="password", type="string", format="password", example="password123")
     *                 }
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login exitoso",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 schema={
     *                     @OA\Property(property="user", ref="#/components/schemas/User"),
     *                     @OA\Property(property="token", type="string")
     *                 }
     *             )
     *         }
     *     ),
     *     @OA\Response(response=422, description="Credenciales inválidas")
     * )
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciales inválidas.'],
            ]);
        }

        $token = $user->createToken('default')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    #[OA\Get(
        path: '/auth/me',
        tags: ['Auth'],
        summary: 'Obtener perfil del usuario autenticado',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Perfil del usuario')]
    )]
    /**
     * @OA\Get(
     *     path="/auth/me",
     *     tags={"Auth"},
     *     summary="Obtener perfil del usuario autenticado",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Perfil del usuario",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 schema={
     *                     @OA\Property(property="user", ref="#/components/schemas/User")
     *                 }
     *             )
     *         }
     *     ),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    #[OA\Post(
        path: '/auth/logout',
        tags: ['Auth'],
        summary: 'Cerrar sesión',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 204, description: 'Sesión cerrada')]
    )]
    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     tags={"Auth"},
     *     summary="Cerrar sesión",
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=204, description="Sesión cerrada"),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'ok' => true,
        ]);
    }
}