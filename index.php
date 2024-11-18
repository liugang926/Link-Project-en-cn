<?php
require_once 'db_setup.php';

// 设置默认语言
$lang = isset($_GET['lang']) && $_GET['lang'] == 'en' ? 'en' : 'zh';

// 获取所有类别和链接，按照 sort_order 升序排列
$categories = $db->query("SELECT id, name_$lang AS name, sort_order FROM categories ORDER BY sort_order ASC, name_$lang");
$links = $db->query("SELECT l.id, l.title_$lang AS title, l.url, l.description_$lang AS description, l.icon, c.name_$lang AS category, c.sort_order 
                     FROM links l 
                     JOIN categories c ON l.category_id = c.id 
                     ORDER BY c.sort_order ASC, c.name_$lang, l.title_$lang");

// 将链接按类别组织
$categorized_links = [];
while ($link = $links->fetch_assoc()) {
    $categorized_links[$link['category']][] = $link;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Notting Link</title>
    <meta name="keywords" content="网址导航">
    <meta name="description" content="网址导航">
    <link rel="shortcut icon" href="assets/images/favicon.png">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Arimo:400,700,400italic">
    <link rel="stylesheet" href="assets/css/fonts/linecons/css/linecons.css">
    <link rel="stylesheet" href="assets/css/fonts/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/xenon-core.css">
    <link rel="stylesheet" href="assets/css/xenon-components.css">
    <link rel="stylesheet" href="assets/css/xenon-skins.css">
    <link rel="stylesheet" href="assets/css/nav.css">
    <script src="assets/js/jquery-1.11.1.min.js"></script>
</head>

<body class="page-body">
    <div class="page-container">
        <div class="sidebar-menu toggle-others fixed">
            <div class="sidebar-menu-inner">
                <header class="logo-env">
                    <div class="logo">
                        <a href="index.php" class="logo-expanded">
                            <img src="assets/images/logo@2x.png" width="100%" alt="" />
                        </a>
                    </div>
                </header>
                
                <ul id="main-menu" class="main-menu">
                    <?php while ($category = $categories->fetch_assoc()): ?>
                        <li>
                            <a href="#<?php echo $category['id']; ?>" class="smooth">
                                <i class="linecons-star"></i>
                                <span class="title"><?php echo htmlspecialchars($category['name']); ?></span>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>
        <div class="main-content">
            <nav class="navbar user-info-navbar" role="navigation">
                <!-- 顶部导航栏 -->
                <ul class="user-info-menu right-links list-inline list-unstyled">
                    <li>
                        <a href="?lang=<?php echo $lang == 'zh' ? 'en' : 'zh'; ?>">
                            <?php echo $lang == 'zh' ? 'English' : '中文'; ?>
                        </a>
                    </li>
                </ul>
            </nav>

            <?php
            $categories->data_seek(0); // 重置类别结果集指针
            while ($category = $categories->fetch_assoc()):
                if (isset($categorized_links[$category['name']])):
            ?>
                <h4 class="text-gray"><i class="linecons-tag" style="margin-right: 7px;" id="<?php echo $category['id']; ?>"></i><?php echo htmlspecialchars($category['name']); ?></h4>
                <div class="row">
                    <?php foreach ($categorized_links[$category['name']] as $link): ?>
                        <div class="col-sm-3">
                            <div class="xe-widget xe-conversations box2 label-info" onclick="window.open('<?php echo $link['url']; ?>', '_blank')" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="<?php echo $link['url']; ?>">
                                <div class="xe-comment-entry">
                                    <a class="xe-user-img">
                                    <img src="<?php echo $link['icon'] ? $link['icon'] : 'assets/images/default-icon.png'; ?>" class="img-circle" width="40">
                                    </a>
                                    <div class="xe-comment">
                                        <a href="#" class="xe-user-name overflowClip_1">
                                            <strong><?php echo htmlspecialchars($link['title']); ?></strong>
                                        </a>
                                        <p class="overflowClip_2"><?php echo htmlspecialchars($link['description']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <br />
            <?php
                endif;
            endwhile;
            ?>

            <!-- Footer -->
            <footer class="main-footer sticky footer-type-1">
                <div class="footer-inner">
                    <div class="footer-text">
                        &copy; 2024 <strong>Notting IT Team</strong>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- 底部js -->
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/TweenMax.min.js"></script>
    <script src="assets/js/resizeable.js"></script>
    <script src="assets/js/joinable.js"></script>
    <script src="assets/js/xenon-api.js"></script>
    <script src="assets/js/xenon-toggles.js"></script>
    <script src="assets/js/xenon-custom.js"></script>
</body>
</html>