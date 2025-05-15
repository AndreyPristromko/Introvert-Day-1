<?php

namespace App\Service;

class ClientService extends ApiService
{
    public function getClients(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'intrdev',
                'api' => '23bc075b710da43f0ffb50ff9e889aed',
            ],
            [
                'id' => 2,
                'name' => 'artedegrass0',
                'api' => '35v35y4u3b5fy45y4guk3y5qu4k5u45',
            ]
        ];
    }

    public function checkClients(array $client): bool
    {
        try {
            $this->config->setApiKey('key', $client['api']);
            $result = $this->api->account->info();
            return true;

        } catch (\Exception $e) {
            $this->logger->error('Ошибка при проверке доступа');
            return false;
        }
    }
} 
