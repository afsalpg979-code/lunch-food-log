<?php
require __DIR__ . '/auth.php'; requireLogin();
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);header('Allow: POST');exit('Method not allowed.');}
verifyCsrf();
$id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);$date=trim($_POST['lunch_date']??'');$food=trim($_POST['food_item']??'');$quantity=trim($_POST['quantity']??'');$notes=trim($_POST['notes']??'');$mealType=trim($_POST['meal_type']??'Lunch');
$meals=['Breakfast'=>'08:00-09:00','Morning Snack'=>'10:30-11:00','Lunch'=>'12:00-13:00','Evening Snack'=>'16:30-17:00','During Duty'=>'20:00-21:00','Dinner'=>'22:15-22:45'];
$d=DateTime::createFromFormat('Y-m-d',$date);
if(!$id||!$d||$d->format('Y-m-d')!==$date||$food===''){http_response_code(400);exit('Please enter a valid entry, date and food item.');}
if(mb_strlen($food)>150||mb_strlen($quantity)>80||mb_strlen($notes)>250){http_response_code(400);exit('One or more fields are too long.');}
if(!isset($meals[$mealType]))$mealType='Lunch';
$stmt=$db->prepare('UPDATE lunches SET lunch_date=?,food_item=?,quantity=?,notes=?,meal_type=?,meal_time=? WHERE id=? AND user_id=?');
$stmt->execute([$date,$food,$quantity,$notes,$mealType,$meals[$mealType],$id,$_SESSION['user_id']]);
header('Location: index.php?updated=1');exit;
