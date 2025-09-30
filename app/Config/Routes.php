<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'DashboardController::index');



// Usuários
$routes->group('usuarios', function($routes) {
    $routes->get('/', 'UsuarioController::index');
    $routes->get('create', 'UsuarioController::create');
    $routes->post('store', 'UsuarioController::store');
    $routes->get('edit/(:num)', 'UsuarioController::edit/$1');
    $routes->post('update/(:num)', 'UsuarioController::update/$1');
    $routes->get('delete/(:num)', 'UsuarioController::delete/$1');
});

// Financeiro
$routes->group('financeiro', function($routes) {
    $routes->get('/', 'FinanceiroController::index');
    $routes->get('create', 'FinanceiroController::create');
    $routes->post('store', 'FinanceiroController::store');
    $routes->get('delete/(:num)', 'FinanceiroController::delete/$1');
});

// Vendas
$routes->group('vendas', function($routes) {
    $routes->get('/', 'VendaController::index');
    $routes->get('create', 'VendaController::create');
    $routes->post('store', 'VendaController::store');
    $routes->get('delete/(:num)', 'VendaController::delete/$1');
});


// WhatsApp
$routes->get('whatsapp', 'MensagemWhatsappController::conversas');
$routes->get('whatsapp/conversas', 'MensagemWhatsappController::conversas');
$routes->get('whatsapp/conversa/(:alphanum)', 'MensagemWhatsappController::getConversa/$1');
$routes->post('whatsapp/send', 'MensagemWhatsappController::sendMessage');
$routes->post('whatsapp/sync', 'MensagemWhatsappController::syncConversas');
$routes->get('whatsapp/connection', 'MensagemWhatsappController::checkConnection');
$routes->get('whatsapp/chats', 'MensagemWhatsappController::getChats');

$routes->get('clientes', 'ClienteController::index');
$routes->get('clientes/create', 'ClienteController::create');
$routes->post('clientes/store', 'ClienteController::store');
$routes->get('clientes/edit/(:num)', 'ClienteController::edit/$1');
$routes->post('clientes/update/(:num)', 'ClienteController::update/$1');
$routes->get('clientes/delete/(:num)', 'ClienteController::delete/$1');