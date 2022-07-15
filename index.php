<?php
    require_once "lib/util.php";
    require_once "lib/init.php";
    require_once "lib/template.php";
    require_once "lib/model.php";
    require_once "lib/controller.php";

    $allowed_controller = ["default", "user", "status", "memo", "task", "drive"];
    try
    {
        $controller = isset($_GET["controller"]) ? $_GET["controller"] : "default";
        $action = isset($_GET["action"]) ? $_GET["action"] : "default";

        if (in_array($controller, $allowed_controller, true)) {
            $controller = ucfirst($controller) . "Controller";
            $action = ucfirst($action) . "Action";
    
            $controller = new $controller;
            $controller->$action();
            exit;
        }
        else {
            ReturnError();
        }
    }
    catch(Exception $ex) {
        ReturnError();
    }
?>