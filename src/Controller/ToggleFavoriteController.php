<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;


class ToggleFavoriteController
{
    #[Route('/toggle-favorite', methods: ['POST'])]
    public function setFavorites(Connection $connection, Request $request): JsonResponse
    {
        $data = $request->toArray();
        $userId = $data['userId'];
        $offerId = $data['offerId'];

        try {
            $connection->executeStatement(
                'INSERT INTO Favorites(user_id, offer_id) VALUES (?,?)',
                [$userId, $offerId]
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'Operation failed unexpectedly'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return new JsonResponse(
            ['data' => TRUE],
            Response::HTTP_OK
        );
    }
}