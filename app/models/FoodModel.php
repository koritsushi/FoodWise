<?php
require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class FoodModel {
    private $conn;

    public function __construct() {
        global $conn;
		$this->conn = $conn;
    }

    public function getAllFoodByUser($user_id) {
		$this->markExpiredFoods();
        $stmt = $this->conn->prepare("SELECT * FROM Food WHERE user_id = :user_id ORDER BY expiration_date ASC");
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFoodById($food_id) {
        $stmt = $this->conn->prepare("SELECT * FROM Food WHERE food_id = :food_id");
        $stmt->execute(['food_id' => $food_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

	public function createFood($user_id, $type, $name, $notes, $category, $storage_location, $expiration_date, $quantity) {
		$query = "INSERT INTO Food (user_id, type, name, notes, category, storage_location, expiration_date, quantity)
					VALUES (:user_id, :type, :name, :notes, :category, :storage_location, :expiration_date, :quantity)";
		$stmt = $this->conn->prepare($query);

		$stmt->execute([
			'user_id' => $user_id,
			'type' => $type,
			'name' => $name,
			'notes' => $notes,
			'category' => $category,
			'storage_location' => $storage_location,
			'expiration_date' => $expiration_date,
			'quantity' => $quantity
		]);
	}


    public function updateFood($food_id, $data) {
        $stmt = $this->conn->prepare("
            UPDATE Food
            SET type = :type, name = :name, notes = :notes, category = :category,
                storage_location = :storage_location, expiration_date = :expiration_date, quantity = :quantity
            WHERE food_id = :food_id
        ");
        $data['food_id'] = $food_id;
        $stmt->execute($data);
    }

    public function deleteFood($food_id) {
        $stmt = $this->conn->prepare("DELETE FROM Food WHERE food_id = :food_id");
        $stmt->execute(['food_id' => $food_id]);
    }

	public function markExpiredFoods() {
		$query = "UPDATE Food SET is_expired = TRUE WHERE expiration_date < CURDATE() AND is_expired = FALSE";
		try {
			$stmt = $this->conn->prepare($query);
			if (!$stmt) {
				throw new Exception("Prepare failed: " . $this->conn->errorInfo()[2]);
			}
			$stmt->execute();
			$count = $stmt->rowCount();
			error_log("markExpiredFoods: Updated {$count} rows");
		} catch (Exception $e) {
			error_log("markExpiredFoods ERROR: " . $e->getMessage());
		}
	}

	private function sendExpiredAlert($toEmail, $toName, $foodName, $expiryDate) {
		$mail = new PHPMailer(true);

		try {
			$mail->isSMTP();
			$mail->Host = 'smtp.gmail.com';
			$mail->SMTPAuth = true;
			$mail->Username = getenv('SMTP_USER');
			$mail->Password = getenv('SMTP_PASS');
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
			$mail->Port = 587;

			$mail->setFrom(getenv('SMTP_USER'), 'FoodWise');
			$mail->addAddress($toEmail, $toName);
			$mail->isHTML(true);
			$mail->Subject = "Food Expiration Alert: {$foodName}";
			$mail->Body = "
				<h2>Hi {$toName},</h2>
				<p>Your food item <strong>{$foodName}</strong> expired on <strong>{$expiryDate}</strong>.</p>
				<p>Please check your inventory and dispose of expired items safely.</p>
				<p>Stay organized,<br>The FoodWise Team</p>
			";
			$mail->AltBody = "Your food item '{$foodName}' expired on {$expiryDate}. Please check your inventory.";

			$mail->send();
		} catch (Exception $e) {
			error_log("Email alert failed: {$mail->ErrorInfo}");
		}
    }

	public function getTotalFood(int $user_id): int
	{
		error_log("getTotalFood() called for user_id = $user_id");
		// 1. Count total food
		$sql = "SELECT COUNT(*) FROM Food WHERE user_id = :uid";
		$stmt = $this->conn->prepare($sql);
		$stmt->execute(['uid' => $user_id]);
		$result = (int)$stmt->fetchColumn();
		error_log("getTotalFood() RESULT = $result");

		// 2. Check for near-expiry items (include food_id!)
		$nearExpiry = $this->conn->prepare("
			SELECT food_id, name, expiration_date 
			FROM Food 
			WHERE user_id = :uid 
			AND is_expired = 0 
			AND expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
		");
		$nearExpiry->execute(['uid' => $user_id]);

		foreach ($nearExpiry->fetchAll(PDO::FETCH_ASSOC) as $food) {
			$this->createNotification(
				user_id: $user_id,
				title: "Expiring Soon",
				message: $food['name'] . " expires on " . date('M j', strtotime($food['expiration_date'])),
				type: "inventory",
				reference_id: $food['food_id']  // ← NOW EXISTS!
			);
		}

		return $result;
	}

	public function getExpiredFood(int $user_id): int
	{
		$this->markExpiredFoods(); // Ensure expired flag is updated
		error_log("getExpiredFood() called for user_id = $user_id");
		$sql = "SELECT COUNT(*) FROM Food WHERE user_id = :uid AND is_expired = 1";
		$stmt = $this->conn->prepare($sql);
		$stmt->execute(['uid' => $user_id]);
		$result = (int)$stmt->fetchColumn();
		error_log("getExpiredFood() RESULT = $result");
		return $result;
	}

	public function getFoodUsedInMeals(int $user_id): int
	{
		error_log("getFoodUsedInMeals() called for user_id = $user_id");
		$sql = "
			SELECT COUNT(DISTINCT mpi.food_id)
			FROM MealPlanItems mpi
			JOIN Meal_Plan mp ON mpi.meal_plan_id = mp.meal_plan_id
			WHERE mp.user_id = :uid
		";
		$stmt = $this->conn->prepare($sql);
		$stmt->execute(['uid' => $user_id]);
		$result = (int)$stmt->fetchColumn();
		error_log("getFoodUsedInMeals() RESULT = $result");
		return $result;
	}

	public function getCompletedDonations(int $user_id): int
	{
		error_log("getCompletedDonations() called for user_id = $user_id");
		$sql = "SELECT COUNT(*) FROM Donation WHERE user_id = :uid AND status = 'completed'";
		$stmt = $this->conn->prepare($sql);
		$stmt->execute(['uid' => $user_id]);
		$result = (int)$stmt->fetchColumn();
		error_log("getCompletedDonations() RESULT = $result");
		return $result;
	}

	public function getMonthlyAnalytics(int $user_id): array
	{
		error_log("getMonthlyAnalytics() called for user_id = $user_id");

		$savedSql = "
			SELECT DATE_FORMAT(f.created_at, '%Y-%m') AS month,
				COUNT(DISTINCT CASE WHEN mpi.food_id IS NOT NULL THEN f.food_id END) +
				COUNT(DISTINCT CASE WHEN d.status = 'completed' THEN d.food_id END) AS saved
			FROM Food f
			LEFT JOIN MealPlanItems mpi ON f.food_id = mpi.food_id
			LEFT JOIN Donation d ON f.food_id = d.food_id
			WHERE f.user_id = :uid
			GROUP BY month
			ORDER BY month DESC
			LIMIT 12
		";
		$stmt = $this->conn->prepare($savedSql);
		$stmt->execute(['uid' => $user_id]);
		$saved = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$donSql = "
			SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS donations
			FROM Donation
			WHERE user_id = :uid AND status = 'completed'
			GROUP BY month
			ORDER BY month DESC
			LIMIT 12
		";
		$stmt = $this->conn->prepare($donSql);
		$stmt->execute(['uid' => $user_id]);
		$donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

		return ['saved' => $saved, 'donations' => $donations];
	}

	// === NOTIFICATION HELPERS ===
	public function createNotification(int $user_id, string $title, string $message, string $type = 'system', ?int $reference_id = null ): bool
	{
		$checkSql = "SELECT 1 FROM Notifications 
                 WHERE user_id = :user_id 
                   AND type = :type 
                   AND (reference_id = :ref OR (reference_id IS NULL AND :ref IS NULL)) 
                 LIMIT 1";
		$check = $this->conn->prepare($checkSql);
		$check->execute([
			'user_id' => $user_id,
			'type'    => $type,
			'ref'     => $reference_id
		]);
		if ($check->fetch()) {
			error_log("Notification already sent: type=$type, ref=$reference_id, user=$user_id");
			return false; // Already sent
		}
		// Insert new notification
		$sql = "INSERT INTO Notifications 
				(user_id, title, message, type, reference_id, created_at) 
				VALUES (:user_id, :title, :message, :type, :ref, NOW())";
		$stmt = $this->conn->prepare($sql);
		$success = $stmt->execute([
			'user_id' => $user_id,
			'title'   => $title,
			'message' => $message,
			'type'    => $type,
			'ref'     => $reference_id
		]);
		if ($success) {
			error_log("New notification sent: $title");
		}
		return $success;
	}

	public function getNotifications(int $user_id, int $limit = 10): array
	{
		$sql = "SELECT * FROM Notifications 
				WHERE user_id = :user_id 
				ORDER BY created_at DESC 
				LIMIT :limit";
		$stmt = $this->conn->prepare($sql);
		$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
		$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getUnreadCount(int $user_id): int
	{
		$sql = "SELECT COUNT(*) FROM Notifications WHERE user_id = :user_id AND is_read = 0";
		$stmt = $this->conn->prepare($sql);
		$stmt->execute(['user_id' => $user_id]);
		return (int)$stmt->fetchColumn();
	}

	public function markNotificationAsRead(int $notification_id, int $user_id): void
	{
		$sql = "UPDATE Notifications SET is_read = 1 WHERE notification_id = :id AND user_id = :user_id";
		$stmt = $this->conn->prepare($sql);
		$stmt->execute(['id' => $notification_id, 'user_id' => $user_id]);
	}

	public function markAllAsRead(int $user_id): void
	{
		$sql = "UPDATE Notifications SET is_read = 1 WHERE user_id = :uid AND is_read = 0";
		$stmt = $this->conn->prepare($sql);
		$stmt->execute(['uid' => $user_id]);
	}
}
?>