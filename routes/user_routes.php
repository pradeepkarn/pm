<?php
$user_routes = [
    '/user' => 'User_ctrl@index@name.userhome',
    '/user/supports/{cg}/create' => 'Support_user_ctrl@create@name.userSupportCreate',
    '/user/supports/{cg}/list' => 'Support_user_ctrl@list@name.userSupportList',
    '/user/supports/{cg}/trash-list' => 'Support_user_ctrl@trash_list@name.userSupportTrashList',
    '/user/supports/{cg}/trash/{id}' => 'Support_user_ctrl@move_to_trash@name.userSupportTrash',
    '/user/supports/{cg}/restore/{id}' => 'Support_user_ctrl@restore@name.userSupportRestore',
    '/user/supports/{cg}/delete/{id}' => 'Support_user_ctrl@delete_trash@name.userSupportDelete',
    '/user/supports/{cg}/edit/{id}' => 'Support_user_ctrl@edit@name.userSupportEdit',
    '/user/supports/{cg}/edit/{id}/save-by-ajax' => 'Support_user_ctrl@update@name.userSupportUpdateAjax',
    '/user/supports/{cg}/toggle-marked-support' => 'Support_user_ctrl@toggle_approve@name.userSupportToggleMarked',
    '/user/supports/{cg}/toggle-closed-support' => 'Support_user_ctrl@toggle_closed@name.userSupportToggleClosed',
];
