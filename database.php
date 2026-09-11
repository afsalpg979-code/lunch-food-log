<?php
// Keep application timestamps aligned with the user's local timezone.
date_default_timezone_set('Asia/Kolkata');
if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);
    session_start();
}
$dataDir=__DIR__.'/data';
if(!is_dir($dataDir)&&!mkdir($dataDir,0775,true)) throw new RuntimeException('Unable to create data directory.');
$db=new PDO('sqlite:'.$dataDir.'/lunch.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION); $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
$db->exec('PRAGMA foreign_keys = ON'); $db->exec('PRAGMA busy_timeout = 5000'); $db->exec('PRAGMA journal_mode = WAL');
$db->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT NOT NULL,email TEXT NOT NULL UNIQUE,password_hash TEXT NOT NULL,created_at TEXT NOT NULL,updated_at TEXT NOT NULL)");
$db->exec("CREATE TABLE IF NOT EXISTS lunches (id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER,lunch_date TEXT NOT NULL,food_item TEXT NOT NULL,quantity TEXT DEFAULT '',notes TEXT DEFAULT '',meal_type TEXT DEFAULT 'Lunch',meal_time TEXT DEFAULT '12:00-13:00',recorded_time TEXT,FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE)");
$uc=$db->query('PRAGMA table_info(users)')->fetchAll(); $un=array_column($uc,'name');
if(!in_array('profile_photo',$un,true)) $db->exec('ALTER TABLE users ADD COLUMN profile_photo TEXT DEFAULT NULL');
$columns=$db->query('PRAGMA table_info(lunches)')->fetchAll(); $names=array_column($columns,'name');
if(!in_array('user_id',$names,true)) $db->exec('ALTER TABLE lunches ADD COLUMN user_id INTEGER');
if(!in_array('meal_type',$names,true)) $db->exec("ALTER TABLE lunches ADD COLUMN meal_type TEXT DEFAULT 'Lunch'");
if(!in_array('meal_time',$names,true)) $db->exec("ALTER TABLE lunches ADD COLUMN meal_time TEXT DEFAULT '12:00-13:00'");
if(!in_array('recorded_time',$names,true)) $db->exec('ALTER TABLE lunches ADD COLUMN recorded_time TEXT');
$db->exec('CREATE INDEX IF NOT EXISTS idx_lunches_user_date ON lunches(user_id,lunch_date)'); $db->exec('CREATE INDEX IF NOT EXISTS idx_lunches_user_meal ON lunches(user_id,meal_time,id)');
function csrfToken():string{if(empty($_SESSION['csrf_token']))$_SESSION['csrf_token']=bin2hex(random_bytes(32));return $_SESSION['csrf_token'];}
function verifyCsrf():void{$token=$_POST['csrf_token']??'';if(!$token||!hash_equals($_SESSION['csrf_token']??'',$token)){http_response_code(403);exit('Invalid security token.');}}
function h(?string $value):string{return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');}
