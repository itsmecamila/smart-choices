<?php

namespace App\Models;

use CodeIgniter\Model;

class RegisterModel extends Model{
    protected $table = 'registers'; // Nome da tabela do banco
    protected $primaryKey = 'id'; // Chave primária
    protected $allowedFields = ['title', 'value','type','date']; // Campos permitidos para inserção e alteração (manipulação)
    protected $validationRules = [
        'title' => 'required|max_length[100]|string|min_length[2]',
        'value' => 'required|numeric',  
        'type' => 'required|in_list[income,expense]',    
        'date' => 'required|valid_date[Y-m-d H:i:s]',
    ];  // Regras de validação para atributo

    protected $validationMessages = [
        'title' => [
            'required' => 'O título é obrigatório.',
            'max_length' => 'O título deve ter no máximo 100 caracteres.',
            'min_length' => 'O título deve ter pelo menos 2 caracteres.',
        ],

        'value' => [
            'required' => 'O valor é obrigatório.',
            'numeric' => 'O valor deve ser numérico.',
        ],

        'type' => [
            'required' => 'O tipo é obrigatório.',
            'in_list' => 'O tipo deve ser (income) ou (expense).',
        ],

        'date' => [ 
            'required' => 'A data é obrigatória.',
            'valid_date' => 'A data deve estar no formato YYYY-MM-DD HH:MM:SS.',
        ]
    ]; // Mensagens de validação para atributos

    // Função para converter type para lowercase
    public function convertType($data){
        $data['type'] = strtolower($data['type']); // Converte type para lowercase
        return $data; // Retorna data
    }

    // Função para calcular o saldo final COM ou SEM parâmetros
    public function calculateBalance($startDate = null, $endDate = null){
        // Se houver data de início e fim
        if($startDate && $endDate){
            // Calcula as somas das entradas e saídas dentro do período
            $incomes = $this->where('type', 'income')
                            ->where('date >=', $startDate)
                            ->where('date <=', $endDate)
                            ->selectSum('value')
                            ->first(); // Aqui uso 'first' para obter o resultado como array
            
            $expenses = $this->where('type', 'expense')
                                ->where('date >=', $startDate)
                                ->where('date <=', $endDate)
                                ->selectSum('value')
                                ->first(); // Aqui também

            // Se não houver valor retornado, consideramos 0
            $income = $incomes['value'] ?? 0;
            $expense = $expenses['value'] ?? 0;

            // Calcula o saldo final
            $balance = $income - $expense;
        } 
        
        else {
            // Sem parâmetros de data, calcula as entradas e saídas totais
            $incomes = $this->where('type', 'income')->selectSum('value')->first();
            $expenses = $this->where('type', 'expense')->selectSum('value')->first();

            $income = $incomes['value'] ?? 0;
            $expense = $expenses['value'] ?? 0;

            $balance = $income - $expense; // Calcula o saldo
        }

    return $balance; // Retorna o saldo
    }
}

?>
    
