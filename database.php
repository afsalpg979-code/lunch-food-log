<?php
declare(strict_types=1);
date_default_timezone_set('Asia/Kolkata');
if(session_status()!==PHP_SESSION_ACTIVE){$secure=!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off';session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);session_start();}
if(!headers_sent()){header('X-Content-Type-Options: nosniff');header('X-Frame-Options: SAMEORIGIN');header('Referrer-Policy: strict-origin-when-cross-origin');header('Permissions-Policy: camera=(), microphone=(), geolocation=()');}
$dataDir=__DIR__.'/data';if(!is_dir($dataDir)&&!mkdir($dataDir,0775,true))throw new RuntimeException('Unable to create data directory.');
$db=new PDO('sqlite:'.$dataDir.'/lunch.sqlite',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
$db->exec('PRAGMA foreign_keys = ON');$db->exec('PRAGMA busy_timeout = 5000');$db->exec('PRAGMA journal_mode = WAL');$db->exec('PRAGMA synchronous = NORMAL');$db->exec('PRAGMA temp_store = MEMORY');$db->exec('PRAGMA cache_size = -8192');
$db->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT NOT NULL,email TEXT NOT NULL UNIQUE,password_hash TEXT NOT NULL,created_at TEXT NOT NULL,updated_at TEXT NOT NULL)");
$db->exec("CREATE TABLE IF NOT EXISTS lunches (id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER,lunch_date TEXT NOT NULL,food_item TEXT NOT NULL,quantity TEXT DEFAULT '',notes TEXT DEFAULT '',meal_type TEXT DEFAULT 'Lunch',meal_time TEXT DEFAULT '12:00-13:00',recorded_time TEXT,FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE)");
$db->exec("CREATE TABLE IF NOT EXISTS weights (id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER NOT NULL,weight_kg REAL NOT NULL,weight_date TEXT NOT NULL,notes TEXT DEFAULT '',created_at TEXT NOT NULL,FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE)");
$uc=$db->query('PRAGMA table_info(users)')->fetchAll();$un=array_column($uc,'name');if(!in_array('profile_photo',$un,true))$db->exec('ALTER TABLE users ADD COLUMN profile_photo TEXT DEFAULT NULL');
$columns=$db->query('PRAGMA table_info(lunches)')->fetchAll();$names=array_column($columns,'name');
$migrations=['user_id'=>'ALTER TABLE lunches ADD COLUMN user_id INTEGER','meal_type'=>"ALTER TABLE lunches ADD COLUMN meal_type TEXT DEFAULT 'Lunch'",'meal_time'=>"ALTER TABLE lunches ADD COLUMN meal_time TEXT DEFAULT '12:00-13:00'",'recorded_time'=>'ALTER TABLE lunches ADD COLUMN recorded_time TEXT','calories'=>'ALTER TABLE lunches ADD COLUMN calories REAL DEFAULT 0','protein'=>'ALTER TABLE lunches ADD COLUMN protein REAL DEFAULT 0','carbs'=>'ALTER TABLE lunches ADD COLUMN carbs REAL DEFAULT 0','fat'=>'ALTER TABLE lunches ADD COLUMN fat REAL DEFAULT 0'];
foreach($migrations as $column=>$sql)if(!in_array($column,$names,true))$db->exec($sql);
$db->exec('CREATE INDEX IF NOT EXISTS idx_lunches_user_date ON lunches(user_id,lunch_date)');$db->exec('CREATE INDEX IF NOT EXISTS idx_lunches_user_date_meal ON lunches(user_id,lunch_date,meal_time,id)');$db->exec('CREATE INDEX IF NOT EXISTS idx_weights_user_date ON weights(user_id,weight_date,id)');
function csrfToken():string{if(empty($_SESSION['csrf_token']))$_SESSION['csrf_token']=bin2hex(random_bytes(32));return $_SESSION['csrf_token'];}
function verifyCsrf():void{$token=$_POST['csrf_token']??'';if(!$token||!hash_equals($_SESSION['csrf_token']??'',$token)){http_response_code(403);exit('Invalid security token.');}}
function h(?string $value):string{return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');}
function nutritionNumber($value,float $min=0,float $max=100000):float{$n=is_numeric($value)?(float)$value:0;return max($min,min($max,$n));}
