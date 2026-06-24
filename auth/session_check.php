<?php
// Kiểm tra nếu chưa có session nhưng có cookie
if (!isset($_SESSION['user']) && isset($_COOKIE['logged_in_user'])) {
    $cookieData = json_decode($_COOKIE['logged_in_user'], true);
    
    if ($cookieData && isset($cookieData['username'])) {
        // Tự động khôi phục session từ cookie
        $_SESSION['user'] = [
            'username' => $cookieData['username'],
            'role'     => $cookieData['role']
        ];
    }
}
?>