<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\ClientService;
use App\Service\DealsService;

class DealsController extends AbstractController
{
    private $clientService;
    private $dealsService;
    private $logger;

    public function __construct(
        ClientService $clientService, 
        DealsService $dealsService,
        LoggerInterface $logger
    ) {
        $this->clientService = $clientService;
        $this->dealsService = $dealsService;
        $this->logger = $logger;
    }

    public function getDealsStatistics(Request $request): Response
    {
        try {
            $dateFrom = $request->query->get('date_from');
            $dateTo = $request->query->get('date_to');
            
            if (!$dateFrom || !$dateTo) {
                throw new \Exception('Не указан период');
            }

            $clients = $this->clientService->getClients();
            $totalSum = 0;
            $statistics = [];

            foreach ($clients as $client) {
                if ($result = $this->clientService->checkClients($client)) {
                    $this->logger->info($result);
                    $clientDealsSum = $this->dealsService->getDealsSum($dateFrom, $dateTo);
                    
                    $statistics[] = [
                        'id' => $client['id'],
                        'name' => $client['name'],
                        'sum' => $clientDealsSum
                    ];
                    
                    $totalSum += $clientDealsSum;
                    $this->logger->info('Обработан клиент, доступ есть',  ['client_id' => $client['id'], 'deals_sum' => $clientDealsSum]);
                    usleep(200000);
                } else {
                    $clientDealsSum = 0;
                    $statistics[] = [
                        'id' => $client['id'],
                        'name' => $client['name'],
                        'sum' => $clientDealsSum
                    ];
                    $this->logger->info('Обработан клиент, доступа нет', ['client_id' => $client['id'], 'deals_sum' => $clientDealsSum]);
                    usleep(200000);
                }
            }

            return $this->render('deals/statistics.html.twig', [
                'statistics' => $statistics,
                'total_sum' => $totalSum,
                'date_from' => date('Y-m-d H:i:s', (int)$dateFrom),
                'date_to' => date('Y-m-d H:i:s', (int)$dateTo)
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Ошибка при работе скрипта', [
                'error' => $e->getMessage()
            ]);
            return  new Response ('Ошибка при работе скрипта');
        }
    }
} 