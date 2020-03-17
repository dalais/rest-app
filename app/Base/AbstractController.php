<?php

namespace App\Base;


use Symfony\Component\HttpFoundation\JsonResponse;

abstract class AbstractController
{
    public function jsonResponse(string $message, $data = [], $status = 0)
    {
        $response = new JsonResponse([
            'error' => $status,
            'message' => $message,
            'data' => $data,
        ]);
        return $response->send();
    }

    public function notFound()
    {
        $response = new JsonResponse([
            'statusCode' => 404,
            'message' => 'Not Found'
        ]);
        return $response->send();
    }

    public function notAllowedMethod()
    {
        $response = new JsonResponse([
            'statusCode' => 405,
            'message' => 'Method Not Allowed'
        ]);
        return $response->send();
    }
}