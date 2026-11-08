-- Northstar Hotel upgrade. Run AFTER the original schema.sql. Safe to re-run.
-- Existing tables/data (users, items) are preserved; only a `role` column is added to users.
USE web_project_db;

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'role');
SET @ddl := IF(@col = 0,
  'ALTER TABLE users ADD COLUMN role ENUM(''customer'',''admin'') NOT NULL DEFAULT ''customer'' AFTER password',
  'SELECT 1');
PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS room_types (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(80) NOT NULL,
  description VARCHAR(500) NOT NULL DEFAULT '',
  sort_order SMALLINT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY room_types_name_unique (name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS rooms (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  room_number VARCHAR(20) NOT NULL,
  name VARCHAR(120) NOT NULL,
  room_type_id INT UNSIGNED NOT NULL,
  description TEXT NOT NULL,
  capacity TINYINT UNSIGNED NOT NULL DEFAULT 2,
  beds VARCHAR(100) NOT NULL DEFAULT '',
  price_per_night DECIMAL(10,2) NOT NULL,
  amenities VARCHAR(500) NOT NULL DEFAULT '',
  image_path VARCHAR(255) NULL,
  is_available TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY rooms_number_unique (room_number),
  KEY rooms_type_index (room_type_id),
  CONSTRAINT rooms_type_foreign FOREIGN KEY (room_type_id) REFERENCES room_types (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bookings (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  reference VARCHAR(20) NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  room_id INT UNSIGNED NOT NULL,
  check_in DATE NOT NULL,
  check_out DATE NOT NULL,
  guests TINYINT UNSIGNED NOT NULL,
  nights SMALLINT UNSIGNED NOT NULL,
  price_per_night DECIMAL(10,2) NOT NULL,      -- price snapshot at booking time
  total_amount DECIMAL(12,2) NOT NULL,
  guest_name VARCHAR(120) NOT NULL,
  guest_phone VARCHAR(30) NOT NULL,
  special_requests VARCHAR(500) NOT NULL DEFAULT '',
  status ENUM('Pending','Confirmed','Checked-In','Checked-Out','Cancelled','Rejected') NOT NULL DEFAULT 'Pending',
  cancellation_requested TINYINT(1) NOT NULL DEFAULT 0,
  payment_method VARCHAR(30) NOT NULL DEFAULT 'Pay at Hotel',
  payment_status ENUM('Unpaid','Paid') NOT NULL DEFAULT 'Unpaid',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY bookings_reference_unique (reference),
  KEY bookings_room_dates_index (room_id, check_in, check_out),
  KEY bookings_user_index (user_id),
  KEY bookings_status_index (status),
  CONSTRAINT bookings_user_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE RESTRICT,
  CONSTRAINT bookings_room_foreign FOREIGN KEY (room_id) REFERENCES rooms (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  booking_id INT UNSIGNED NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  method VARCHAR(30) NOT NULL,
  paid_at DATE NOT NULL,
  recorded_by INT UNSIGNED NOT NULL,
  note VARCHAR(255) NOT NULL DEFAULT '',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY payments_booking_index (booking_id),
  CONSTRAINT payments_booking_foreign FOREIGN KEY (booking_id) REFERENCES bookings (id) ON DELETE RESTRICT,
  CONSTRAINT payments_admin_foreign FOREIGN KEY (recorded_by) REFERENCES users (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Sample data (edit in the admin panel afterwards). Prices are in PKR.
INSERT INTO room_types (name, description, sort_order) VALUES
 ('Standard Room',  'Comfortable, well-appointed rooms for short stays.', 1),
 ('Deluxe Room',    'More space, a seating area and upgraded amenities.', 2),
 ('Executive Room', 'A work desk, lounge corner and premium bedding.', 3),
 ('Family Room',    'Spacious rooms designed for families and small groups.', 4),
 ('Suite',          'Our finest: separate living area and the best views.', 5)
ON DUPLICATE KEY UPDATE id = id;

INSERT INTO rooms (room_number, name, room_type_id, description, capacity, beds, price_per_night, amenities)
SELECT '101','Standard Twin',id,'Quiet twin room with garden-facing window.',2,'2 single beds',12000,'Wi-Fi, Air conditioning, TV, Breakfast' FROM room_types WHERE name='Standard Room'
ON DUPLICATE KEY UPDATE id = id;
INSERT INTO rooms (room_number, name, room_type_id, description, capacity, beds, price_per_night, amenities)
SELECT '102','Standard Double',id,'Cosy double room, ideal for couples.',2,'1 double bed',12500,'Wi-Fi, Air conditioning, TV, Breakfast' FROM room_types WHERE name='Standard Room'
ON DUPLICATE KEY UPDATE id = id;
INSERT INTO rooms (room_number, name, room_type_id, description, capacity, beds, price_per_night, amenities)
SELECT '201','Deluxe King',id,'Spacious room with a king bed and sitting area.',2,'1 king bed',18000,'Wi-Fi, Air conditioning, Smart TV, Mini fridge, Breakfast' FROM room_types WHERE name='Deluxe Room'
ON DUPLICATE KEY UPDATE id = id;
INSERT INTO rooms (room_number, name, room_type_id, description, capacity, beds, price_per_night, amenities)
SELECT '202','Deluxe Twin',id,'Bright twin room with a work desk.',3,'2 single beds + sofa bed',18500,'Wi-Fi, Air conditioning, Smart TV, Mini fridge, Breakfast' FROM room_types WHERE name='Deluxe Room'
ON DUPLICATE KEY UPDATE id = id;
INSERT INTO rooms (room_number, name, room_type_id, description, capacity, beds, price_per_night, amenities)
SELECT '301','Executive King',id,'Premium room with desk, lounge chair and city view.',2,'1 king bed',25000,'Wi-Fi, Air conditioning, Smart TV, Work desk, Coffee machine, Breakfast' FROM room_types WHERE name='Executive Room'
ON DUPLICATE KEY UPDATE id = id;
INSERT INTO rooms (room_number, name, room_type_id, description, capacity, beds, price_per_night, amenities)
SELECT '302','Executive Corner',id,'Corner room with extra windows and a lounge corner.',3,'1 king bed + sofa bed',27000,'Wi-Fi, Air conditioning, Smart TV, Work desk, Coffee machine, Breakfast' FROM room_types WHERE name='Executive Room'
ON DUPLICATE KEY UPDATE id = id;
INSERT INTO rooms (room_number, name, room_type_id, description, capacity, beds, price_per_night, amenities)
SELECT '401','Family Room',id,'Large room for families, with two double beds.',4,'2 double beds',30000,'Wi-Fi, Air conditioning, Smart TV, Mini fridge, Breakfast' FROM room_types WHERE name='Family Room'
ON DUPLICATE KEY UPDATE id = id;
INSERT INTO rooms (room_number, name, room_type_id, description, capacity, beds, price_per_night, amenities)
SELECT '402','Family Connect',id,'Two connecting rooms for larger groups.',6,'2 double beds + 2 single beds',38000,'Wi-Fi, Air conditioning, Smart TV, Mini fridge, Breakfast' FROM room_types WHERE name='Family Room'
ON DUPLICATE KEY UPDATE id = id;
INSERT INTO rooms (room_number, name, room_type_id, description, capacity, beds, price_per_night, amenities)
SELECT '501','Northstar Suite',id,'Our signature suite: living room, bedroom and private balcony.',3,'1 king bed + sofa bed',45000,'Wi-Fi, Air conditioning, Smart TV, Living area, Balcony, Minibar, Breakfast' FROM room_types WHERE name='Suite'
ON DUPLICATE KEY UPDATE id = id;
