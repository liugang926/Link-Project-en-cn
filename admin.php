<?php
session_start();
require_once 'db_setup.php';
require_once 'functions.php';
ensure_session_started();
$lang = isset($_GET['lang']) && $_GET['lang'] == 'en' ? 'en' : 'zh';

// CSRF 令牌生成
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// 检查 CSRF 令牌
function checkCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// 登录尝试限制
$maxLoginAttempts = 5;
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

// 登录处理
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    if ($_SESSION['login_attempts'] >= $maxLoginAttempts) {
        $error = "Too many login attempts. Please try again later.";
    } else {
        $username = $db->real_escape_string($_POST['username']);
        $password = $_POST['password'];
        $csrf_token = $_POST['csrf_token'];

        if (!checkCsrfToken($csrf_token)) {
            $error = "Invalid CSRF token.";
        } else {
            $result = $db->query("SELECT id, password FROM users WHERE username = '$username'");
            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['login_attempts'] = 0; // 重置登录尝试计数
                    header('Location: admin.php');
                    exit();
                }
            }
            $_SESSION['login_attempts']++;
            $error = "Invalid username or password";
        }
    }
}

// 管理页面
if (!isset($_GET['action']) || $_GET['action'] != 'login') {
    checkLogin();
    
    $editLink = null;
    if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
        $linkId = (int)$_GET['id'];
        $result = $db->query("SELECT * FROM links WHERE id = $linkId");
        if ($result->num_rows > 0) {
            $editLink = $result->fetch_assoc();
        }
    }

    // 添加/编辑链接
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_link'])) {
        $title_zh = $db->real_escape_string($_POST['title_zh']);
        $title_en = $db->real_escape_string($_POST['title_en']);
        $url = $db->real_escape_string($_POST['url']);
        $description_zh = $db->real_escape_string($_POST['description_zh']);
        $description_en = $db->real_escape_string($_POST['description_en']);
        $category_id = (int)$_POST['category_id'];
        
        // 处理图标上传
        $icon_path = '';
        if (isset($_FILES['icon']) && $_FILES['icon']['error'] == 0) {
            $allowed = array('jpg', 'jpeg', 'png', 'gif');
            $filename = $_FILES['icon']['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if (in_array(strtolower($ext), $allowed)) {
                $icon_path = 'assets/images/logos/' . time() . '.' . $ext;
                move_uploaded_file($_FILES['icon']['tmp_name'], $icon_path);
            }
        }
        
        if (isset($_POST['link_id'])) {
            $link_id = (int)$_POST['link_id'];
            $query = "UPDATE links SET title_zh = '$title_zh', title_en = '$title_en', url = '$url', 
                      description_zh = '$description_zh', description_en = '$description_en', 
                      category_id = $category_id" . ($icon_path ? ", icon = '$icon_path'" : "") . " WHERE id = $link_id";
        } else {
            $query = "INSERT INTO links (title_zh, title_en, url, description_zh, description_en, category_id" . ($icon_path ? ", icon" : "") . ") 
                      VALUES ('$title_zh', '$title_en', '$url', '$description_zh', '$description_en', $category_id" . ($icon_path ? ", '$icon_path'" : "") . ")";
        }
        
        if ($db->query($query) === TRUE) {
            setFlashMessage("Link successfully " . (isset($_POST['link_id']) ? "updated" : "added") . ".");
        } else {
            setFlashMessage("Error: " . $db->error);
        }
        
        header('Location: admin.php');
        exit();
    }
    
    // 删除链接
    if (isset($_GET['delete_link'])) {
        $link_id = (int)$_GET['delete_link'];
        if ($db->query("DELETE FROM links WHERE id = $link_id")) {
            setFlashMessage("Link successfully deleted.");
        } else {
            setFlashMessage("Error deleting link: " . $db->error);
        }
        header('Location: admin.php');
        exit();
    }
    
    // 获取所有链接
    $links = $db->query("SELECT l.id, l.title_zh, l.title_en, l.url, l.description_zh, l.description_en, l.icon, 
                         c.name_zh as category_zh, c.name_en as category_en 
                         FROM links l 
                         JOIN categories c ON l.category_id = c.id 
                         ORDER BY c.name_zh, l.title_zh");
    
    // 获取所有类别
    $categories = $db->query("SELECT id, name_zh, name_en FROM categories ORDER BY name_zh");
    
    include 'admin_template.php';
} else {
    include 'login_template.php';
}

// 登出
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header('Location: admin.php?action=login');
    exit();
}

$db->close();
?>