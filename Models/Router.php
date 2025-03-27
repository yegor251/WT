<?php

namespace Models;

class Router
{
    private $routes = [];
    private $templateFacade;
    private $genreService;
    private $navController;
    private $genreController;
    private $movieRepository;

    public function __construct($templateFacade, $configPath)
    {
        $this->templateFacade = $templateFacade;
        $this->genreService = new \Services\GenreService($templateFacade);
        $this->navController = new \Controllers\NavController();
        $this->genreController = new \Controllers\GenreController($this->genreService);
        $this->movieRepository = new \Models\MovieRepository($configPath);
    }

    public function addRoute(string $method, string $path, callable $handler)
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function handleRequest(string $requestUri, string $requestMethod)
    {
        $basePath = '/films';
        if (strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $route['path'] === $requestUri) {
                call_user_func($route['handler']);
                return;
            }
        }

        $this->renderMainPage();
    }

    private function renderMainPage()
    {
        $movies = $this->movieRepository->getMovies();
        $this->movieRepository->closeConnection();

        try {
            echo $this->templateFacade->render(__DIR__ . '/../views/main_page.html', [
                'movies' => $movies
            ]);
        } catch (\Exception $e) {
            die("Ошибка рендеринга шаблона: " . $e->getMessage());
        }
    }

    public static function createWithDefaultRoutes($templateFacade, $configPath): self
    {
        $router = new self($templateFacade, $configPath);

        $router->addRoute('GET', '/process-genres', function() use ($router) {
            $router->genreController->handleGenres();
        });

        $router->addRoute('GET', '/nav', function() use ($router) {
            $router->navController->updateActive();
        });

        $router->addRoute('POST', '/add-movie', function() use ($router) {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $release_year = $_POST['release_year'];

            $router->movieRepository->addMovie($title, $description, $release_year);
            $router->movieRepository->closeConnection();

            header("Location: /films");
        });

        return $router;
    }
}