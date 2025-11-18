<?php
require_once __DIR__ . '/../models/FoodModel.php';

class InventoryController {
	private $foodModel;

	public function __construct() {
		$this->foodModel = new FoodModel();
	}

	public function index() {
		if (!isset($_SESSION['user_id'])) {
			header('Location: /login');
			exit;
		}

		$foods = $this->foodModel->getAllFoodByUser($_SESSION['user_id']);
		
		include '../app/views/layout/header.php';
		include '../app/views/layout/sidebar.php';
		include '../app/views/inventory/index.php';
		include '../app/views/layout/footer.php';
	}

	public function add() {
		if (!isset($_SESSION['user_id'])) {
			header('Location: /login');
			exit;
		}

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$this->foodModel->createFood(
				$_SESSION['user_id'],
				$_POST['type'],
				$_POST['name'],
				$_POST['notes'],
				$_POST['category'],
				$_POST['storage_location'],
				$_POST['expiration_date'],
				$_POST['quantity']
			);

			header('Location: /inventory');
			exit;
		}

		include '../app/views/layout/header.php';
		include '../app/views/layout/sidebar.php';
		include '../app/views/inventory/add.php';
		include '../app/views/layout/footer.php';
	}

	public function delete($foodId) {
		if (!isset($_SESSION['user_id'])) {
			header('Location: /login');
			exit;
		}

		$this->foodModel->deleteFood($foodId, $_SESSION['user_id']);
		header('Location: /inventory');
		exit;
	}
}
?>