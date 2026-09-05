<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;


class SearchResultsController
{
    #[Route('/search-results', methods: ['GET'])]
    public function getSearchResults(Connection $connection, Request $request): JsonResponse

    {
        $allproviderIds = $connection->fetchFirstColumn(
            "SELECT id FROM Providers"
        );

        $searchTerm       = $request->query->get('searchTerm', '');
        $providerIds      = $request->query->all('providerIds') ?: $allproviderIds; 
        $coveredMinPrice  = $request->query->get('coveredMinPrice', 0);
        $coveredMaxPrice  = $request->query->get('coveredMaxPrice', 99999999);
        $monthlyPayMin    = $request->query->get('monthlyPayMin', 0);
        $monthlyPayMax    = $request->query->get('monthlyPayMax', 99999999);
        $durationMin      = $request->query->get('durationMin', 0);
        $durationMax      = $request->query->get('durationMax', 99999999);
        $pageNumber       = $request->query->get('pageNumber', 1);
        $pageSize         = $request->query->get('pageSize', 20);

        $offsetCount = ($pageNumber - 1) * $pageSize;

        try {
            $sql = "
                SELECT O.name,
                    O.id,
                    O.link
                    O.total_covered_amount,
                    O.monthly_payment,
                    O.duration,
                    P.name AS ProviderName,
                    T.name AS InsuranceType,
                    O.p_id AS activePIDs
                FROM Offers O
                INNER JOIN Providers P ON O.p_id = P.id
                INNER JOIN Types T ON O.type_id = T.id
                WHERE (P.name LIKE :searchTerm OR T.name LIKE :searchTerm)
                AND O.p_id IN (:providerIds)
                AND O.total_covered_amount BETWEEN :coveredMin AND :coveredMax
                AND O.monthly_payment BETWEEN :monthlyMin AND :monthlyMax
                AND O.duration BETWEEN :durationMin AND :durationMax
                ORDER BY O.total_covered_amount DESC
                LIMIT :limit OFFSET :offset
            ";


            $result = $connection->executeQuery(
                $sql,
                [
                    'searchTerm'   => '%' . $searchTerm . '%',
                    'providerIds'  => $providerIds,
                    'coveredMin'   => $coveredMinPrice,
                    'coveredMax'   => $coveredMaxPrice,
                    'monthlyMin'   => $monthlyPayMin,
                    'monthlyMax'   => $monthlyPayMax,
                    'durationMin'  => $durationMin,
                    'durationMax'  => $durationMax,
                    'limit'        => $pageSize,
                    'offset'       => $offsetCount,
                ],
                [
                    'providerIds' => \Doctrine\DBAL\ArrayParameterType::INTEGER,
                    'limit'       => \Doctrine\DBAL\ParameterType::INTEGER,
                    'offset'      => \Doctrine\DBAL\ParameterType::INTEGER,
                ]
            );

            $rows = $result->fetchAllAssociative();
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => "Operation failed unexpectedly: $e"],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return new JsonResponse(
            ['data' => $rows],
            Response::HTTP_OK
        );
    }
}