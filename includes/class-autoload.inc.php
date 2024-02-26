<?php 
spl_autoload_register('classLoader');
spl_autoload_register('controllersLoader');
spl_autoload_register('eventControllersLoader');
spl_autoload_register('adminControllersLoader');
spl_autoload_register('userControllersLoader');
spl_autoload_register('apiControllersLoader');
spl_autoload_register('cmdControllersLoader');
spl_autoload_register('frontControllersLoader');
spl_autoload_register('travelControllersLoader');
spl_autoload_register('gamesControllersLoader');
spl_autoload_register('pgControllersLoader');
spl_autoload_register('smsControllersLoader');

function classLoader($className){
    $path = RPATH ."/classes/";
    $extension = ".class.php";
    $fullPath = $path . $className . $extension;
    if(file_exists($fullPath)){
        include_once $fullPath;
    }else{
        return false;
    }
}
function controllersLoader($className){
    $path = RPATH ."/controllers/";
    $extension = ".ctrl.php";
    $fullPath = $path . $className . $extension;
    
    if(file_exists($fullPath)){
        include_once $fullPath;
    }else{
        return false;
    }
}
function eventControllersLoader($className){
    $path = RPATH ."/controllers/events/";
    $extension = ".event.php";
    $fullPath = $path . $className . $extension;
    
    if(file_exists($fullPath)){
        include_once $fullPath;
    }else{
        return false;
    }
}
function adminControllersLoader($className){
    $path = RPATH ."/controllers/admin/";
    $extension = ".ctrl.php";
    $fullPath = $path . $className . $extension;
    
    if(file_exists($fullPath)){
        include_once $fullPath;
    }else{
        return false;
    }
}
function userControllersLoader($className){
    $path = RPATH ."/controllers/user/";
    $extension = ".ctrl.php";
    $fullPath = $path . $className . $extension;
    
    if(file_exists($fullPath)){
        include_once $fullPath;
    }else{
        return false;
    }
}
function apiControllersLoader($className){
    $path = RPATH ."/controllers/api/";
    $extension = ".api.php";
    $fullPath = $path . $className . $extension;
    
    if(file_exists($fullPath)){
        include_once $fullPath;
    }else{
        return false;
    }
}
function cmdControllersLoader($className){
    $path = RPATH ."/controllers/cmd/";
    $extension = ".cmd.php";
    $fullPath = $path . $className . $extension;
    
    if(file_exists($fullPath)){
        include_once $fullPath;
    }else{
        return false;
    }
}
function frontControllersLoader($className){
    $path = RPATH ."/controllers/front/";
    $extension = ".front.php";
    $fullPath = $path . $className . $extension;
    
    if(file_exists($fullPath)){
        include_once $fullPath;
    }else{
        return false;
    }
}
function travelControllersLoader($className){
    $path = RPATH ."/controllers/travel/";
    $extension = ".travel.php";
    $fullPath = $path . $className . $extension;
    
    if(file_exists($fullPath)){
        include_once $fullPath;
    }else{
        return false;
    }
}
function gamesControllersLoader($className){
    $path = RPATH ."/controllers/games/";
    $extension = ".game.php";
    $fullPath = $path . $className . $extension;
    
    if(file_exists($fullPath)){
        include_once $fullPath;
    }else{
        return false;
    }
}
function pgControllersLoader($className){
    $path = RPATH ."/controllers/pg/";
    $extension = ".pg.php";
    $fullPath = $path . $className . $extension;
    
    if(file_exists($fullPath)){
        include_once $fullPath;
    }else{
        return false;
    }
}
function smsControllersLoader($className){
    $path = RPATH ."/controllers/sms/";
    $extension = ".sms.php";
    $fullPath = $path . $className . $extension;
    
    if(file_exists($fullPath)){
        include_once $fullPath;
    }else{
        return false;
    }
}
$GLOBALS['PDO'] = (new Dbh)->conn();
?>