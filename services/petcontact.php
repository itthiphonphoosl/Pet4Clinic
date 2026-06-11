<?php
include('../config/router.php');
header('Content-type: application/json');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $basicAuth = getallheaders()['Authorization'];
    if ($basicAuth == 'Basic cGV0NGNsaW5pYzpQZXQ0QDIwMjQ=') {
        $sqlQuery = "SELECT t.t_id,
        p.p_name, p.p_contact, p.p_email, u.u_name 
        FROM treatment t 
        LEFT JOIN pet p ON t.p_id = p.p_id 
        LEFT JOIN user u ON t.u_id = u.u_id WHERE p.p_email IS NOT NULL AND t.t_status LIKE 'meet' AND DATE(t.t_appointment) = CURDATE()";
        $results = selectData($sqlQuery, []);
        $response = ['status' => 'success', 'message' => 'ดึงข้อมูลสำเร็จ', 'data' => $results];
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    } else {
        $response = ['status' => 'denied', 'message' => 'Error authorization.'];
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }
}
