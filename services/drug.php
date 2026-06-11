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
    $sqlQuery = "SELECT * FROM drug d";
    $params = [];
    if (isset($_GET['d_type']) && in_array($_GET['d_type'], ['vac', 'ora', 'top'])) {
        $sqlQuery .= " WHERE d.d_type = :d_type";
        $params = [
            ':d_type' => $_GET['d_type']
        ];
    }

    if (isset($_GET['d_active']) && in_array($_GET['d_active'], [0, 1])) {
        if (count($params) > 0) {
            $sqlQuery .= " AND d.d_active = :d_active";
        } else {
            $sqlQuery .= " WHERE d.d_active = :d_active";
        }
        $params[':d_type'] = $_GET['d_active'];
    }
    if (isset($_GET['d_id']) && $_GET['d_id'] != "") {
        if ($params == []) {
            $sqlQuery .= " WHERE d.d_id = :d_id";
            $params = [
                ':d_id' => $_GET['d_id']
            ];
        } else {
            $sqlQuery .= " AND d.d_id = :d_id";
            $params[':d_id'] = $_GET['d_id'];
        }
    }


    $results = selectData($sqlQuery, $params);

    $resultInfos = [];

    foreach ($results as $result) {
        $resultInfo = $result;
        if ($result['d_type'] == 'vac') {
            $resultInfo['d_type_desc'] = 'วัคซีน';
        }
        if ($result['d_type'] == 'ora') {
            $resultInfo['d_type_desc'] = 'ยาทาน';
        }
        if ($result['d_type'] == 'top') {
            $resultInfo['d_type_desc'] = 'ยาทา';
        }
        $resultInfos[] = $resultInfo;
    }

    if ((isset($_GET['d_id']) && $_GET['d_id'] != "")) {
        $response = ['status' => 'success', 'message' => 'ดึงข้อมูลสำเร็จ', 'data' => $resultInfos[0]];
    } else {
        $response = ['status' => 'success', 'message' => 'ดึงข้อมูลสำเร็จ', 'data' => $resultInfos];
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_GET['action'] == 'add') {
        if (
            isset($_POST['d_name']) && ($_POST['d_name'] != "") &&
            isset($_POST['d_detail']) && ($_POST['d_detail'] != "") &&
            isset($_POST['d_amount']) && ($_POST['d_amount'] != "") &&
            isset($_POST['d_type']) && in_array($_POST['d_type'], ['vac', 'ora', 'top']) && // support vac, ora, top
            isset($_POST['d_price']) && ($_POST['d_price'] != "") &&
            isset($_POST['d_active']) && in_array($_POST['d_active'], [0, 1])
        ) {
            $d_name = $_POST['d_name'];
            $d_detail = $_POST['d_detail'];
            $d_amount = $_POST['d_amount'];
            $d_type = $_POST['d_type'];
            $d_price = $_POST['d_price'];
            $d_active = $_POST['d_active'];

            $sqlQuery = "SELECT * FROM drug d
            WHERE d.d_name = :d_name AND d.d_type = :d_type";
            $params = [
                ':d_name' => $d_name,
                ':d_type' => $d_type,
            ];
            $results = selectData($sqlQuery, $params);
            if (count($results) > 0) {
                $response = ['status' => 'error', 'message' => 'ข้อมูลชื่อ, ชนิด ของตัวยานี้มีอยู่แล้ว'];
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                // เพิ่มข้อมูลผู้ใช้ใหม่
                $params = [
                    'd_name' => $d_name,
                    'd_detail' => $d_detail,
                    'd_amount' => $d_amount,
                    'd_type' => $d_type,
                    'd_price' => $d_price,
                    'd_active' => $d_active
                ];
                $lastInsertId = insertData('drug', $params);
                if (is_numeric($lastInsertId)) {
                    $response = ['status' => 'success', 'message' => 'เพิ่มข้อมูลสำเร็จ'];
                    echo json_encode($response, JSON_UNESCAPED_UNICODE);
                } else {
                    $response = ['status' => 'error', 'message' => $lastInsertId];
                    echo json_encode($response, JSON_UNESCAPED_UNICODE);
                }
            }
        } else {
            $response = ['status' => 'error', 'message' => 'parameter not found or empty (d_name, d_detail, d_amount, d_type[vac, ora, top], d_price, d_active)'];
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    } else if ($_GET['action'] == 'edit') {
        if (
            isset($_POST['d_id']) && ($_POST['d_id'] > 0) &&
            isset($_POST['d_name']) && ($_POST['d_name'] != "") &&
            isset($_POST['d_detail']) && ($_POST['d_detail'] != "") &&
            isset($_POST['d_amount']) && ($_POST['d_amount'] != "") &&
            isset($_POST['d_type']) && in_array($_POST['d_type'], ['vac', 'ora', 'top']) && // support vac, ora, top
            isset($_POST['d_price']) && ($_POST['d_price'] != "") &&
            isset($_POST['d_active']) && in_array($_POST['d_active'], [0, 1])
        ) {
            $d_id = $_POST['d_id'];
            $d_name = $_POST['d_name'];
            $d_detail = $_POST['d_detail'];
            $d_amount = $_POST['d_amount'];
            $d_type = $_POST['d_type'];
            $d_price = $_POST['d_price'];
            $d_active = $_POST['d_active'];

            $params = [
                'd_name' => $d_name,
                'd_detail' => $d_detail,
                'd_amount' => $d_amount,
                'd_type' => $d_type,
                'd_price' => $d_price,
                'd_active' => $d_active
            ];
            $condition = "d_id = " . $d_id;
            $result = updateData('drug', $params, $condition);

            if (!$result) {
                $response = ['status' => 'success', 'message' => 'แก้ไขข้อมูลสำเร็จ'];
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $response = ['status' => 'error', 'message' => $result];
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            }
        } else {
            $response = ['status' => 'error', 'message' => 'parameter not found or empty (d_id, d_name, d_detail, d_amount, d_type[vac, ora, top], d_price, d_active)'];
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
