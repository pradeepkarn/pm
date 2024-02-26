<?php
$game_routes = [
    "/game-register/{gameid}" => 'Game_auth_ctrl@game_registration_page@name.gameRegister',
    "/game-registration-ajax" => 'Game_auth_ctrl@game_register@name.gameRegisterAjax',
];