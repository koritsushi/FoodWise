<?php
require_once '../config/db_connection.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/../../vendor/autoload.php';

class AuthController {
	public function register() {
		global $conn;

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			// sanitize + validate
			$name = trim($_POST['name'] ?? '');
			$email = trim($_POST['email'] ?? '');
			$phoneNumber = trim($_POST['phoneNumber'] ?? '');
			$password = $_POST['password'] ?? '';
			$address = trim($_POST['address'] ?? '');

			$errors = [];

			// email format
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$errors[] = "Invalid email address.";
			}

			// password strength (server-side)
			$pwdPattern = '/^(?=.{8,64}$)(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).*$/';
			if (!preg_match($pwdPattern, $password)) {
				$errors[] = "Password must be 10-64 chars and include upper/lowercase, a number and a special character.";
			}

			// check existing email
			$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = :email");
			$stmt->execute(['email' => $email]);
			if ($stmt->fetch()) {
				$errors[] = "This email is already registered.";
			}

			if (empty($errors)) {
				$password_hash = password_hash($password, PASSWORD_DEFAULT); // secure
				// verification figures
				$verification_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT); // 6-digit code
				$verification_token = bin2hex(random_bytes(32)); // long token for link
				$expires_at = (new DateTime('+30 minutes'))->format('Y-m-d H:i:s'); // set expiry

