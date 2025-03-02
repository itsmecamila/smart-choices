<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Rota para rodar script "Setup Database" e criar tabelas no banco
$routes->get('/setup-database', 'SetupDatabaseController::index');

// Rota para criar um relatório (CSV,PDF ou Excel-XLS)
$routes->get('/report', 'ReportController::generateReport');

// Rota para consultar saldo dinâmico OU saldo por período (se passar parâmetros)
$routes->get('/balance', 'RegisterController::getBalance');

// Rotas de busca: todos períodos OU único período pelo ID 
$routes->get('/registers','RegisterController::getAllRegisters');
$routes->get('/registers/(:num)', 'RegisterController::getRegisterByID/$1');

// Rota para criar um novo registro
$routes->post('/create-register', 'RegisterController::createRegister');

// Rota para atualizar um registro (necessário passar ID)
$routes->patch('/update/(:num)','RegisterController::updateRegister/$1');

// Rota para excluir um registro (necessário passar ID)
$routes->delete('/delete/(:num)', 'RegisterController::deleteRegister/$1');

?>