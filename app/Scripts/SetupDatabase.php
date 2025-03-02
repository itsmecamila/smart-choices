<?php

namespace App\Scripts;

use Config\Database; 
use CodeIgniter\Database\Exceptions\DatabaseException;

class SetupDatabase{
    private $writableDirectory; // Caminho de escrita
    protected $db; // Objeto database
    public function __construct(){
        // Preenche variável
        $this->writableDirectory = WRITEPATH; // Caminho de escrita
        $this->db = Database::connect(); // Objeto database
    }

    public function run(){
        // Verifica se as tabelas foram criadas
        if (file_exists(WRITEPATH . 'setup_done.txt')) {
            return "As tabelas já foram criadas.";
        }

        // Cria as tabelas
        try{
            $this->db->query(
                "CREATE TABLE IF NOT EXISTS registers (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(255) NOT NULL,
                    value DECIMAL(10,2) NOT NULL,
                    type ENUM('income', 'expense') NOT NULL,
                    date TIMESTAMP NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
        }

        // Tratamento de erro
        catch(DatabaseException $e){
            return "Erro ao criar tabelas: " . $e->getMessage();
        }

        $this->createSetupDone(); // Cria o arquivo setup_done
        return "Tabelas criadas com sucesso!"; // Retorna mensagem
    }

    // Cria o arquivo setup_done
    private function createSetupDone(){
        // Cria o arquivo que é usado como "flag" para atestar se tabelas foram criadas
        touch($this->writableDirectory . 'setup_done.txt'); 
    }
}
