<?php

require_once __DIR__ . '/autoload.php';

use Models\Router;
use TemplateFacade;

$templateFacade = new TemplateFacade();
$configPath = __DIR__ . '/config.json';

if (!file_exists($configPath)) {
    die("Файл конфигурации не найден.");
}

$router = Router::createWithDefaultRoutes($templateFacade, $configPath);
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$router->handleRequest($requestUri, $requestMethod);