				// store user (is_verified = 0) with verification data
				$stmt = $conn->prepare("INSERT INTO users (name, email, password, phone_number, address, is_active, is_verified, verification_code, verification_token, verification_expires) 
				VALUES 
					(:name, :email, :password, :phone_number, :address, 1, 0, :vcode, :vtoken, :vexp)");
				$stmt->execute([
					'name' => $name,
					'email' => $email,
					'password' => $password_hash,
					'phone_number' => $phoneNumber,
					'address' => $address,
					'vcode' => $verification_code,
					'vtoken' => $verification_token,
					'vexp' => $expires_at
				]);

				// send verification email
				$this->sendVerificationEmail($email, $name, $verification_code, $verification_token);

				// redirect to a "check your email" page (or show a message)
				header('Location: /verify-code');
				exit;
			}
			// pass $errors to register view for display (you already include header/footer below)
    	}
		include '../app/views/layout/header.php';
		include '../app/views/register.php';
		include '../app/views/layout/footer.php';
	}

	private function sendVerificationEmail($toEmail, $toName, $code, $token) {
		$mail = new PHPMailer(true);
		try {
			$mail->isSMTP();
			$mail->Host = 'smtp.gmail.com'; // replace with your SMTP
			$mail->SMTPAuth = true;
			$mail->Username = getenv('SMTP_USER');
			$mail->Password = getenv('SMTP_PASS');
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
			$mail->Port = 587;

			$mail->setFrom(getenv('SMTP_USER'), 'FoodWise');
			$mail->addAddress($toEmail, $toName);

			$mail->isHTML(true);
			$mail->Subject = 'Verify your FoodWise account';

			$verificationLink = '/verify-email?token=' . urlencode($token);
			$mail->Body = "
				<h2>Welcome to FoodWise, {$toName}!</h2>
				<p>To activate your account, please verify your email address by clicking the button below:</p>
				<p><a href='{$verificationLink}' style='background:#28a745;color:#fff;padding:10px 20px;border-radius:5px;text-decoration:none;'>Verify Email</a></p>
				<p>Or use this code: <b>{$code}</b></p>
				<p>This code expires in 30 minutes.</p>
				<p>— The FoodWise Team</p>
			";
			$mail->AltBody = "Your verification code is {$code}. Or verify via link: {$verificationLink}";

			$mail->send();
		} catch (Exception $e) {
			error_log('Mail error: ' . $mail->ErrorInfo);
		}
	}

	public function verifyEmail() {
		global $conn;
		$token = $_GET['token'] ?? '';

		if (!$token) {
			echo "Invalid verification token.";
			return;
		}

		$stmt = $conn->prepare("SELECT user_id, verification_expires FROM users WHERE verification_token = :token AND is_verified = 0");
		$stmt->execute(['token' => $token]);
		$user = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$user) {
			echo "Invalid or expired link.";
			return;
		}

		if (new DateTime() > new DateTime($user['verification_expires'])) {
			echo "Verification link expired.";
			return;
		}

		$stmt = $conn->prepare("UPDATE users SET is_active = 1, is_verified = 1, verification_token = NULL, verification_code = NULL, verification_expires = NULL WHERE user_id = :id");
		$stmt->execute(['id' => $user['user_id']]);

		header('Location: /login?verified=1');
		exit;
	}

	public function verifyCode() {
		global $conn;
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$email = $_POST['email'] ?? '';
			$code  = $_POST['code'] ?? '';

			$stmt = $conn->prepare("SELECT user_id, verification_expires FROM users WHERE email = :email AND verification_code = :code AND is_verified = 0");
			$stmt->execute(['email' => $email, 'code' => $code]);
			$user = $stmt->fetch(PDO::FETCH_ASSOC);

			if (!$user) {
				$error = "Invalid email or code.";
			} else if (new DateTime() > new DateTime($user['verification_expires'])) {
				$error = "Code expired.";
			} else {
				$stmt = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL, verification_code = NULL, verification_expires = NULL WHERE user_id = :id");
				$stmt->execute(['id' => $user['user_id']]);
				header('Location: /login?verified=1');
				exit;
			}
		}

		include '../app/views/layout/header.php';
		include '../app/views/verify_code.php';
		include '../app/views/layout/footer.php';
	}

	// public function login() {
	// 	global $conn;
	// 	$error = "";

	// 	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// 		$email = trim($_POST['email']);
	// 		$password = $_POST['password'];

	// 		if (empty($email) || empty($password)) {
	// 			$error = "Please fill in all fields.";
	// 		} else {
	// 			$stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
	// 			$stmt->execute(['email' => $email]);
	// 			$user = $stmt->fetch(PDO::FETCH_ASSOC);

	// 			if ($user && password_verify($password, $user['password'])) {
	// 				$_SESSION['user_id'] = $user['user_id'];
	// 				$_SESSION['name'] = $user['name'];
	// 				header('Location: /dashboard');
	// 				exit;
	// 			} else {
	// 				$error = "Invalid email or password.";
	// 			}
	// 		}
    // 	}

	// 	include '../app/views/layout/header.php';
	// 	include '../app/views/login.php';
	// 	include '../app/views/layout/footer.php';
	// }

	public function login() {
		global $conn;
		$error = "";

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$email    = trim($_POST['email']);
			$password = $_POST['password'];

			if (empty($email) || empty($password)) {
				$error = "Please fill in all fields.";
			} else {
				$stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
				$stmt->execute(['email' => $email]);
				$user = $stmt->fetch(PDO::FETCH_ASSOC);

				if ($user && password_verify($password, $user['password'])) {
					//Password correct — generate 2FA code
					$code       = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
					$expires_at = (new DateTime('+10 minutes'))->format('Y-m-d H:i:s');

					$stmt = $conn->prepare("UPDATE users SET two_fa_code = :code, two_fa_expires = :exp WHERE user_id = :id");
					$stmt->execute(['code' => $code, 'exp' => $expires_at, 'id' => $user['user_id']]);

					// Store user_id in session temporarily (not fully logged in yet)
					$_SESSION['2fa_user_id'] = $user['user_id'];

					// Send code to email
					$this->send2FAEmail($user['email'], $user['name'], $code);

					header('Location: /verify-2fa');
					exit;
				} else {
					$error = "Invalid email or password.";
				}
			}
		}

		include '../app/views/layout/header.php';
		include '../app/views/login.php';
		include '../app/views/layout/footer.php';
	}

	public function verify2FA() {
		global $conn;
		$error = "";

		// Guard: must have come from login step
		if (!isset($_SESSION['2fa_user_id'])) {
			header('Location: /login');
			exit;
		}

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$code   = trim($_POST['code'] ?? '');
			$userId = (int)$_SESSION['2fa_user_id'];

			$stmt = $conn->prepare("SELECT name, two_fa_code, two_fa_expires FROM users WHERE user_id = :id");
			$stmt->execute(['id' => $userId]);
			$user = $stmt->fetch(PDO::FETCH_ASSOC);

			if (!$user || $user['two_fa_code'] !== $code) {
				$error = "Invalid code. Please try again.";
			} elseif (new DateTime() > new DateTime($user['two_fa_expires'])) {
				$error = "Code expired. Please log in again.";
				unset($_SESSION['2fa_user_id']);
			} else {
				// 2FA passed — clear code and fully log in
				$stmt = $conn->prepare("UPDATE users SET two_fa_code = NULL, two_fa_expires = NULL WHERE user_id = :id");
				$stmt->execute(['id' => $userId]);

				unset($_SESSION['2fa_user_id']);
				$_SESSION['user_id'] = $userId;
				$_SESSION['name']    = $user['name'];

				header('Location: /dashboard');
				exit;
			}
		}

		include '../app/views/layout/header.php';
		include '../app/views/verify_2fa.php';
		include '../app/views/layout/footer.php';
	}

	public function resend2FA() {
		global $conn;

		// Guard: must have come from login step
		if (!isset($_SESSION['2fa_user_id'])) {
			header('Location: /login');
			exit;
		}

		$userId = (int)$_SESSION['2fa_user_id'];

		$stmt = $conn->prepare("SELECT email, name FROM users WHERE user_id = :id");
		$stmt->execute(['id' => $userId]);
		$user = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($user) {
			$code       = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
			$expires_at = (new DateTime('+10 minutes'))->format('Y-m-d H:i:s');

			$stmt = $conn->prepare("UPDATE users SET two_fa_code = :code, two_fa_expires = :exp WHERE user_id = :id");
			$stmt->execute(['code' => $code, 'exp' => $expires_at, 'id' => $userId]);

			$this->send2FAEmail($user['email'], $user['name'], $code);

			header('Location: /verify-2fa?resent=1');
			exit;
		}

		header('Location: /login');
		exit;
	}

	private function send2FAEmail($toEmail, $toName, $code) {
		$mail = new PHPMailer(true);
		try {
			$mail->isSMTP();
			$mail->Host       = 'smtp.gmail.com';
			$mail->SMTPAuth   = true;
			$mail->Username   = getenv('SMTP_USER');
			$mail->Password   = getenv('SMTP_PASS');
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
			$mail->Port       = 587;

			$mail->setFrom(getenv('SMTP_USER'), 'FoodWise');
			$mail->addAddress($toEmail, $toName);
			$mail->isHTML(true);
			$mail->Subject = 'Your FoodWise login code';
			$mail->Body    = "
				<h2>Hi {$toName},</h2>
				<p>Your login verification code is:</p>
				<h1 style='letter-spacing:8px;color:#28a745;'>{$code}</h1>
				<p>This code expires in <strong>10 minutes</strong>.</p>
				<p>If you didn't try to log in, please ignore this email.</p>
				<p>— The FoodWise Team</p>
			";
			$mail->AltBody = "Your FoodWise login code is: {$code}. Expires in 10 minutes.";
			$mail->send();
		} catch (Exception $e) {
			error_log('2FA mail error: ' . $mail->ErrorInfo);
		}
	}

    public function logout() {
        session_destroy();
        // Redirect to login
        header('Location: /login');
        exit;
    }
}
?>