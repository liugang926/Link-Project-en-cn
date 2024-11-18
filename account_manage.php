<?php
require_once 'functions.php';
require_once 'db_setup.php';

ensure_session_started();
checkLogin();

$lang = isset($_GET['lang']) && $_GET['lang'] == 'en' ? 'en' : 'zh';

$error = null;
$success = false;
$editUser = null;

// 处理密码修改
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    // 验证当前密码
    $result = $db->query("SELECT password FROM users WHERE id = $user_id");
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_new_password) {
                $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                $query = "UPDATE users SET password = ? WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->bind_param("si", $hashed_new_password, $user_id);
                if ($stmt->execute()) {
                    setFlashMessage(t("密码已成功更新。", "Password has been successfully updated."));
                    $success = true;
                } else {
                    $error = t("更新密码时出错：" . $stmt->error, "Error updating password: " . $stmt->error);
                }
                $stmt->close();
            } else {
                $error = t("新密码和确认密码不匹配。", "New password and confirmation do not match.");
            }
        } else {
            $error = t("当前密码不正确。", "Current password is incorrect.");
        }
    } else {
        $error = t("用户不存在。", "User does not exist.");
    }
}

// 处理用户管理表单提交
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_user'])) {
    $username = $db->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = t("密码不匹配", "Passwords do not match");
    } else if (empty($username) || empty($password)) {
        $error = t("用户名和密码都是必需的", "Both username and password are required");
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        if (isset($_POST['user_id'])) {
            // 更新现有用户
            $user_id = (int)$_POST['user_id'];
            $query = "UPDATE users SET username = ?, password = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bind_param("ssi", $username, $hashed_password, $user_id);
        } else {
            // 添加新用户
            $query = "INSERT INTO users (username, password) VALUES (?, ?)";
            $stmt = $db->prepare($query);
            $stmt->bind_param("ss", $username, $hashed_password);
        }
        
        if ($stmt->execute()) {
            setFlashMessage(t("用户成功" . (isset($_POST['user_id']) ? "更新" : "添加") . "。", 
                               "User successfully " . (isset($_POST['user_id']) ? "updated" : "added") . "."));
            $success = true;
        } else {
            $error = t("错误：" . $stmt->error, "Error: " . $stmt->error);
        }
        $stmt->close();
    }
}

// 删除用户
if (isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    $query = "DELETE FROM users WHERE id = ? AND username != 'admin'";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        setFlashMessage(t("用户成功删除。", "User deleted successfully."));
    } else {
        setFlashMessage(t("删除用户时出错：" . $stmt->error, "Error deleting user: " . $stmt->error));
    }
    $stmt->close();
    header('Location: account_manage.php');
    exit();
}

// 编辑用户
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $result = $db->query("SELECT id, username FROM users WHERE id = $edit_id");
    if ($result && $result->num_rows > 0) {
        $editUser = $result->fetch_assoc();
    }
}

// 获取所有用户
$users = $db->query("SELECT id, username FROM users ORDER BY username");

