<?php
    class Controller 
    {
        protected $sql;

        public function __construct()
        {
            global $sql;
            $this->sql = $sql;
        }

        public function Output($data)
        {
            header("Content-Type: text/json; charset=utf-8");
            echo @json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        public function AuthFilter($string)
        {
            return $this->sql->StringFilter($string, "auth");
        }

        public function IsSigned()
        {
            $session = $_SESSION["username"];
            $session .= $this->AuthFilter($_SERVER["REMOTE_ADDR"]);

            if (SecureHash($session) == $_SESSION["session"]) {
                return true;
            }
            return false;
        }
    }
    
    $controllerDir = "lib/controllers/";
    $controllerList = array_diff(scandir($controllerDir), [".", ".."]);

    foreach ($controllerList as $controller) {
        if (substr($controller, -strlen(".php")) === ".php") {
            include_once $controllerDir . "/" . $controller;
        }
    }
?>