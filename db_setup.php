<?php
function setupDatabase($host, $user, $password, $dbname) {
    $conn = new mysqli($host, $user, $password);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql) === FALSE) {
        die("Error creating database: " . $conn->error);
    }

    $conn->select_db($dbname);

    $sql = "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name_zh VARCHAR(255) NOT NULL,
        name_en VARCHAR(255) NOT NULL,
        UNIQUE KEY (name_zh, name_en)
    )";
    if ($conn->query($sql) === FALSE) {
        die("Error creating table categories: " . $conn->error);
    }

    $sql = "CREATE TABLE IF NOT EXISTS links (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title_zh VARCHAR(255) NOT NULL,
        title_en VARCHAR(255) NOT NULL,
        url VARCHAR(255) NOT NULL,
        description_zh TEXT,
        description_en TEXT,
        category_id INT NOT NULL,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    )";
    if ($conn->query($sql) === FALSE) {
        die("Error creating table links: " . $conn->error);
    }

    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL
    )";
    if ($conn->query($sql) === FALSE) {
        die("Error creating table users: " . $conn->error);
    }

    $result = $conn->query("SELECT * FROM users WHERE username = 'admin'");
    if ($result->num_rows == 0) {
        $username = 'admin';
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        
        // 使用准备好的语句插入用户
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        if ($stmt === FALSE) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("ss", $username, $password);
        if ($stmt->execute() === FALSE) {
            die("Error creating default admin user: " . $stmt->error);
        }
        $stmt->close();
        
        echo "Default admin user created. Username: admin, Password: admin123. Please change the password immediately.<br>";
    }

    $conn->close();
    return true;
}

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'webstack_db';

setupDatabase($host, $user, $password, $dbname);

$db = new mysqli($host, $user, $password, $dbname);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
$db->set_charset("utf8mb4");

function updateDatabase($db) {
    $result = $db->query("SHOW COLUMNS FROM categories LIKE 'name_zh'");
    if ($result->num_rows == 0) {
        // 使用准备好的语句更新表
        $stmt = $db->prepare("ALTER TABLE categories 
                    ADD COLUMN name_zh VARCHAR(255) NOT NULL,
                    ADD COLUMN name_en VARCHAR(255) NOT NULL,
                    DROP COLUMN IF EXISTS name,
                    ADD UNIQUE KEY (name_zh, name_en)");
        if ($stmt->execute() === FALSE) {
            die("Error updating categories table: " . $stmt->error);
        }
        $stmt->close();
        
        $stmt = $db->prepare("ALTER TABLE links 
                    ADD COLUMN category_id INT,
                    ADD FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
                    DROP COLUMN IF EXISTS category");
        if ($stmt->execute() === FALSE) {
            die("Error updating links table: " . $stmt->error);
        }
        $stmt->close();
        
        echo "Updated categories and links tables for multilingual support.<br>";
    }
}

updateDatabase($db);
?>