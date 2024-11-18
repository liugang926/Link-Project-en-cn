<?php
// 在文件顶部添加语言设置
require_once 'functions.php';
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('Notting WebStackPage 管理', 'Notting WebStackPage Admin'); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="./assets/css/admin-styles.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="fade-in"><?php echo t('WebStackPage 管理', 'WebStackPage Admin'); ?></h1>
        
        <nav class="nav-buttons fade-in">
            <a href="category_manage.php" class="btn btn-primary"><i class="fas fa-folder-open"></i> <?php echo t('管理类别', 'Manage Categories'); ?></a>
            <a href="admin.php?action=logout" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> <?php echo t('登出', 'Logout'); ?></a>
            <a href="?lang=<?php echo $lang == 'zh' ? 'en' : 'zh'; ?>" class="btn btn-info">
                <i class="fas fa-language"></i> <?php echo $lang == 'zh' ? 'Switch to English' : '切换到中文'; ?>
            </a>
            <a href="account_manage.php" class="btn btn-success"><i class="fas fa-user-cog"></i> <?php echo t('账户管理', 'Account Management'); ?></a>
        </nav>

        <?php
        $flashMessage = getFlashMessage();
        if ($flashMessage) {
            echo "<div class='alert alert-info fade-in'>$flashMessage</div>";
        }
        ?>

        <div class="page-section fade-in">
            <h2><?php echo $editLink ? t('编辑链接', 'Edit Link') : t('添加新链接', 'Add New Link'); ?></h2>
            <form method="post" id="linkForm" enctype="multipart/form-data">
                <?php if ($editLink): ?>
                    <input type="hidden" name="link_id" value="<?php echo $editLink['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="title_zh"><?php echo t('标题 (中文):', 'Title (Chinese):'); ?></label>
                    <input type="text" id="title_zh" name="title_zh" class="form-control" required
                           value="<?php echo $editLink ? htmlspecialchars($editLink['title_zh']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="title_en"><?php echo t('标题 (英文):', 'Title (English):'); ?></label>
                    <input type="text" id="title_en" name="title_en" class="form-control" required
                           value="<?php echo $editLink ? htmlspecialchars($editLink['title_en']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="url">URL:</label>
                    <input type="url" id="url" name="url" class="form-control" required
                           value="<?php echo $editLink ? htmlspecialchars($editLink['url']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="description_zh"><?php echo t('描述 (中文):', 'Description (Chinese):'); ?></label>
                    <textarea id="description_zh" name="description_zh" class="form-control"><?php echo $editLink ? htmlspecialchars($editLink['description_zh']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="description_en"><?php echo t('描述 (英文):', 'Description (English):'); ?></label>
                    <textarea id="description_en" name="description_en" class="form-control"><?php echo $editLink ? htmlspecialchars($editLink['description_en']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="category_id"><?php echo t('类别:', 'Category:'); ?></label>
                    <select id="category_id" name="category_id" class="form-control" required>
                        <?php 
                        $categories->data_seek(0);
                        while ($category = $categories->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo ($editLink && $editLink['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name_zh'] . ' / ' . $category['name_en']); ?>
                            </option>
                        <?php 
                        endwhile; 
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="icon"><?php echo t('图标:', 'Icon:'); ?></label>
                    <input type="file" id="icon" name="icon" class="form-control-file">
                    <?php if ($editLink && !empty($editLink['icon'])): ?>
                        <img src="<?php echo htmlspecialchars($editLink['icon']); ?>" alt="Current Icon" class="icon-preview mt-2">
                    <?php endif; ?>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="submit_link" class="btn btn-primary">
                        <i class="fas <?php echo $editLink ? 'fa-save' : 'fa-plus'; ?>"></i>
                        <?php echo $editLink ? t('更新', 'Update') : t('添加', 'Add'); ?>
                    </button>
                    <?php if ($editLink): ?>
                        <a href="admin.php" class="btn btn-secondary"><i class="fas fa-times"></i> <?php echo t('取消', 'Cancel'); ?></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="page-section fade-in">
            <h2><?php echo t('编辑链接', 'Edit Links'); ?></h2>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?php echo t('图标', 'Icon'); ?></th>
                            <th><?php echo t('类别', 'Category'); ?></th>
                            <th><?php echo t('标题 (中文/英文)', 'Title (Chinese/English)'); ?></th>
                            <th>URL</th>
                            <th><?php echo t('操作', 'Actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $current_category = '';
                        while ($link = $links->fetch_assoc()): 
                            if ($current_category != $link['category_zh']):
                                $current_category = $link['category_zh'];
                        ?>
                            <tr>
                                <td colspan="5" class="category-header"><?php echo htmlspecialchars($link['category_zh'] . ' / ' . $link['category_en']); ?></td>
                            </tr>
                        <?php 
                            endif;
                        ?>
                            <tr>
                                <td>
                                    <?php if (!empty($link['icon'])): ?>
                                        <img src="<?php echo htmlspecialchars($link['icon']); ?>" alt="Icon" class="icon-preview">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($link['category_zh'] . ' / ' . $link['category_en']); ?></td>
                                <td><?php echo htmlspecialchars($link['title_zh'] . ' / ' . $link['title_en']); ?></td>
                                <td><a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank"><?php echo htmlspecialchars($link['url']); ?></a></td>
                                <td>
                                    <a href="admin.php?action=edit&id=<?php echo $link['id']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> <?php echo t('编辑', 'Edit'); ?></a>
                                    <a href="admin.php?delete_link=<?php echo $link['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('<?php echo t('确定要删除这个链接吗？', 'Are you sure you want to delete this link?'); ?>');"><i class="fas fa-trash-alt"></i> <?php echo t('删除', 'Delete'); ?></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
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