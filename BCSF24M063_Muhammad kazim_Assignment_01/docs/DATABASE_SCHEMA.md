# SmartDine Database Schema Documentation

## 1. Database overview

The SmartDine application uses a normalized MySQL database designed for a restaurant management system. The design supports the current authentication module and future features such as categories, menu items, reservations, and orders.

## 2. Tables summary

| Table | Purpose |
|------|---------|
| `users` | Stores customer, staff, and admin user account data |
| `restaurants` | Stores restaurant branch or business profiles |
| `categories` | Groups menu items under categories |
| `menu_items` | Stores the restaurant menu product list |
| `reservations` | Stores customer booking information |
| `orders` | Stores order header data |
| `order_items` | Stores each menu item in an order |

## 3. Table details

### users

**Purpose:** Stores account information for authentication and user identity.

| Column | Type | Key | Required | Default | Notes |
|--------|------|-----|----------|---------|-------|
| `id` | `INT UNSIGNED` | PK | Yes | Auto-increment | Unique user ID |
| `name` | `VARCHAR(100)` | - | Yes | - | Full name |
| `email` | `VARCHAR(150)` | UNIQUE | Yes | - | Used for login |
| `password_hash` | `VARCHAR(255)` | - | Yes | - | Hashed with `password_hash()` |
| `phone` | `VARCHAR(20)` | - | No | `NULL` | Optional contact number |
| `role` | `ENUM('customer','staff','admin')` | - | Yes | `customer` | Role-based access |
| `created_at` | `TIMESTAMP` | - | Yes | `CURRENT_TIMESTAMP` | Account creation date |
| `updated_at` | `TIMESTAMP` | - | Yes | `CURRENT_TIMESTAMP` | Last profile update |

**Relationships:**
- One user may create many reservations.
- One user may place many orders.

**Indexes and constraints:**
- Primary key on `id`
- Unique index on `email`

### restaurants

**Purpose:** Stores data about each restaurant business or branch.

| Column | Type | Key | Required | Default | Notes |
|--------|------|-----|----------|---------|-------|
| `id` | `INT UNSIGNED` | PK | Yes | Auto-increment | Unique restaurant ID |
| `name` | `VARCHAR(150)` | - | Yes | - | Restaurant name |
| `address` | `VARCHAR(255)` | - | Yes | - | Physical address |
| `phone` | `VARCHAR(20)` | - | No | `NULL` | Contact number |
| `email` | `VARCHAR(150)` | - | No | `NULL` | Business email |
| `is_active` | `TINYINT(1)` | - | Yes | `1` | Active or inactive status |
| `created_at` | `TIMESTAMP` | - | Yes | `CURRENT_TIMESTAMP` | Record creation time |

**Relationships:**
- One restaurant may have many categories.
- One restaurant may have many menu items.
- One restaurant may host many reservations.
- One restaurant may receive many orders.

### categories

**Purpose:** Organizes menu items into logical food groups.

| Column | Type | Key | Required | Default | Notes |
|--------|------|-----|----------|---------|-------|
| `id` | `INT UNSIGNED` | PK | Yes | Auto-increment | Unique category ID |
| `restaurant_id` | `INT UNSIGNED` | FK | Yes | - | Parent restaurant |
| `name` | `VARCHAR(100)` | - | Yes | - | Category name |
| `description` | `TEXT` | - | No | `NULL` | Optional description |
| `created_at` | `TIMESTAMP` | - | Yes | `CURRENT_TIMESTAMP` | Creation time |

**Relationships:**
- Many categories belong to one restaurant.
- One category contains many menu items.

**Constraints:**
- Foreign key to `restaurants.id`
- Unique combo `(restaurant_id, name)`

### menu_items

**Purpose:** Represents dishes or products available for ordering.

| Column | Type | Key | Required | Default | Notes |
|--------|------|-----|----------|---------|-------|
| `id` | `INT UNSIGNED` | PK | Yes | Auto-increment | Unique item ID |
| `restaurant_id` | `INT UNSIGNED` | FK | Yes | - | Parent restaurant |
| `category_id` | `INT UNSIGNED` | FK | Yes | - | Parent category |
| `name` | `VARCHAR(150)` | - | Yes | - | Dish name |
| `description` | `TEXT` | - | No | `NULL` | Menu description |
| `price` | `DECIMAL(10,2)` | - | Yes | - | Item price |
| `image_url` | `VARCHAR(255)` | - | No | `NULL` | Optional image path |
| `is_available` | `TINYINT(1)` | - | Yes | `1` | Whether item is available |
| `created_at` | `TIMESTAMP` | - | Yes | `CURRENT_TIMESTAMP` | Creation time |

