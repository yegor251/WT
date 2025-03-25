<?php

use Controller\GenreController;
use Controller\NavController;
use models\MovieRepository;
use services\GenreService;

require_once __DIR__ . '/TemplateFacade.php';
require_once __DIR__ . '/controllers/NavController.php';
require_once __DIR__ . '/controllers/GenreController.php';
require_once __DIR__ . '/services/GenreService.php';
require_once __DIR__ . '/models/MovieRepository.php';

$templateFacade = new TemplateFacade();
$genreService = new GenreService($templateFacade);

$navController = new NavController();
$genreController = new GenreController($genreService);

$requestUri = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : null;
$requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;

$basePath = '/films';
if (strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

error_log("Request URI: $requestUri, Method: $requestMethod");
// Обработка GET-запроса для жанров
if ($requestMethod === 'GET' && $requestUri === '/process-genres') {
    $genreController->handleGenres();
    exit();
}

// Обработка GET-запроса для навигации
if ($requestMethod === 'GET' && $requestUri === '/nav') {
    $navController->updateActive();
    exit();
}

// Обработка POST-запроса для добавления фильма
if ($requestMethod === 'POST' && $requestUri === '/add-movie') {
    error_log("Processing add-movie request");

    error_log("Form data: " . print_r($_POST, true));

    $title = $_POST['title'];
    $description = $_POST['description'];
    $release_year = $_POST['release_year'];

    $configPath = __DIR__ . '/config.json';
    if (!file_exists($configPath)) {
        die("Файл конфигурации не найден.");
    }

    $movieRepository = new MovieRepository($configPath);
    $movieRepository->addMovie($title, $description, $release_year);
    $movieRepository->closeConnection();

    header("Location: /films");
    exit();
}

// Получение списка фильмов и рендеринг главной страницы
$configPath = __DIR__ . '/config.json';

if (!file_exists($configPath)) {
    die("Файл конфигурации не найден.");
}
$movieRepository = new MovieRepository($configPath);

$movies = $movieRepository->getMovies();
$movieRepository->closeConnection();

try {
    echo $templateFacade->render(__DIR__ . '/views/main_page.html', [
        'movies' => $movies
    ]);
} catch (Exception $e) {
    die("Ошибка рендеринга шаблона: " . $e->getMessage());
}