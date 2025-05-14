<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\ClientService;
use App\Service\LeadsService;

class LeadsController extends AbstractController
{
    private $clientService;
    private $leadsService;
    private $logger;

    public function __construct(
        ClientService $clientService, 
        LeadsService $leadsService,
        LoggerInterface $logger
    ) {
        $this->clientService = $clientService;
        $this->leadsService = $leadsService;
        $this->logger = $logger;
    }

    public function getLeadsStatistics(Request $request): Response
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
                if ($this->clientService->checkClients($client) {
                    $clientLeadsSum = $this->leadsService->getLeadsSum($dateFrom, $dateTo);
                    
                    $statistics[] = [
                        'id' => $client['id'],
                        'name' => $client['name'],
                        'sum' => $clientLeadsSum
                    ];
                    
                    $totalSum += $clientLeadsSum;
                    $this->logger->info('Обработан клиент, доступ есть',  ['client_id' => $client['id'], 'leads_sum' => $clientLeadsSum]);
                    usleep(200000);
                } else {
                    $clientLeadsSum = 0;
                    $statistics[] = [
                        'id' => $client['id'],
                        'name' => $client['name'],
                        'sum' => $clientLeadsSum
                    ];
                    $this->logger->info('Обработан клиент, доступа нет', ['client_id' => $client['id'], 'leads_sum' => $clientLeadsSum]);
                    usleep(200000);
                }
            }

            return $this->render('leads/statistics.html.twig', [
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
