<?php

namespace App\Application\UseCases;

use App\Domain\Repositories\SaleRepositoryInterface;

class GetSaleDetailsUseCase
{
    private SaleRepositoryInterface $saleRepository;

    public function __construct(SaleRepositoryInterface $saleRepository)
    {
        $this->saleRepository = $saleRepository;
    }

    public function execute(string $saleId, string $customerId): array
    {
        $sale = $this->saleRepository->findById($saleId);
        
        if (!$sale) {
            throw new \Exception('Venda não encontrada', 404);
        }

        if ($sale->getCustomerId() !== $customerId) {
            throw new \Exception('Acesso negado. Esta venda não pertence ao cliente', 403);
        }

        if ($sale->isDeleted()) {
            throw new \Exception('Venda não disponível', 410);
        }

        return $sale->toArray();
    }
}

