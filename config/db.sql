-- =======================================================
-- DATABASE: foodwise
-- Description: Supports food tracking, donations, meal planning & notifications
-- =======================================================
CREATE DATABASE IF NOT EXISTS foowdwise;
USE foodwise;

-- =======================================================
-- USERS TABLE
-- =======================================================
CREATE TABLE Users (
	user_id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(100) NOT NULL,
	email VARCHAR(100) UNIQUE NOT NULL,
	password VARCHAR(255) NOT NULL,
	phone_number VARCHAR(15),
	address VARCHAR(150),
	is_verified TINYINT(1) DEFAULT 0,
	verification_code VARCHAR(6) DEFAULT NULL,
	verification_token VARCHAR(128) DEFAULT NULL,
	verification_expires DATETIME DEFAULT NULL;
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- =======================================================
-- FOOD TABLE
-- =======================================================
CREATE TABLE Food (
	food_id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT NOT NULL,
	type ENUM('canned', 'frozen', 'fresh', 'dry', 'other') NOT NULL,
	name VARCHAR(100) NOT NULL,
	notes VARCHAR(255),
	category ENUM('protein', 'vegetable', 'grain', 'fruit', 'dairy', 'other') DEFAULT 'other',
	storage_location VARCHAR(50),
	expiration_date DATE,
	quantity INT DEFAULT 1,
	is_expired BOOLEAN DEFAULT FALSE,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

CREATE INDEX idx_food_user ON Food(user_id);
CREATE INDEX idx_food_expiration ON Food(expiration_date);

-- =======================================================
-- DONATION TABLE
-- =======================================================
CREATE TABLE Donation (
	donation_id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT NOT NULL,
	food_id INT NOT NULL,
	status ENUM('available', 'claimed', 'completed', 'cancelled') DEFAULT 'available',
	pickup_location VARCHAR(150),
	contact_info VARCHAR(50),
	availability DATE,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
	FOREIGN KEY (food_id) REFERENCES Food(food_id) ON DELETE CASCADE
);

CREATE INDEX idx_donation_status ON Donation(status);

-- =======================================================
-- MEAL PLAN TABLE
-- =======================================================
CREATE TABLE Meal_Plan (
	meal_plan_id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT NOT NULL,
	date DATE NOT NULL,
	meal_slot ENUM('breakfast', 'lunch', 'dinner', 'snack') NOT NULL,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

CREATE INDEX idx_mealplan_user_date ON Meal_Plan(user_id, date);

-- =======================================================
-- MEAL PLAN ITEMS (JUNCTION TABLE)
-- =======================================================
CREATE TABLE MealPlanItems (
	meal_plan_id INT,
	food_id INT,
	PRIMARY KEY (meal_plan_id, food_id),
	FOREIGN KEY (meal_plan_id) REFERENCES Meal_Plan(meal_plan_id) ON DELETE CASCADE,
	FOREIGN KEY (food_id) REFERENCES Food(food_id) ON DELETE CASCADE
);

-- =======================================================
-- NOTIFICATIONS TABLE
-- =======================================================
CREATE TABLE Notifications (
	notification_id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT NOT NULL,
	title VARCHAR(100),
	message VARCHAR(255),
	type ENUM('inventory', 'donation', 'meal', 'system') DEFAULT 'system',
	is_read BOOLEAN DEFAULT FALSE,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

CREATE INDEX idx_notifications_user ON Notifications(user_id);