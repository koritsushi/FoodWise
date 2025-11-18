<?php
// app/controllers/NotificationController.php
require_once __DIR__ . '/../models/FoodModel.php';

class NotificationController
{
    private $foodModel;

    public function __construct()
    {
        $this->foodModel = new FoodModel();
    }

    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];

        // Get all notifications (most recent first)
        $notifications = $this->foodModel->getNotifications($userId, 50);

        // Mark all as read when viewing page (optional)
        // $this->foodModel->markAllAsRead($userId);

        include '../app/views/layout/header.php';
        include '../app/views/layout/sidebar.php';
        include '../app/views/notification.php';
        include '../app/views/layout/footer.php';
    }

	function timeAgo($datetime) {
		$now = new DateTime();
		$past = new DateTime($datetime);
		$interval = $now->diff($past);

		if ($interval->d == 0) {
			if ($interval->h > 0) return $interval->h . " hour" . ($interval->h > 1 ? "s" : "") . " ago";
			if ($interval->i > 0) return $interval->i . " minute" . ($interval->i > 1 ? "s" : "") . " ago";
			return "Just now";
		}
		if ($interval->d == 1) return "Yesterday";
		if ($interval->d < 7) return $interval->d . " days ago";
		return date('M j, Y', strtotime($datetime));
	}
}