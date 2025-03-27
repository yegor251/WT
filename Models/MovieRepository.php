<?php

namespace Models;
use mysqli;

class MovieRepository
{
    private $configPath;
    private $connection;

    public function __construct($configPath){
        $this->configPath = $configPath;
        $this->sqlInit();
    }

    private function sqlInit()
    {
        $config = json_decode(file_get_contents($this->configPath), true);
        if ($config === null) {
            die("Ошибка декодирования файла конфигурации.");
        }

        $host = $config['host'];
        $user = $config['username'];
        $password = $config['password'];
        $dbname = $config['dbname'];

        $this->connection = new mysqli($host, $user, $password, $dbname);
    }

    public function getMovies(){
        if ($this->connection->connect_error) {
            die("Ошибка подключения к базе данных:".$this->connection->connect_error);
        }

        $sql = "SELECT title, description, release_year FROM movies";
        $result = $this->connection->query($sql);

        $movies = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $movies[] = [
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'release_year' => $row['release_year']
                ];
            }
        }
        return $movies;
    }

    public function addMovie($title, $description, $release_year) {
        $stmt = $this->connection->prepare("INSERT INTO movies (title, description, release_year) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $title, $description, $release_year);
        $stmt->execute();
        $stmt->close();
    }

    public function closeConnection(){
        $this->connection->close();
    }
}