<?php
$login_routes = [
    "/set-language/{lang}" => "Lang_ctrl@set_lang@name.setLang",
    "/logout" => 'Auth@logout@name.logout',
    "/user-login-ajax" => 'Auth@user_login@name.userLoginAjax',
    "/login" => 'Auth@user_login_page@name.userLogin',
    "/admin-login" => 'Auth@admin_login_page@name.adminLogin',
    "/admin-login-ajax" => 'Auth@admin_login@name.adminLoginAjax',
    "/register" => 'Game_auth_ctrl@registration_page@name.register',
    "/check-status-page/{pid}" => 'Game_auth_ctrl@check_status_page@name.checkStatusPage',
    "/user-registration-ajax" => 'Game_auth_ctrl@register@name.registerAjax',
    "/reset-password" => 'Game_auth_ctrl@reset_password_page@name.resetPassword',
    "/reset-password-ajax" => 'Game_auth_ctrl@reset_password_ajax@name.resetPasswordAjax',
    "/cnp/{prt}" => 'Game_auth_ctrl@create_new_password_page@name.createNewPassword',
    "/send-temp-pass-on-ajax" => 'Game_auth_ctrl@send_me_temp_password_ajax@name.sendMeTempPassAjax',
    "/send-otp-on-ajax" => 'Game_auth_ctrl@send_otp@name.sendOtpAjax',
];