<?php
class InventoryController {
    public function index() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        include '../app/views/layout/header.php';
        include '../app/views/inventory.php';
        include '../app/views/layout/footer.php';
    }
}
?>