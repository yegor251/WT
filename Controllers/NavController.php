<?php

namespace Controllers;

class NavController
{
    private $activeLink;

    public function __construct(){
        if(isset($_SESSION['active_nav'])){
            $this->activeLink = $_SESSION['active_nav'];
        }else{
            $this->activeLink = 'About';
        }
    }

    public function updateActive() {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            header("HTTP/1.1 405 Method Not Allowed");
            exit();
        }

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!isset($data['active'])) {
            header("HTTP/1.1 400 Bad Request");
            echo json_encode(['error' => 'Не указана новая активная ссылка']);
            exit();
        }

        $this->activeLink = $data['active'];
        $_SESSION['active_nav'] = $this->activeLink;

        header('Content-Type: application/json');
        echo json_encode(['active' => $this->activeLink]);
    }

    public function getActive() {
        return $this->activeLink;
    }
}