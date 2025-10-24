<?php
session_start();
require_once('../controllers/TodoController.php');

$todoController = new TodoController();
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'index':
        $todoController->index();
        break;
    case 'create':
        $todoController->create();
        break;
    case 'update':
        $todoController->update();
        break;
    case 'delete':
        $todoController->delete();
        break;
    case 'updateOrder':
        $todoController->updateOrder();
        break;
    default:
        $todoController->index();
        break;
}
