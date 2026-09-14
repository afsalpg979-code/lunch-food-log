<?php
require_once __DIR__ . '/database.php';

function setCaptcha():void{$a=random_int(2,9);$b=random_int(2,9);$_SESSION['captcha_question']="$a + $b";$_SESSION['captcha_answer']=(string)($a+$b);}
function captchaQuestion():string{if(empty($_SESSION['captcha_answer']))setCaptcha();return $_SESSION['captcha_question'].' = ?';}
function verifyCaptcha(string $answer):bool{$ok=hash_equals((string)($_SESSION['captcha_answer']??''),trim($answer));unset($_SESSION['captcha_answer'],$_SESSION['captcha_question']);return $ok;}

function currentUser():?array{
    if(empty($_SESSION['user_id'])) return null;
    $stmt=$GLOBALS['db']->prepare('SELECT id,name,email,password_hash,profile_photo,created_at,updated_at FROM users WHERE id=? LIMIT 1');
    $stmt->execute([(int)$_SESSION['user_id']]);
    return $stmt->fetch()?:null;
}

function loginUser(int $id):void{
    session_regenerate_id(true);
    $_SESSION['user_id']=$id;
    $_SESSION['last_activity']=time();
}

function requireLogin():void{
    $timeout=60*60*4;
    if(!empty($_SESSION['last_activity']) && time()-(int)$_SESSION['last_activity']>$timeout){
        $_SESSION=[];
        session_destroy();
        header('Location: login.php?expired=1');exit;
    }
    $user=currentUser();
    if(!$user){unset($_SESSION['user_id']);header('Location: login.php');exit;}
    $_SESSION['last_activity']=time();
}
