<?php

namespace Controllers {

    use Services\GenreService;

    class GenreController
    {
        private $genreService;

        public function __construct(GenreService $genreService)
        {
            $this->genreService = $genreService;
        }

        public function handleGenres()
        {
            echo $this->genreService->handleGenres();
        }
    }
}