?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t("账户管理 - WebStackPage 管理", "Account Management - WebStackPage Admin"); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="./assets/css/admin-styles.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="text-center fade-in"><?php echo t("账户管理", "Account Management"); ?></h1>
        
        <nav class="nav-buttons fade-in">
            <a href="admin.php?lang=<?php echo $lang; ?>" class="btn btn-primary"><i class="fas fa-arrow-left"></i> <?php echo t("返回管理页面", "Back to Admin"); ?></a>
            <a href="admin.php?action=logout&lang=<?php echo $lang; ?>" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> <?php echo t("登出", "Logout"); ?></a>
            <a href="?lang=<?php echo $lang == 'zh' ? 'en' : 'zh'; ?>" class="btn btn-info">
                <i class="fas fa-language"></i> <?php echo $lang == 'zh' ? 'Switch to English' : '切换到中文'; ?>
            </a>
        </nav>

        <?php
        $flashMessage = getFlashMessage();
        if ($flashMessage) {
            echo "<div class='alert alert-info fade-in'>$flashMessage</div>";
        }
        if ($error) {
            echo "<div class='alert alert-danger fade-in'>$error</div>";
        }
        ?>

        <div class="page-section fade-in">
            <h2><?php echo t("修改密码", "Change Password"); ?></h2>
            <form method="post" id="changePasswordForm">
                <div class="form-group">
                    <label for="current_password"><?php echo t("当前密码:", "Current Password:"); ?></label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="new_password"><?php echo t("新密码:", "New Password:"); ?></label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="confirm_new_password"><?php echo t("确认新密码:", "Confirm New Password:"); ?></label>
                    <input type="password" id="confirm_new_password" name="confirm_new_password" class="form-control" required>
                </div>
                <div class="form-actions">
                    <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="fas fa-key"></i> <?php echo t("更改密码", "Change Password"); ?>
                    </button>
                </div>
            </form>
        </div>

        <div class="page-section fade-in">
            <h2><?php echo $editUser ? t("编辑用户", "Edit User") : t("添加新用户", "Add New User"); ?></h2>
            <form method="post" id="userForm">
                <?php if ($editUser): ?>
                    <input type="hidden" name="user_id" value="<?php echo $editUser['id']; ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label for="username"><?php echo t("用户名:", "Username:"); ?></label>
                    <input type="text" id="username" name="username" class="form-control" required
                           value="<?php echo $editUser ? htmlspecialchars($editUser['username']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="password"><?php echo t("密码:", "Password:"); ?></label>
                    <input type="password" id="password" name="password" class="form-control" <?php echo $editUser ? '' : 'required'; ?>>
                </div>
                <div class="form-group">
                    <label for="confirm_password"><?php echo t("确认密码:", "Confirm Password:"); ?></label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" <?php echo $editUser ? '' : 'required'; ?>>
                </div>
                <div class="form-actions">
                    <button type="submit" name="submit_user" class="btn btn-primary">
                        <i class="fas <?php echo $editUser ? 'fa-save' : 'fa-plus'; ?>"></i>
                        <?php echo $editUser ? t("更新用户", "Update User") : t("添加用户", "Add User"); ?>
                    </button>
                    <?php if ($editUser): ?>
                        <a href="account_manage.php?lang=<?php echo $lang; ?>" class="btn btn-secondary"><i class="fas fa-times"></i> <?php echo t("取消", "Cancel"); ?></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="page-section fade-in">
            <h2><?php echo t("现有用户", "Existing Users"); ?></h2>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?php echo t("用户名", "Username"); ?></th>
                            <th><?php echo t("操作", "Actions"); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($users && $users->num_rows > 0):
                            while ($user = $users->fetch_assoc()): 
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td>
                                    <a href="account_manage.php?edit=<?php echo $user['id']; ?>&lang=<?php echo $lang; ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> <?php echo t("编辑", "Edit"); ?></a>
                                    <?php if ($user['username'] !== 'admin'): ?>
                                        <a href="account_manage.php?delete=<?php echo $user['id']; ?>&lang=<?php echo $lang; ?>" class="btn btn-sm btn-danger" onclick="return confirm('<?php echo t("确定要删除这个用户吗？", "Are you sure you want to delete this user?"); ?>');"><i class="fas fa-trash-alt"></i> <?php echo t("删除", "Delete"); ?></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php 
                            endwhile; 
                        else:
                        ?>
                            <tr>
                                <td colspan="2"><?php echo t("暂无用户", "No users found"); ?></td>
                            </tr>
                        <?php
                        endif;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="./assets/js/jquery.min.js"></script>
    <script src="./assets/js/popper.min.js"></script>
    <script src="./assets/js/bootstrap.min.js"></script>
    <script>
        const LANG = '<?php echo $lang; ?>';
    </script>
    <script src="./assets/js/admin-scripts.js"></script>
</body>
</html>