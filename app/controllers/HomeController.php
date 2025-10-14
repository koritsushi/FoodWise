<?php
class HomeController {
	public function index() {
		include '../app/views/layout/header.php';
		include '../app/views/home.php';
		include '../app/views/layout/footer.php';
	}

	public function dashboard() {
		if (!isset($_SESSION['user_id'])) {
			header('Location: http://localhost/FoodWise/public/login');
			exit;
		}
		include '../app/views/layout/header.php';
		include '../app/views/layout/sidebar.php';
		include '../app/views/dashboard.php';
		include '../app/views/layout/footer.php';
	}
}
?>