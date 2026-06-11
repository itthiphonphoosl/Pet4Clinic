<?php
include('../config/router.php');
header('Content-type: application/json');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

$checkToken = checkToken(getallheaders()['Authorization']);
if ($checkToken) {
    echo json_encode($checkToken, JSON_UNESCAPED_UNICODE);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $sqlQuery = "SELECT * FROM pet p";
    $params = [];
    if (isset($_GET['p_type']) && in_array($_GET['p_type'], ['cat', 'dog'])) {
        $sqlQuery .= " WHERE p.p_type = :p_type";
        $params = [
            ':p_type' => $_GET['p_type']
        ];
    }
    if (isset($_GET['p_id']) && $_GET['p_id'] != "") {
        if ($params == []) {
            $sqlQuery .= " WHERE p.p_id = :p_id";
            $params = [
                ':p_id' => $_GET['p_id']
            ];
        } else {
            $sqlQuery .= " AND p.p_id = :p_id";
            $params[':p_id'] = $_GET['p_id'];
        }
    }
    $results = selectData($sqlQuery, $params);
    if ((isset($_GET['p_id']) && $_GET['p_id'] != "")) {
        $response = ['status' => 'success', 'message' => 'ดึงข้อมูลสำเร็จ', 'data' => $results[0]];
    } else {
        $response = ['status' => 'success', 'message' => 'ดึงข้อมูลสำเร็จ', 'data' => $results];
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_GET['action'] == 'add') {
        if (
            isset($_POST['p_name']) && ($_POST['p_name'] != "") &&
            isset($_POST['p_type']) && in_array($_POST['p_type'], ['cat', 'dog']) && // support cat, dog
            isset($_POST['p_contact']) && ($_POST['p_contact'] != "") &&
            isset($_POST['p_phone']) && ($_POST['p_phone'] != "") &&
            isset($_POST['p_email']) && ($_POST['p_email'] != "") && filter_var($_POST['p_email'], FILTER_VALIDATE_EMAIL)
        ) {
            $p_name = $_POST['p_name'];
            $p_type = $_POST['p_type'];
            $p_contact = $_POST['p_contact'];
            $p_phone = $_POST['p_phone'];
            $p_email = $_POST['p_email'];

            $sqlQuery = "SELECT * FROM pet p
            WHERE p.p_name = :p_name AND p.p_type = :p_type AND p.p_phone = :p_phone";
            $params = [
                ':p_name' => $p_name,
                ':p_type' => $p_type,
                ':p_phone' => $p_phone
            ];
            $results = selectData($sqlQuery, $params);
            if (count($results) > 0) {
                $response = ['status' => 'error', 'message' => 'ข้อมูลชื่อ, ชนิด, เบอร์โทร ของผู้ป่วยนี้มีอยู่แล้ว'];
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                // เพิ่มข้อมูลผู้ใช้ใหม่
                $params = [
                    'p_name' => $p_name,
                    'p_type' => $p_type,
                    'p_contact' => $p_contact,
                    'p_phone' => $p_phone,
                    'p_email' => $p_email,
                ];
                $lastInsertId = insertData('pet', $params);
                if (is_numeric($lastInsertId)) {
                    $response = ['status' => 'success', 'message' => 'เพิ่มข้อมูลสำเร็จ'];
                    echo json_encode($response, JSON_UNESCAPED_UNICODE);
                } else {
                    $response = ['status' => 'error', 'message' => $lastInsertId];
                    echo json_encode($response, JSON_UNESCAPED_UNICODE);
                }
            }
        } else {
            $response = ['status' => 'error', 'message' => 'parameter not found or empty (p_name, p_type[cat, dog], p_contact, p_email)'];
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    } else if ($_GET['action'] == 'edit') {
        $checkToken = checkToken(getallheaders()['Authorization']);
        if ($checkToken) {
            echo json_encode($checkToken, JSON_UNESCAPED_UNICODE);
            exit();
        }
        // แก้ไขข้อมูลผู้ใช้
        if (
            isset($_POST['p_id']) && ($_POST['p_id'] > 0) &&
            isset($_POST['p_name']) && ($_POST['p_name'] != "") &&
            isset($_POST['p_type']) && in_array($_POST['p_type'], ['cat', 'dog']) && // support cat, dog
            isset($_POST['p_contact']) && ($_POST['p_contact'] != "") &&
            isset($_POST['p_phone']) && ($_POST['p_phone'] != "") &&
            isset($_POST['p_email']) && ($_POST['p_email'] != "") && filter_var($_POST['p_email'], FILTER_VALIDATE_EMAIL)
        ) {
            $p_id = $_POST['p_id'];
            $p_name = $_POST['p_name'];
            $p_type = $_POST['p_type'];
            $p_contact = $_POST['p_contact'];
            $p_phone = $_POST['p_phone'];
            $p_email = $_POST['p_email'];
            $sqlQuery = "SELECT * FROM pet p
            WHERE p.p_name = :p_name AND p.p_type = :p_type AND p.p_phone = :p_phone AND p.p_email = :p_email AND p.p_id <> :p_id";
            $params = [
                ':p_name' => $p_name,
                ':p_type' => $p_type,
                ':p_phone' => $p_phone,
                ':p_email' => $p_email,
                ':p_id' => $p_id
            ];
            $results = selectData($sqlQuery, $params);
            if (count($results) > 0) {
                $response = ['status' => 'error', 'message' => 'ข้อมูลชื่อ, ชนิด, เบอร์โทร ของผู้ป่วยนี้มีซ้ำกับผู้ป่วยอื่น'];
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $params = [
                    'p_name' => $p_name,
                    'p_type' => $p_type,
                    'p_contact' => $p_contact,
                    'p_email' => $p_email
                ];
                $condition = "p_id = " . $p_id;
                $result = updateData('pet', $params, $condition);
                if (!$result) {
                    $response = ['status' => 'success', 'message' => 'แก้ไขข้อมูลสำเร็จ'];
                    echo json_encode($response, JSON_UNESCAPED_UNICODE);
                } else {
                    $response = ['status' => 'error', 'message' => $result];
                    echo json_encode($response, JSON_UNESCAPED_UNICODE);
                }
            }
        } else {
            $response = ['status' => 'error', 'message' => 'parameter not found or empty (p_id, p_name, p_type[cat, dog], p_contact, p_phone, p_email)'];
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    } else {
        $response = ['status' => 'error', 'message' => 'action not support'];
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }
} else {
    $response = ['status' => 'error', 'message' => 'method not support'];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}
