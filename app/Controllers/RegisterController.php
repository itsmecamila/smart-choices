<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\RegisterModel;
class RegisterController extends Controller{
    // Atributo da model de Registro 
    private $registerModel;

    // Atributo do JSON (dados passados pela requisição, declarei aqui para não ter que ficar redeclarando em todas funções)
    private $data;

    public function __construct(){
        // Construtor da classe instancia a Model, para não ter q redeclarar em todas as funções
        $this->registerModel =  new RegisterModel();
    }

    // Criei uma função para validar o JSON que vem da requisição (update e inset usam-a)
    public function validateJSON(){
        // Coloquei um try catch - assim o getJSON() não mostra sua mensagem de erro, e consigo dar feedback de erro personalizado
        try {
            // Atributo data recebe o JSON que vem da requisição - se for válido na estrutura, passa para IF
            $this->data = $this->request->getJSON(true);
            
            // Se o JSON estiver vazio, avisa
            if (empty($this->data)) {
                return [
                    'status' => 'error',
                    'message' => 'O JSON está vazio.'
                ];
            } 
            
            // Se não, DATA contém dados válidos, retorno false e permito ação onde chamou a função
            else {
                return false;
            }
        } 
        
        // Se o JSON for invalidado logo na getJSON(), pego o erro e aviso
        catch (\CodeIgniter\HTTP\Exceptions\HTTPException $e) {
            return [
                'status' => 'error',
                'message' => 'Erro ao processar JSON: ' . $e->getMessage()
            ];
        }
    }

    // Função que calcula saldo final com ou sem parâmetro
    public function getBalance(){
        // Variáveis captam parâmetros passados pela requisição
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        // Balance guarda chamada da função da model responsável por calcular o saldo
        $balance = $this->registerModel->calculateBalance($startDate, $endDate);

        // Não há caso de erro pois o tratamento está na model, se saldo não existir retorna 0
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Saldo calculado com sucesso!',
            'balance' => $balance
        ]);
    }

    // Função para criar registros
    public function createRegister(){
        // Primeiro eu valido o json da requisição
        $json = $this->validateJSON();

        // Se for diferente de falso, é INVÁLIDO, então retorna o erro que já está preenchido em $json na response
        if ($json != false) {
            return $this->response->setJSON($json)->setStatusCode(400);
        }

        // Se chegar nesse IF, é pq é válido (já que não atendeu o if anterior), então tento inserir no banco, caso não dê erro lá, insere e retorna sucesso
        $this->data = $this->registerModel->convertType($this->data);
        if ($this->registerModel->insert($this->data)) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Registro criado com sucesso!',
                'data' => $this->data
            ]);
        } 

        // Se der erro na inserção, retorna erro e cancela operação
        else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Erro ao criar registro.',
                'errors' => $this->registerModel->errors()
            ])->setStatusCode(400);
        }
    }

    // Busca todos períodos
    public function getAllRegisters(){
        // Se existirem registros, retorna com sucesso
        if (($data = $this->registerModel->findAll()) != null) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Todos registros encontrados!',
                'data' => $data
            ]);
        } 
        
        // Se não, retorna erro
        else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Busca falhou! Pode ser que não existam registros cadastrados. Verifique!',
            ])->setStatusCode(400);
        }
    }

    // Função q busca por ID
    public function getRegisterByID($id){
        // Se o ID for diferente de nulo e o registro buscado existir, retorna com sucesso 
        if ($id && ($data = $this->registerModel->find($id)) != null) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Registro encontrado!',
                'data' => $data
            ]);
        } 

        // Se nao, retorna erro
        else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ID nulo ou Registro buscado inexistente no banco.'
            ])->setStatusCode(400);
        }
    }

    public function updateRegister($id){
        // Valido json da requisição
        $json = $this->validateJSON();

        // Se é falso, é inválido
        if ($json != false) {
            return $this->response->setJSON($json)->setStatusCode(400);
        }

        // Se é válido, prossigo
        if ($this->registerModel->find($id)) {

            // Armazeno json anterior para mensagem personalizada
            $data = $this->registerModel->find($id);
            // Pego json da requisição
            $newData = $this->request->getJSON(true);
            // Tento atualizar e guardo resultado

            // Verifico se existe alteração para campo type
            if(isset($newData['type'])){
                $newData = $this->registerModel->convertType($newData);
            }   
    
            $allowed = $this->registerModel->update($id, $newData);

            // Se der certo, será TRUE, retorna sucesso
            if($allowed){
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Registro atualizado com sucesso!',
                    'old_data' => $data,
                    'new_data' => $this->registerModel->find($id)
                ]);
            }

            // Se der erro no banco, será FALSE, retorna erro
            else{
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Erro ao atualizar registro.',
                    'errors' => $this->registerModel->errors()
                ])->setStatusCode(400);
            }
        }

        // Se não encontrar no banco, retorna erro
        else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Erro ao atualizar registro.',
                'errors' => 'Registro inexistente no banco.'
            ])->setStatusCode(400);
        }
    }

    public function deleteRegister($id){

        // Verifico se parâmetro é válido
        if ($id == null) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ID nulo'
            ])->setStatusCode(400);
        }

        // Verifico se o registro existe
        $data = $this->registerModel->find($id);

        // Se existir, tento deletar
        if($data != null){
            if($this->registerModel->delete($id)){
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'O registro abaixo foi excluído com sucesso!',
                    'data' => $data
                ]);
            }

            // Se der erro, retorna erro
            else{
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Erro ao excluir registro.',
                ])->setStatusCode(400);
            }
        }


        // Se for null, não existe no banco
        else{
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Erro ao excluir registro.',
                'errors' => 'Registro inexistente no banco.'
            ])->setStatusCode(400);
        }
    }
}
