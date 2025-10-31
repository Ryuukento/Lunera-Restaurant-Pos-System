CREATE DATABASE Paldo;
USE Paldo;

-- LOGIN TABLE
CREATE TABLE login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','cashier') NOT NULL DEFAULT 'cashier'
);

INSERT INTO login (username, password, role) VALUES
('Ryan', MD5('Mondido@06022005'), 'admin'),
('Mariah', MD5('Galising@12222004'), 'cashier');



CREATE TABLE menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) DEFAULT 'noimage.jpg',
    status ENUM('available','unavailable') DEFAULT 'available',
    category VARCHAR(50)
);

-- ORDERS TABLE
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    status ENUM('pending','completed','cancelled') DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES login(id) ON DELETE CASCADE
);

ALTER TABLE order_items ADD COLUMN sugar_level VARCHAR(10) DEFAULT '100%';

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_id INT NOT NULL,
    quantity INT DEFAULT 1,
    sugar_level VARCHAR(10) DEFAULT '100%',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menu(id) ON DELETE CASCADE
);


INSERT INTO menu (name, description, price, image, status, category) VALUES
-- Main Courses
('Crispy Pata','Crispy deep-fried pork leg served with soy-vinegar dip',799.00,'crispy_pata.jpg','available','Main Courses'),
('Beef Kare-Kare','Beef shank and vegetables in peanut sauce',850.00,'beef_karekare.jpg','available','Main Courses'),
('Seafood Paella','Spanish rice dish with shrimp, mussels, and squid',950.00,'seafood_paella.jpg','available','Main Courses'),
('Chicken Cordon Bleu','Chicken breast stuffed with ham and cheese',380.00,'chicken_cordonbleu.jpg','available','Main Courses'),
('Pasta Carbonara','Creamy pasta with bacon and parmesan cheese',250.00,'pasta_carbonara.jpg','available','Main Courses'),
('Grilled Tuna Belly','Fresh tuna belly grilled to perfection',420.00,'grilled_tuna.jpg','available','Main Courses'),
('Roast Chicken ','Roasted chicken infused with rosemary herbs',360.00,'roast_chicken.jpg','available','Main Courses'),
('Pork Sisig','Sizzling chopped pork face with egg and chili',299.00,'pork_sisig.jpg','available','Main Courses'),

-- Appetizers
('Caesar Salad Bites','Mini romaine lettuce with caesar dressing',180.00,'caesar_bites.jpg','available','Appetizers'),
('Calamares Fritos','squid rings that are battered deep-fried until golden crispy.',220.00,'calamares.jpg','available','Appetizers'),
('Shrimp Gambas','shrimp sautéed in olive oil with lots of garlic and chili peppers.',250.00,'shrimp_gambas.jpg','available','Appetizers'),
('Tuna Tartare','Fresh tuna cubes in citrus dressing',270.00,'tuna_tartare.jpg','available','Appetizers'),
('Vegetable Spring Rolls','Crispy spring rolls with sweet chili sauce',150.00,'spring_rolls.jpg','available','Appetizers'),
('Prosciutto-Wrapped Melon','Sweet melon wrapped in prosciutto ham',200.00,'prosciutto_melon.jpg','available','Appetizers'),
('Baked Oysters with Cheese','Fresh oysters baked with garlic and cheese',300.00,'baked_oysters.jpg','available','Appetizers'),
('Chicharon Bulaklak Crispy','Deep fried ruffled pork fat served with vinegar dip',250.00,'chicharon_bulaklak.jpg','available','Appetizers'),

-- Dessert
('Matcha Cheesecake','Creamy cheesecake infused with matcha',220.00,'matcha_cheesecake.jpg','available','Dessert'),
('Chocolate Lava Cake','Warm cake with molten chocolate center',180.00,'lava_cake.jpg','available','Dessert'),
('Chocolate Mousse','Layers of dark, milk and white chocolate mousse',200.00,'choco_mousse.jpg','available','Dessert'),
('Leche Flan','Traditional Filipino caramel custard dessert',120.00,'leche_flan.jpg','available','Dessert'),
('Halo-Halo Special','Mixed fruits, beans, and crushed ice dessert',150.00,'halo_halo.jpg','available','Dessert'),
('Caramel White Mocha','Sweet, creamy, and caramel-kissed over ice.',250.00,'Caramel White Mocha.jpg','available','Dessert'),
('Matcha Tiramisu','Tiramisu with matcha flavor twist',230.00,'matcha_tiramisu.jpg','available','Dessert'),
('Tiramisu', 'creamy layers of mascarpone and coffee-soaked ladyfingers.', 270.00, 'Tiramisu.jpg','available','Dessert'),

-- Beverages
('Pineapple Juice','Fresh pineapple juice',90.00,'pineapple_juice.jpg','available','Beverages'),
('Watermelon Shake','Fresh watermelon blended',100.00,'watermelon_shake.jpg','available','Beverages'),
('Iced Coffee Latte','Chilled coffee with milk and ice',110.00,'iced_coffee.jpg','available','Beverages'),
('Hot Chocolate','Creamy chocolate coffe',80.00,'hot_chocolate.jpg','available','Beverages'),
('Lemonade','Freshly squeezed lemon juice with syrup',90.00,'lemonade.jpg','available','Beverages'),
('Tang Lemon Iced Tea','Powdered/ready blend iced tea, budget size.',75.00,'Tang Lemon Iced Tea.jpg','available','Beverages'),
('Milk Tea with Pearls','Classic milk tea with tapioca pearls',120.00,'milk_tea.jpg','available','Beverages'),
('Dragon Fruit Shake','Bright pink dragon fruit blended drink',150.00,'dragonfruit_shake.jpg','available','Beverages');



