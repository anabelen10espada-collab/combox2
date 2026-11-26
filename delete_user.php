<?php
ob_start();
ini_set('default_socket_timeout', 3);
set_time_limit(5);

require("./config/database.php");
ob_end_clean();

$id = $_GET['id'] ?? '';
$action = $_GET['action'] ?? '';

if (empty($id) || !is_numeric($id)) {
    header("Location: list_users.php");
    exit;
}

$id = (int)$id;

if ($action === 'delete') {
    $sql = 'DELETE FROM public.users WHERE id = $1';
    $params = [$id];
} elseif ($action === 'deactivate') {
    $sql = 'UPDATE public.users SET status = false, deleted_at = NOW() WHERE id = $1';
    $params = [$id];
} else {
    header("Location: list_users.php");
    exit;
}

if (isset($local_conn) && $local_conn) {
    $res_local = pg_query_params($local_conn, $sql, $params);
    if (!$res_local) {
        die("Error en base de datos local: " . pg_last_error($local_conn));
    }
}

if (isset($supa_conn) && $supa_conn) {
    $res_supa = pg_query_params($supa_conn, $sql, $params);
    if (!$res_supa) {
        die("Error en Supabase: " . pg_last_error($supa_conn));
    }
}

header("Location: list_users.php");
exit;

/*
//get database connection
require("../config/database.php");

//gET DATA 
$user_id = $GET['userID'];
echo $user_id;
//pPrepare query 
$sql = "DELETE FROM users WHERE id = $user_id";
//Execute query 
$res = pg_query($local_conn, $sql);
if($res) {
    echo "<script>alert('User has been created successfylly:::')</script>";
    header('refresh:0;url=list_users.php');
} else {
    //echo "<script>alert('User has not been delete:::')</script>";
    //header('refresh:0;url=list_users.php');
    
}*/
?>