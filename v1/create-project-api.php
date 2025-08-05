<?php

ini_set("display_errors", 1);

require '../vendor/autoload.php';
use \Firebase\JWT\JWT;

// اضافه کردن هدرها
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-type: application/json; charset=utf-8");

// فراخوانی فایل‌ها
include_once("../config/database.php");
include_once("../classes/Users.php");

// ایجاد آبجکت‌ها
$db = new Database();
$connection = $db->connect();
$user_obj = new Users($connection);

if ($_SERVER['REQUEST_METHOD'] === "POST") {

   // دریافت بدنه درخواست
   $data = json_decode(file_get_contents("php://input"));
   $headers = getallheaders();

   if (!empty($data->name) && !empty($data->description) && !empty($data->status)) {

     try {

       // استخراج توکن از هدر Authorization
       $jwt = $headers["Authorization"];
       $secret_key = "owt125";

       // دیکد کردن JWT
       $decoded_data = JWT::decode($jwt, $secret_key, array('HS512'));

       // مقداردهی به ویژگی‌های پروژه
       $user_obj->user_id       = $decoded_data->data->id;
       $user_obj->project_name  = $data->name;
       $user_obj->description   = $data->description;
       $user_obj->status        = $data->status;

       // ایجاد پروژه
       if ($user_obj->create_project()) {
         http_response_code(200); // موفق
         echo json_encode(array(
           "status"  => 1,
           "message" => "پروژه با موفقیت ایجاد شد"
         ));
       } else {
         http_response_code(500); // خطای سرور
         echo json_encode(array(
           "status"  => 0,
           "message" => "ایجاد پروژه موفقیت‌آمیز نبود"
         ));
       }

     } catch (Exception $ex) {
       http_response_code(500); // خطای سرور
       echo json_encode(array(
         "status"  => 0,
         "message" => $ex->getMessage()
       ));
     }

   } else {
     http_response_code(404); // داده‌های کافی نیست
     echo json_encode(array(
       "status"  => 0,
       "message" => "تمام فیلدها الزامی است"
     ));
   }
}

?>
