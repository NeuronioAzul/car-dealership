<?php

declare(strict_types=1);

namespace App\Application\Services;

use FPDF;

class PDFGeneratorService
{
    private string $storagePath;

    public function __construct()
    {
        $this->storagePath = $_ENV['PDF_STORAGE_PATH'] ?? '/var/www/html/storage/pdfs';

        // Criar diretório se não existir
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }

    public function generateSaleContract(array $saleData, array $customerData, array $vehicleData): string
    {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);

        // Cabeçalho
        $pdf->Cell(0, 10, 'CONTRATO DE COMPRA E VENDA DE VEÍCULO', 0, 1, 'C');
        $pdf->Ln(10);

        // Dados da concessionária
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'VENDEDOR:', 0, 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, 'AutoDealer Concessionária LTDA', 0, 1);
        $pdf->Cell(0, 6, 'CNPJ: 12.345.678/0001-90', 0, 1);
        $pdf->Cell(0, 6, 'Endereço: Av. Principal, 1000 - Centro - São Paulo/SP', 0, 1);
        $pdf->Cell(0, 6, 'Telefone: (11) 1234-5678', 0, 1);
        $pdf->Ln(5);

        // Dados do comprador
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'COMPRADOR:', 0, 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, 'Nome: ' . $customerData['name'], 0, 1);
        $pdf->Cell(0, 6, 'CPF: ' . $customerData['cpf'], 0, 1);
        $pdf->Cell(0, 6, 'Email: ' . $customerData['email'], 0, 1);
        $pdf->Cell(0, 6, 'Telefone: ' . $customerData['phone'], 0, 1);

        if (isset($customerData['address'])) {
            $pdf->Cell(0, 6, 'Endereço: ' . $customerData['address'], 0, 1);
        }
        $pdf->Ln(5);

        // Dados do veículo
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'VEÍCULO:', 0, 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, 'Marca/Modelo: ' . $vehicleData['brand'] . ' ' . $vehicleData['model'], 0, 1);
        $pdf->Cell(0, 6, 'Ano de Fabricação: ' . $vehicleData['manufacturing_year'], 0, 1);
        $pdf->Cell(0, 6, 'Ano do Modelo: ' . $vehicleData['model_year'], 0, 1);
        $pdf->Cell(0, 6, 'Cor: ' . $vehicleData['color'], 0, 1);
        $pdf->Cell(0, 6, 'Quilometragem: ' . number_format($vehicleData['mileage']) . ' km', 0, 1);
        $pdf->Cell(0, 6, 'Combustível: ' . $vehicleData['fuel_type'], 0, 1);
        $pdf->Cell(0, 6, 'Transmissão: ' . $vehicleData['transmission_type'], 0, 1);
        $pdf->Cell(0, 6, 'Final da Placa: ' . $vehicleData['license_plate_end'], 0, 1);
        $pdf->Ln(5);

        // Valor da venda
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'VALOR DA VENDA:', 0, 1);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 8, 'R$ ' . number_format($saleData['sale_price'], 2, ',', '.'), 0, 1);
        $pdf->Ln(5);

        // Cláusulas do contrato
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'CLÁUSULAS:', 0, 1);
        $pdf->SetFont('Arial', '', 9);

        $clauses = [
            '1. O veículo descrito acima é vendido no estado em que se encontra.',
            '2. O comprador declara ter examinado o veículo e estar ciente de suas condições.',
            '3. A transferência de propriedade será efetivada após a quitação total do valor.',
            '4. O vendedor se responsabiliza pela documentação até a data da venda.',
            '5. Eventuais multas anteriores à data da venda são de responsabilidade do vendedor.',
            '6. Este contrato é válido para todos os fins de direito.',
        ];

        foreach ($clauses as $clause) {
            $pdf->Cell(0, 5, $clause, 0, 1);
        }

        $pdf->Ln(10);

        // Data e assinaturas
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, 'São Paulo, ' . date('d/m/Y'), 0, 1);
        $pdf->Ln(15);

        $pdf->Cell(90, 6, '_________________________________', 0, 0, 'C');
        $pdf->Cell(90, 6, '_________________________________', 0, 1, 'C');
        $pdf->Cell(90, 6, 'Vendedor', 0, 0, 'C');
        $pdf->Cell(90, 6, 'Comprador', 0, 1, 'C');

        // Salvar arquivo
        $filename = 'contrato_' . $saleData['id'] . '_' . date('YmdHis') . '.pdf';
        $filepath = $this->storagePath . '/' . $filename;
        $pdf->Output('F', $filepath);

        return $filename;
    }

    public function generateInvoice(array $saleData, array $customerData, array $vehicleData): string
    {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);

        // Cabeçalho
        $pdf->Cell(0, 10, 'NOTA FISCAL DE VENDA', 0, 1, 'C');
        $pdf->Ln(5);

        // Número da nota fiscal
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'NF Nº: ' . str_pad(substr($saleData['id'], -8), 8, '0', STR_PAD_LEFT), 0, 1, 'R');
        $pdf->Cell(0, 8, 'Data de Emissão: ' . date('d/m/Y H:i:s'), 0, 1, 'R');
        $pdf->Ln(5);

        // Dados da empresa
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'DADOS DA EMPRESA:', 0, 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, 'AutoDealer Concessionária LTDA', 0, 1);
        $pdf->Cell(0, 6, 'CNPJ: 12.345.678/0001-90', 0, 1);
        $pdf->Cell(0, 6, 'IE: 123.456.789.012', 0, 1);
        $pdf->Cell(0, 6, 'Endereço: Av. Principal, 1000 - Centro - São Paulo/SP - CEP: 01000-000', 0, 1);
        $pdf->Ln(5);

        // Dados do cliente
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'DADOS DO CLIENTE:', 0, 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, 'Nome: ' . $customerData['name'], 0, 1);
        $pdf->Cell(0, 6, 'CPF: ' . $customerData['cpf'], 0, 1);
        $pdf->Cell(0, 6, 'Email: ' . $customerData['email'], 0, 1);
        $pdf->Ln(5);

        // Tabela de itens
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(20, 8, 'Item', 1, 0, 'C');
        $pdf->Cell(80, 8, 'Descrição', 1, 0, 'C');
        $pdf->Cell(20, 8, 'Qtd', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Valor Unit.', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Valor Total', 1, 1, 'C');

        $pdf->SetFont('Arial', '', 9);
        $description = $vehicleData['brand'] . ' ' . $vehicleData['model'] . ' ' . $vehicleData['model_year'];
        $pdf->Cell(20, 8, '001', 1, 0, 'C');
        $pdf->Cell(80, 8, $description, 1, 0, 'L');
        $pdf->Cell(20, 8, '1', 1, 0, 'C');
        $pdf->Cell(30, 8, 'R$ ' . number_format($saleData['sale_price'], 2, ',', '.'), 1, 0, 'R');
        $pdf->Cell(30, 8, 'R$ ' . number_format($saleData['sale_price'], 2, ',', '.'), 1, 1, 'R');

        // Total
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(150, 8, 'TOTAL GERAL:', 1, 0, 'R');
        $pdf->Cell(30, 8, 'R$ ' . number_format($saleData['sale_price'], 2, ',', '.'), 1, 1, 'R');

        $pdf->Ln(10);

        // Informações adicionais
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(0, 5, 'Forma de Pagamento: À Vista', 0, 1);
        $pdf->Cell(0, 5, 'Observações: Venda de veículo usado conforme descrito.', 0, 1);

        $pdf->Ln(10);

        // Rodapé
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 5, 'Esta nota fiscal foi gerada eletronicamente e é válida sem assinatura.', 0, 1, 'C');

        // Salvar arquivo
        $filename = 'nota_fiscal_' . $saleData['id'] . '_' . date('YmdHis') . '.pdf';
        $filepath = $this->storagePath . '/' . $filename;
        $pdf->Output('F', $filepath);

        return $filename;
    }

    public function getFilePath(string $filename): string
    {
        return $this->storagePath . '/' . $filename;
    }
}
