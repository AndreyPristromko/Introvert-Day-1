<?php

namespace App\Service;

use Introvert\ApiClient;
use Introvert\Configuration;
use Psr\Log\LoggerInterface;

class LeadsService extends ApiService
{
    public function __construct(ApiClient $api, Configuration $config, LoggerInterface $logger)
    {
        parent::__construct($api, $config, $logger);
    }

    public function getDealsSum(int $dateFrom, int $dateTo): float
    {
        try {
            $offset = 0;
            $totalSum = 0;
            $flag = true;

            while ($flag) {
                $response = $this->api->lead->getAll(null, [142], null, null, 25, $offset);
                
                if (empty($response['result'])) {
                    $flag = false;
                } else {
                    foreach ($response['result'] as $deal) {
                        if ($deal['date_create'] <= $dateFrom && $deal['date_close'] >= $dateTo) {
                            $totalSum += (float)$deal['price'];
                        }
                    }
                    $offset += 25;
                }
                
                usleep(200000);
            }

            $this->logger->info('Обработаны все сделки', ['total_sum' => $totalSum]);
            return $totalSum;

        } catch (\Exception $e) {
            $this->logger->error('Ошибка при получении сделок', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
} 
