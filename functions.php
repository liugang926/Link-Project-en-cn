<?php
// 检查并确保会话已启动
if (!function_exists('ensure_session_started')) {
    function ensure_session_started() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
}

// 设置闪存消息
if (!function_exists('setFlashMessage')) {
    function setFlashMessage($message) {
        ensure_session_started();
        $_SESSION['flash_message'] = $message;
    }
}

// 获取闪存消息
if (!function_exists('getFlashMessage')) {
    function getFlashMessage() {
        ensure_session_started();
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            return $message;
        }
        return null;
    }
}

// 检查登录状态
if (!function_exists('checkLogin')) {
    function checkLogin() {
        ensure_session_started();
        if (!isset($_SESSION['user_id'])) {
            header('Location: admin.php?action=login&lang=' . $GLOBALS['lang']);
            exit();
        }
    }
}

// 翻译函数
if (!function_exists('t')) {
    function t($zh, $en) {
        global $lang;
        return $lang == 'zh' ? $zh : $en;
    }
}

// 自定义错误日志函数
if (!function_exists('custom_error_log')) {
    function custom_error_log($message) {
        error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, 'error.log');
    }
}

// 可以在这里添加其他共享函数
?>