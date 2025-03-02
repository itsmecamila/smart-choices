<?php

namespace App\Controllers;

use App\Scripts\SetupDatabase;

class SetupDatabaseController extends BaseController{
    public function index(){
        $setup = new SetupDatabase(); // Objeto SetupDatabase
        $result = $setup->run(); // Executa o script
        return $this->response->setJSON([
            'message' => $result // Retorna o resultado
        ]);
    }
}
