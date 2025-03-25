<?php

namespace services;

use Exception;
use TemplateFacade;

class GenreService
{
    private $templateFacade;

    public function __construct(TemplateFacade $templateFacade)
    {
        $this->templateFacade = $templateFacade;
    }

    public function handleGenres()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            header("HTTP/1.1 405 Method Not Allowed");
            exit();
        }

        if (!isset($_GET["genres"])) {
            header("HTTP/1.1 400 Bad Request");
            echo json_encode(['error' => 'Не указаны жанры.']);
            exit();
        }

        if(empty(trim($_GET["genres"]))){
            header("Location: /");
            exit();
        }

        $genresInput = $_GET["genres"];
        $genresArray = explode(',', $genresInput);
        $processedGenres = [];
        foreach ($genresArray as $genre) {
            $genre = trim($genre);
            if (strlen($genre) > 0) {
                $formattedGenre = ucfirst(strtolower($genre));
                $processedGenres[] = $formattedGenre;
            }
        }

        $processedGenres = array_unique($processedGenres);

        sort($processedGenres, SORT_STRING);

        header('Content-Type: application/json');
        $result = '';
        try {
            header("Content-Type: text/html; charset=UTF-8");
            $result =  $this->templateFacade->render(
                __DIR__ . '/../templates/genres_template.html',
                ['genres' => $processedGenres]
            );
        } catch (Exception $e) {
            echo "Ошибка: " . $e->getMessage();
        }
        return $result;
    }
}