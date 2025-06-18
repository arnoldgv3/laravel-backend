<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 * version="1.0.0",
 * title="E-commerce API Documentation",
 * description="Documentación interactiva para la API del sistema de E-commerce, desarrollada para la prueba técnica de Zenova Digital.",
 * @OA\Contact(
 * email="leidy.palomino@example.com"
 * )
 * )
 *
 * @OA\Server(
 * url=L5_SWAGGER_CONST_HOST,
 * description="Servidor Principal de la API"
 * )
 *
 * @OA\SecurityScheme(
 * securityScheme="bearerAuth",
 * type="http",
 * scheme="bearer",
 * bearerFormat="JWT",
 * description="Autenticación con Token JWT (Bearer Token)"
 * )
 */

abstract class Controller
{
    //
}
