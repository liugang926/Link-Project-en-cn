<?php
require_once 'functions.php';
require_once 'db_setup.php';

// 启用错误报告
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

custom_error_log("脚本开始执行");

// 设置语言
$lang = isset($_GET['lang']) && $_GET['lang'] == 'en' ? 'en' : 'zh';

// 检查登录状态
checkLogin();

// 初始化变量
$error = null;
$success = false;
$edit_category = null;
$categories = null;

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    custom_error_log("收到 POST 请求");
    if (isset($_POST['submit_category'])) {
        custom_error_log("正在处理类别提交");
        $name_zh = $db->real_escape_string($_POST['name_zh']);
        $name_en = $db->real_escape_string($_POST['name_en']);
        $sort_order = (int)$_POST['sort_order'];
        
        if (!empty($name_zh) && !empty($name_en)) {
            if (isset($_POST['category_id'])) {
                // 更新现有类别
                $category_id = (int)$_POST['category_id'];
                $query = "UPDATE categories SET name_zh = '$name_zh', name_en = '$name_en', sort_order = $sort_order WHERE id = $category_id";
            } else {
                // 添加新类别
                $query = "INSERT INTO categories (name_zh, name_en, sort_order) VALUES ('$name_zh', '$name_en', $sort_order)";
            }
            
            custom_error_log("执行查询: " . $query);
            
            try {
                if ($db->query($query) === TRUE) {
                    custom_error_log("查询执行成功");
                    setFlashMessage(t("类别成功" . (isset($_POST['category_id']) ? "更新" : "添加") . "。", 
                                       "Category successfully " . (isset($_POST['category_id']) ? "updated" : "added") . "."));
                    $success = true;
                } else {
                    throw new Exception($db->error);
                }
            } catch (Exception $e) {
                custom_error_log("Error: " . $e->getMessage());
                $error = t("错误：" . $e->getMessage(), "Error: " . $e->getMessage());
            }
        } else {
            custom_error_log("类别名称为空");
            $error = t("中文和英文名称都是必需的。", "Both Chinese and English names are required.");
        }
    }
}

// 删除类别
if (isset($_GET['delete'])) {
    $category_id = (int)$_GET['delete'];
    $query = "DELETE FROM categories WHERE id = $category_id";
    custom_error_log("执行删除查询: " . $query);
    try {
        if ($db->query($query) === TRUE) {
            custom_error_log("类别删除成功");
            setFlashMessage(t("类别成功删除。", "Category deleted successfully."));
        } else {
            throw new Exception($db->error);
        }
    } catch (Exception $e) {
        custom_error_log("删除类别时出错: " . $e->getMessage());
        setFlashMessage(t("删除类别时出错：" . $e->getMessage(), "Error deleting category: " . $e->getMessage()));
    }
}

// 获取所有类别
$categories = $db->query("SELECT id, name_zh, name_en, sort_order FROM categories ORDER BY sort_order, name_zh");
custom_error_log("获取到的类别数量: " . $categories->num_rows);

// 编辑类别
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $result = $db->query("SELECT id, name_zh, name_en, sort_order FROM categories WHERE id = $edit_id");
    if ($result && $result->num_rows > 0) {
        $edit_category = $result->fetch_assoc();
        custom_error_log("正在编辑类别: " . print_r($edit_category, true));
    }
}

// HTML部分开始
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t("管理类别 - WebStackPage 管理", "Manage Categories - WebStackPage Admin"); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="./assets/css/admin-styles.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="text-center fade-in"><?php echo t("管理类别", "Manage Categories"); ?></h1>
        
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
            <h2><?php echo $edit_category ? t("编辑类别", "Edit Category") : t("添加新类别", "Add New Category"); ?></h2>
            <form method="post" id="categoryForm">
                <?php if ($edit_category): ?>
                    <input type="hidden" name="category_id" value="<?php echo $edit_category['id']; ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label for="name_zh"><?php echo t("类别名称（中文）:", "Category Name (Chinese):"); ?></label>
                    <input type="text" id="name_zh" name="name_zh" class="form-control" required
                           value="<?php echo $edit_category ? htmlspecialchars($edit_category['name_zh']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="name_en"><?php echo t("类别名称（英文）:", "Category Name (English):"); ?></label>
                    <input type="text" id="name_en" name="name_en" class="form-control" required
                           value="<?php echo $edit_category ? htmlspecialchars($edit_category['name_en']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="sort_order"><?php echo t("排序:", "Sort Order:"); ?></label>
                    <input type="number" id="sort_order" name="sort_order" class="form-control" required
                           value="<?php echo $edit_category ? htmlspecialchars($edit_category['sort_order']) : '0'; ?>">
                </div>
                <div class="form-actions">
                    <button type="submit" name="submit_category" class="btn btn-primary">
                        <i class="fas <?php echo $edit_category ? 'fa-save' : 'fa-plus'; ?>"></i>
                        <?php echo $edit_category ? t("更新类别", "Update Category") : t("添加类别", "Add Category"); ?>
                    </button>
                    <?php if ($edit_category): ?>
                        <a href="category_manage.php?lang=<?php echo $lang; ?>" class="btn btn-secondary"><i class="fas fa-times"></i> <?php echo t("取消", "Cancel"); ?></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="page-section fade-in">
            <h2><?php echo t("现有类别", "Existing Categories"); ?></h2>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?php echo t("排序", "Sort Order"); ?></th>
                            <th><?php echo t("类别名称（中文）", "Category Name (Chinese)"); ?></th>
                            <th><?php echo t("类别名称（英文）", "Category Name (English)"); ?></th>
                            <th><?php echo t("操作", "Action"); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($categories && $categories->num_rows > 0):
                            while ($category = $categories->fetch_assoc()): 
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($category['sort_order']); ?></td>
                                <td><?php echo htmlspecialchars($category['name_zh']); ?></td>
                                <td><?php echo htmlspecialchars($category['name_en']); ?></td>
                                <td>
                                    <a href="category_manage.php?edit=<?php echo $category['id']; ?>&lang=<?php echo $lang; ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> <?php echo t("编辑", "Edit"); ?></a>
                                    <a href="category_manage.php?delete=<?php echo $category['id']; ?>&lang=<?php echo $lang; ?>" class="btn btn-sm btn-danger" onclick="return confirm('<?php echo t("确定要删除这个类别吗？这将同时删除该类别下的所有链接。", "Are you sure you want to delete this category? This will also delete all links in this category."); ?>');"><i class="fas fa-trash-alt"></i> <?php echo t("删除", "Delete"); ?></a>
                                </td>
                            </tr>
                        <?php 
                            endwhile; 
                        else:
                        ?>
                            <tr>
                                <td colspan="4"><?php echo t("暂无类别", "No categories found"); ?></td>
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
<?php
$db->close();
custom_error_log("脚本执行结束");
?>