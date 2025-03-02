<?php

namespace App\Services;

use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\RegisterModel;

class ReportService{
    protected $registerModel; 

    public function __construct(){
        $this->registerModel = new RegisterModel(); // Instancia a Model
    }

    public function generateReport($format, $startDate, $endDate, $saveToFile = false){
        // Ajustar o formato das datas
        $startDate .= ' 00:00:00'; // Concatenar a hora 00:00:00 para a data de inicio, assim as datas serão inclusivas do dia completo
        $endDate .= ' 23:59:59'; // O mesmo que a anterior

        // Buscar registros no banco
        $data = $this->registerModel
            ->where('date >=', $startDate)
            ->where('date <=', $endDate)
            ->findAll();

        // Calcular saldo dinâmico
        $balance = $this->registerModel->calculateBalance($startDate, $endDate);

        $fileName = 'relatorio_' . date('Y-m-d_H-i-s') . '.' . ($format === 'excel' ? 'xlsx' : $format); // Nome do arquivo
        $filePath = WRITEPATH . 'reports/' . $fileName; // Caminho do arquivo

        // Criar a pasta reports se não existir
        if (!is_dir(WRITEPATH . 'reports')) {
            mkdir(WRITEPATH . 'reports', 0755, true);
        }

        // Gerar o relatório conforme o formato
        switch (strtolower($format)) {
            case 'csv':
                return $this->generateCsv($data, $balance, $saveToFile ? $filePath : null);
            case 'pdf':
                return $this->generatePdf($data, $balance, $startDate, $endDate, $saveToFile ? $filePath : null);
            case 'excel':
                return $this->generateExcel($data, $balance, $saveToFile ? $filePath : null);
            default:
                return 'Formato inválido.';
        }
    }

    public function generateCsv($data, $balance, $filePath = null){
        $output = fopen($filePath ?? 'php://output', 'w');
        fputcsv($output, ['ID', 'Título', 'Valor', 'Tipo', 'Data'], ';');

        // Garantindo que type nunca seja NULL
        foreach ($data as $register) {
            $type = ($register['title'] === 'Saldo Final') ? '' : match (strtolower($register['type'])) {
                'income' => 'Entrada',
                'expense' => 'Saída',
                default => 'Desconhecido',
            };

            // Preenchendo os dados corretamente
            $value = isset($register['value']) && is_numeric($register['value']) ? 'R$ '  . number_format($register['value'], 2, ',', '.') : '';

            fputcsv($output, [
                $register['id'] ?? '',
                $register['title'] ?? '',
                $value,
                $type,
                !empty($register['date']) ? date('d/m/Y H:i:s', strtotime($register['date'])) : ''
            ], ';');
        }


        if ($balance == 0) {
            // Se saldo final for 0, coloca o valor como 0, mas deixa as colunas Tipo e Data vazias
            fputcsv($output, ['Saldo Final', '', 'R$ 0,00', '', ''], ';');
        } 
        
        else {
            // Caso contrário, imprime o saldo com o valor formatado e Tipo vazio
            fputcsv($output, ['Saldo Final', '', 'R$ ' . number_format($balance, 2, ',', '.'), '', ''], ';');
        }

        fclose($output); // Fecha o arquivo
     
        // Se filePath for fornecido, retorna o caminho do arquivo
        if ($filePath) {
            return $filePath;
        }

        header('Content-Type: text/csv'); // Define o tipo de conteúdo como CSV
        header('Content-Disposition: attachment; filename="relatorio.csv"'); // Define o nome do arquivo de saída
        exit; // Encerra o script
    }

    public function generatePdf($data, $balance, $startDate, $endDate, $filePath = null){
        $html = '<h1>Relatório Financeiro</h1>';
        $html .= '<p>Período: ' . $startDate . ' até ' . $endDate . '</p>';
        $html .= '<table border="1" width="100%" style="border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Valor</th>
                            <th>Tipo</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>';

        foreach ($data as $register) {
            $html .= '<tr>
                <td>' . $register['id'] . '</td>
                <td>' . $register['title'] . '</td>
                <td>' . 'R$ '. number_format($register['value'], 2, ',', '.') . '</td>
                <td>' . ($register['type'] === 'income' ? 'Entrada' : 'Saída') . '</td>
                <td>' . date('d/m/Y H:i:s', strtotime($register['date'])) . '</td>
            </tr>';
        }

        $html .= '</tbody></table>';
        $html .= '<h3>Saldo Final: R$ ' . number_format($balance, 2, ',', '.') . '</h3>';

        $pdf = new Dompdf(); // Cria um novo objeto Dompdf
        $pdf->loadHtml($html); // Carrega o HTML no objeto Dompdf
        $pdf->setPaper('A4', 'landscape'); // Define o papel como A4 e orientação horizontal
        $pdf->render(); // Gera o PDF

        // Se filePath for fornecido, retorna o caminho do arquivo
        if ($filePath) {
            file_put_contents($filePath, $pdf->output());
            return $filePath;
        }

        $pdf->stream('relatorio_financeiro.pdf', ['Attachment' => false]); // Define o nome do arquivo de saída
        exit; // Encerra o script
    }
    
    public function generateExcel($data, $balance, $filePath = null) {
            $spreadsheet = new Spreadsheet(); // Cria um novo objeto Spreadsheet
            $sheet = $spreadsheet->getActiveSheet(); // Pega a aba ativa do Spreadsheet
    
            // Cabeçalhos
            $sheet->setCellValue('A1', 'ID');
            $sheet->setCellValue('B1', 'Título');
            $sheet->setCellValue('C1', 'Valor');
            $sheet->setCellValue('D1', 'Tipo');
            $sheet->setCellValue('E1', 'Data');
    
            // Inserindo dados
            $row = 2; // Começa na linha 2 (depois dos cabeçalhos)
            foreach ($data as $register) {
                $type = $register['type'] === 'income' ? 'Entrada' : 'Saída';
    
                $sheet->setCellValue('A' . $row, $register['id']);
                $sheet->setCellValue('B' . $row, $register['title']);
                $sheet->setCellValue('C' . $row, 'R$ ' . number_format($register['value'], 2, ',', '.'));
                $sheet->setCellValue('D' . $row, $type);
                $sheet->setCellValue('E' . $row, date('d/m/Y H:i:s', strtotime($register['date'])));
                $row++;
            }
    
            // Adicionar saldo final
            $sheet->setCellValue('A' . $row, 'Saldo Final:');
            $sheet->setCellValue('C' . $row, 'R$ ' . number_format($balance, 2, ',', '.'));
    
            // Gerar o arquivo
            $writer = new Xlsx($spreadsheet);
    
            // Se filePath for fornecido, retorna o caminho do arquivo
            if ($filePath) {
                $writer->save($filePath);
                return $filePath;
            }
    
            ob_clean();
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // Define o tipo de arquivo
            header('Content-Disposition: attachment; filename="relatorio_financeiro.xlsx"'); // Define o nome do arquivo
            $writer->save('php://output'); // Salva o arquivo
            exit; // Encerra o script
        }
}