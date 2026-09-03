<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;


class AllProvidersController
{
    #[Route('/all-providers', methods: ['GET'])]
    public function getAllProviders(Connection $connection): JsonResponse
    {
        try {
            $providers = $connection->fetchAllAssociative(
                'SELECT id, name FROM Providers'
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'Operation failed unexpectedly'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return new JsonResponse(
            ['data' => $providers],
            Response::HTTP_OK
        );
    }
}