**Relationships:**
- Many menu items belong to one restaurant.
- Many menu items belong to one category.
- One menu item may appear in many order items.

### reservations

**Purpose:** Stores customer booking details for restaurant tables or events.

| Column | Type | Key | Required | Default | Notes |
|--------|------|-----|----------|---------|-------|
| `id` | `INT UNSIGNED` | PK | Yes | Auto-increment | Reservation ID |
| `user_id` | `INT UNSIGNED` | FK | Yes | - | Customer who made booking |
| `restaurant_id` | `INT UNSIGNED` | FK | Yes | - | Restaurant being booked |
| `reservation_date` | `DATE` | - | Yes | - | Reservation date |
| `reservation_time` | `TIME` | - | Yes | - | Reservation time |
| `guest_count` | `INT UNSIGNED` | - | Yes | `2` | Number of guests |
| `status` | `ENUM('pending','confirmed','cancelled')` | - | Yes | `pending` | Reservation state |
| `notes` | `TEXT` | - | No | `NULL` | Customer notes |
| `created_at` | `TIMESTAMP` | - | Yes | `CURRENT_TIMESTAMP` | Reserved creation time |

**Relationships:**
- Many reservations are owned by one user.
- Many reservations belong to one restaurant.

### orders

**Purpose:** Stores order headers and billing summary information.

| Column | Type | Key | Required | Default | Notes |
|--------|------|-----|----------|---------|-------|
| `id` | `INT UNSIGNED` | PK | Yes | Auto-increment | Order ID |
| `user_id` | `INT UNSIGNED` | FK | Yes | - | Customer |
| `restaurant_id` | `INT UNSIGNED` | FK | Yes | - | Restaurant |
| `total_price` | `DECIMAL(10,2)` | - | Yes | `0.00` | Order total |
| `status` | `ENUM('pending','preparing','ready','completed','cancelled')` | - | Yes | `pending` | Order status |
| `order_notes` | `TEXT` | - | No | `NULL` | Optional notes |
| `created_at` | `TIMESTAMP` | - | Yes | `CURRENT_TIMESTAMP` | Order creation |
| `updated_at` | `TIMESTAMP` | - | Yes | `CURRENT_TIMESTAMP` | Last modification |

**Relationships:**
- Many orders belong to one user.
- Many orders belong to one restaurant.
- One order contains many order items.

### order_items

**Purpose:** Stores each menu item included in an order.

| Column | Type | Key | Required | Default | Notes |
|--------|------|-----|----------|---------|-------|
| `id` | `INT UNSIGNED` | PK | Yes | Auto-increment | Line item ID |
| `order_id` | `INT UNSIGNED` | FK | Yes | - | Parent order |
| `menu_item_id` | `INT UNSIGNED` | FK | Yes | - | Ordered item |
| `quantity` | `INT UNSIGNED` | - | Yes | `1` | Number of items |
| `unit_price` | `DECIMAL(10,2)` | - | Yes | - | Price at order time |

**Relationships:**
- Many order items belong to one order.
- Each order item references one menu item.

## 4. Cardinality summary

- `users` to `orders`: one-to-many
- `users` to `reservations`: one-to-many
- `restaurants` to `categories`: one-to-many
- `restaurants` to `menu_items`: one-to-many
- `restaurants` to `orders`: one-to-many
- `restaurants` to `reservations`: one-to-many
- `categories` to `menu_items`: one-to-many
- `orders` to `order_items`: one-to-many
- `menu_items` to `order_items`: one-to-many

## 5. Important indexes

- `uq_users_email` on `users.email`
- `uq_categories_restaurant_name` on `categories(restaurant_id, name)`
- `uq_menu_items_restaurant_name` on `menu_items(restaurant_id, name)`
- `idx_reservations_date` on `reservations(reservation_date)`
- `idx_orders_user` on `orders(user_id)`
- `idx_orders_status` on `orders(status)`

## 6. Design assumptions

- The system supports future restaurant ordering and booking workflows.
- The schema is intentionally minimal but scalable for educational use.
- Data validation and application-level logic remain the responsibility of PHP scripts.
