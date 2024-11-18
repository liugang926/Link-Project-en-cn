<?php
// 检查会话是否已经启动
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_setup.php';

// 设置默认语言
$lang = isset($_GET['lang']) && $_GET['lang'] == 'en' ? 'en' : 'zh';

// 登录处理
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 输入验证
    if (empty($username) || empty($password)) {
        $error = "用户名和密码不能为空。";
    } else {
        // 使用准备好的语句防止 SQL 注入
        $stmt = $db->prepare("SELECT password_hash FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($password_hash);
            $stmt->fetch();

            // 验证密码
            if (password_verify($password, $password_hash)) {
                // 登录成功，设置会话
                $_SESSION['username'] = $username;
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "用户名或密码错误。";
            }
        } else {
            $error = "用户名或密码错误。";
        }
        $stmt->close();
    }
}

// CSRF 令牌
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebStackPage Admin Login</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <style>
        body {
            background-image: url('assets/images/background.jpg'); /* 背景图像 */
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-form {
            background-color: rgba(255, 255, 255, 0.9); /* 半透明背景 */
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 300px;
            opacity: 0; /* 初始透明度 */
            transform: translateY(-20px); /* 初始位置 */
            animation: fadeInUp 0.5s forwards; /* 动画效果 */
        }
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .login-form h2 {
            margin-bottom: 20px;
            text-align: center;
        }
        .form-control {
            transition: border-color 0.3s;
        }
        .form-control:focus {
            border-color: #007bff; /* 聚焦时边框颜色 */
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5); /* 聚焦时阴影 */
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #0056b3; /* 悬停效果 */
        }
        .loading {
            display: none; /* 初始隐藏 */
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="login-form">
        <h2>Admin Login</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <form method="post" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary btn-block">Login</button>
            <div class="loading" id="loading">Loading...</div>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            $('#loginForm').on('submit', function() {
                $('#loading').show(); // 显示加载动画
            });
        });
    </script>
</body>
</html>