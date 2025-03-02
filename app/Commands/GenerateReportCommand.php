<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\ReportService;

class GenerateReportCommand extends BaseCommand{
    // Configurações do comando
    protected $group = 'Reports'; // Agrupando comando em categoria chamada "reports" 
    protected $name = 'report:generate'; // Nome do comando para executar no terminal : php spark report:generate
    protected $description = 'Gera relatórios interativamente (CSV, PDF ou Excel).'; // Explicação do comando
    protected $reportService; // Atributo para gaurdar instância da classe

    public function __construct(){
        $this->reportService = new ReportService(); // Instancia classe
    }

    // Função que contém lógica do comando
    public function run(array $params){
        // Mensagem de entrada no terminal
        CLI::write('Bem-vindo(a) ao Gerador de Relatórios!', 'red');

        // Loop que persiste até usuário digitar dado válido
        do{
            CLI::write('Escolha o formato de relatório desejado: [csv , pdf, excel]','green'); // Mensagem para usuário
            $format = CLI::prompt(''); // Captura entrada digitada pelo usuário
            $validFormat = in_array(strtolower($format), ['csv', 'pdf', 'excel']); // Valida entrada

            // Se entrada inválida, mostra erro
            if(!$validFormat){
                CLI::error('Formato inválido. Escolha entre csv, pdf ou excel.', 'red');
            }
        } while (!$validFormat); // Enquanto entrada inválida, continua loop

        $startDate = $this->getValidDate('Informe a data inicial (YYYY-MM-DD)'); // Captura data inicial
        $endDate = $this->getValidDate('Informe a data final (YYYY-MM-DD)'); // Captura data final

        // Loop que persiste até usuário digitar "yes" ou "no"
        do{
            CLI::write("Gerar relatório em {$format} de {$startDate} a {$endDate}? (yes/no)", 'green');
            $response = strtolower(CLI::prompt('')); // Captura resposta
            $validResponse = in_array($response, ['yes', 'no']); // Valida resposta

            // Se resposta inválida, mostra erro
            if(!$validResponse){
                CLI::error('Resposta inválida. Escolha entre "yes" ou "no".', 'red');
            }
        } while (!$validResponse); // Enquanto resposta inválida, continua loop

        // Se resposta for "no", cancela operação
        if ($response !== 'yes') {
            CLI::error('Operação cancelada.', 'red');
            return;
        }
    
        $filePath = $this->reportService->generateReport($format, $startDate, $endDate, true); // Gera relatório e retorna caminho do arquivo
        CLI::write("Relatório gerado em: {$filePath}", 'green'); // Mostra caminho do arquivo
    }

    // Função para capturar data do usuário
    private function getValidDate(string $message): string {

        // Loop que persiste até usuário digitar data válida
        do {
            CLI::write($message, 'green'); // Mensagem para usuário
            $date = CLI::prompt(''); // Captura entrada digitada pelo usuário
            $valid = $this->isValidDate($date); // Valida entrada

            // Se data inválida, mostra erro
            if (!$valid) {
                CLI::error('Data inválida! Use o formato YYYY-MM-DD.'); // Mensagem de erro
            }

        } while (!$valid);// Enquanto data inválida, continua loop

        return $date; // Retorna data válida
    }

    // Função para validar data
    private function isValidDate(string $date): bool
    {
        // Verifica se data tem o formato YYYY-MM-DD
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false; // Data inválida
        }

        list($year, $month, $day) = explode('-', $date); // Separa data em ano, mês e dia
        return checkdate((int)$month, (int)$day, (int)$year); // Verifica se data é válida
    }
}

?>