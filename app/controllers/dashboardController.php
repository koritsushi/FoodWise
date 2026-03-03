<?php
require_once __DIR__ . '/../models/FoodModel.php';

class DashboardController
{
    private $foodModel;

    public function __construct()
    {
        $this->foodModel = new FoodModel();
    }

    public function index()
    {
		// 1. Auth guard
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        $userId = (int)$_SESSION['user_id'];
        // 2. Initialize all variables (prevents undefined warnings)
		$dateFrom = $_GET['date_from'] ?? null;
        $dateTo   = $_GET['date_to']   ?? null;
        if ($dateFrom && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) $dateFrom = null;
        if ($dateTo   && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo))   $dateTo   = null;
        $totalFood          = 0;
        $expiredFood        = 0;
        $foodInMeals        = 0;
        $completedDonations = 0;
        $foodSaved          = 0;
        $months             = [];
        $savedCounts        = [];
        $donationCounts     = [];
		$foodAddedCounts = [];

       $debug = function ($msg) {
            error_log($msg);
            echo "<script>console.log('PHP DEBUG: $msg');</script>";
        };

        $debug("Dashboard loaded for user_id = $userId");

        try {
            $totalFood = (int)$this->foodModel->getTotalFood($userId);
            $debug("getTotalFood() → $totalFood");

            $expiredFood = (int)$this->foodModel->getExpiredFood($userId);
            $debug("getExpiredFood() → $expiredFood");

            $foodInMeals = (int)$this->foodModel->getFoodUsedInMeals($userId);
            $debug("getFoodUsedInMeals() → $foodInMeals");

            $completedDonations = (int)$this->foodModel->getCompletedDonations($userId);
            $debug("getCompletedDonations() → $completedDonations");

            $foodSaved = $totalFood - $expiredFood;
            $debug("foodSaved = $foodSaved");

            // Monthly analytics
            $monthly = $this->foodModel->getMonthlyAnalytics($userId, $dateFrom, $dateTo) 
                       ?? ['saved' => [], 'donations' => []];
			
            foreach ($monthly['saved'] as $row) {
                $months[] = $row['month'];
                $savedCounts[] = (int)$row['saved'];
            }

            $donMap = array_column($monthly['donations'], 'donations', 'month');
            foreach ($months as $m) {
                $donationCounts[] = $donMap[$m] ?? 0;
            }

            $months = array_reverse($months);
            $savedCounts = array_reverse($savedCounts);
            $donationCounts = array_reverse($donationCounts);

            $debug("Monthly data loaded: " . count($months) . " months");

			$foodAddedMonthly = $this->foodModel->getMonthlyFoodAdded($userId);
			$addedMap = array_column($foodAddedMonthly, 'added', 'month');
			foreach ($months as $m) {
				$foodAddedCounts[] = (int)($addedMap[$m] ?? 0);
			}
			$debug("getMonthlyFoodAdded() -> " . json_encode($foodAddedCounts));

        } catch (Throwable $e) {
            $debug("ERROR: " . $e->getMessage());
        }

        $data = compact(
            'totalFood', 'expiredFood', 'foodInMeals', 'completedDonations',
            'foodSaved', 'months', 'savedCounts', 'donationCounts', 'foodAddedCounts',
			'dateFrom', 'dateTo'
        );

        include '../app/views/layout/header.php';
		include '../app/views/layout/sidebar.php';
		include '../app/views/dashboard.php';
		include '../app/views/layout/footer.php';
    }
}