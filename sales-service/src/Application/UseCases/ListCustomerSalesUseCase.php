<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\Repositories\SaleRepositoryInterface;

class ListCustomerSalesUseCase
{
    private SaleRepositoryInterface $saleRepository;

    public function __construct(SaleRepositoryInterface $saleRepository)
    {
        $this->saleRepository = $saleRepository;
    }

    public function execute(string $customerId): array
    {
        $sales = $this->saleRepository->findByCustomerId($customerId);

        return [
            'sales' => array_map(function ($sale) {
                return $sale->toArray();
            }, $sales),
            'total' => count($sales),
        ];
    }
}
