<?php

namespace App\Application\UseCases;

use App\Domain\Entities\Sale;
use App\Domain\Repositories\SaleRepositoryInterface;
use App\Application\Services\PDFGeneratorService;
use App\Infrastructure\Messaging\EventPublisher;

class CreateSaleUseCase
{
    private SaleRepositoryInterface $saleRepository;
    private PDFGeneratorService $pdfGenerator;
    private EventPublisher $eventPublisher;

    public function __construct(
        SaleRepositoryInterface $saleRepository,
        PDFGeneratorService $pdfGenerator,
        EventPublisher $eventPublisher
    ) {
        $this->saleRepository = $saleRepository;
        $this->pdfGenerator = $pdfGenerator;
        $this->eventPublisher = $eventPublisher;
    }

    public function execute(
        string $customerId,
        string $vehicleId,
        string $reservationId,
        string $paymentId,
        float $salePrice,
        array $customerData,
        array $vehicleData
    ): array {
        // Verificar se já existe venda para esta reserva
        $existingSale = $this->saleRepository->findByReservationId($reservationId);
        if ($existingSale) {
            throw new \Exception('Já existe uma venda para esta reserva', 409);
        }

        // Verificar se já existe venda para este pagamento
        $existingPaymentSale = $this->saleRepository->findByPaymentId($paymentId);
        if ($existingPaymentSale) {
            throw new \Exception('Já existe uma venda para este pagamento', 409);
        }

        // Criar nova venda
        $sale = new Sale(
            $customerId,
            $vehicleId,
            $reservationId,
            $paymentId,
            $salePrice
        );

        // Salvar venda
        if (!$this->saleRepository->save($sale)) {
            throw new \Exception('Erro ao criar venda', 500);
        }

        try {
            // Gerar documentos PDF
            $contractFilename = $this->pdfGenerator->generateSaleContract(
                $sale->toArray(),
                $customerData,
                $vehicleData
            );

            $invoiceFilename = $this->pdfGenerator->generateInvoice(
                $sale->toArray(),
                $customerData,
                $vehicleData
            );

            // Atualizar venda com os caminhos dos PDFs
            $sale->setContractPdfPath($contractFilename);
            $sale->setInvoicePdfPath($invoiceFilename);
            $sale->complete();

            // Salvar alterações
            $this->saleRepository->update($sale);

            // Publicar evento de venda criada
            $this->eventPublisher->publish('sale.created', [
                'sale_id' => $sale->getId(),
                'customer_id' => $sale->getCustomerId(),
                'vehicle_id' => $sale->getVehicleId(),
                'reservation_id' => $sale->getReservationId(),
                'payment_id' => $sale->getPaymentId(),
                'sale_price' => $sale->getSalePrice(),
                'contract_pdf' => $contractFilename,
                'invoice_pdf' => $invoiceFilename,
                'timestamp' => date('Y-m-d H:i:s')
            ]);

            return [
                'sale' => $sale->toArray(),
                'documents' => [
                    'contract' => $contractFilename,
                    'invoice' => $invoiceFilename
                ],
                'message' => 'Venda criada com sucesso'
            ];

        } catch (\Exception $e) {
            // Em caso de erro na geração dos PDFs, cancelar a venda
            $sale->cancel();
            $this->saleRepository->update($sale);

            throw new \Exception('Erro ao gerar documentos da venda: ' . $e->getMessage(), 500);
        }
    }
}

