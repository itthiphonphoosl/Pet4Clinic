<?php
include('../config/router.php');
header('Content-type: application/json');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $checkToken = checkToken(getallheaders()['Authorization']);
    if ($checkToken) {
        echo json_encode($checkToken, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $sqlQuery = "SELECT * FROM user u";
    $params = [];
    if (isset($_GET['u_type']) && in_array($_GET['u_type'], ['adm', 'sta', 'vet'])) {
        $sqlQuery .= " WHERE u.u_type = :u_type";
        $params = [
            ':u_type' => $_GET['u_type']
        ];
    }
    if (isset($_GET['u_id']) && $_GET['u_id'] != "") {
        if ($params == []) {
            $sqlQuery .= " WHERE u.u_id = :u_id";
            $params = [
                ':u_id' => $_GET['u_id']
            ];
        } else {
            $sqlQuery .= " AND u.u_id = :u_id";
            $params[':u_id'] = $_GET['u_id'];
        }
    }

    if (isset($_GET['u_active']) && $_GET['u_active'] != "") {
        if ($params == []) {
            $sqlQuery .= " WHERE u.u_active = :u_active";
            $params = [
                ':u_active' => $_GET['u_active']
            ];
        } else {
            $sqlQuery .= " AND u.u_active = :u_active";
            $params[':u_active'] = $_GET['u_active'];
        }
    }
    $results = selectData($sqlQuery, $params);
    if ((isset($_GET['u_id']) && $_GET['u_id'] != "")) {
        $response = ['status' => 'success', 'message' => 'ดึงข้อมูลสำเร็จ', 'data' => $results[0]];
    } else {
        $response = ['status' => 'success', 'message' => 'ดึงข้อมูลสำเร็จ', 'data' => $results];
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_GET['action'] == 'login') {
        if (
            isset($_POST['u_username']) && ($_POST['u_username'] != "") &&
            isset($_POST['u_password']) && ($_POST['u_password'] != "")
        ) {
            $u_username = $_POST['u_username'];
            $u_password = $_POST['u_password'];

            $sqlQuery = "SELECT * FROM user u
            WHERE u.u_username = :u_username AND u_active = 1";
            $params = [
                ':u_username' => $u_username
            ];
            $results = selectData($sqlQuery, $params);
            
            if (count($results) > 0) {
                foreach ($results as $row) {
                    if (password_verify($u_password, $row['u_password'])) {
                        $token = bin2hex(random_bytes(64)); // 128-character token
                        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hour'));

                        $params = [
                            'u_id' => $row["u_id"],
                            'a_token' => $token,
                            'a_expired' => $expiresAt,
                        ];
                        $lastInsertId = insertData('access', $params);
                        if (is_numeric($lastInsertId)) {
                            $menus = [];
                            // 'm_dashboard' หน้า Dashboard (ALL)
                            // 'm_pet' หน้าจัดการผู้ป่วย (ALL)
                            // 'm_staff' หน้าจัดการพนักงาน (ADMIN)
                            // 'm_veterinary' หน้าจัดการสัตวแพทย์ (ADMIN)
                            // 'm_drug' หน้าจัดการยา (ADMIN)
                            // 'm_treat_report' หน้าดูรายงานการรักษา (ADMIN)
                            // 'm_drug_report' หน้าดูรายงานข้อมูลยา (ADMIN)

                            // 'm_treat' หน้าการรักษาคนไข้ (VET)

                            // 'm_payment' หน้าเก็บเงิน (ADMIN, STAFF)

                            if ($row["u_type"] == 'adm') {
                                $menus = ['m_dashboard', 'm_pet', 'm_staff', 'm_veterinary', 'm_drug', 'm_payment', 'm_treat_report', 'm_drug_report'];
                            } else if ($row["u_type"] == 'sta') {
                                $menus = ['m_dashboard', 'm_pet', 'm_payment'];
                            } else if ($row["u_type"] == 'vet') {
                                $menus = ['m_dashboard', 'm_pet', 'm_treat'];
                            }
                            $response = [
                                'status' => 'success',
                                'message' => 'เข้าสู่ระบบสำเร็จ',
                                'token' => $token,
                                'data' => [
                                    'u_id' => $row["u_id"],
                                    'u_name' => $row["u_name"],
                                    'u_phone' => $row["u_phone"],
                                    'u_username' => $row["u_username"],
                                    'u_type' => $row["u_type"],
                                    'menus' => $menus,
                                ]
                            ];
                            http_response_code(200);
                            echo json_encode($response, JSON_UNESCAPED_UNICODE);
                        } else {
                            $response = ['status' => 'error', 'message' => $lastInsertId];
                            echo json_encode($response, JSON_UNESCAPED_UNICODE);
                        }
                    } else {
                        $response = ['status' => 'error', 'message' => 'ยูเซสเนมหรือรหัสผ่านไม่ถูกต้อง'];
                        echo json_encode($response, JSON_UNESCAPED_UNICODE);
                    }
                }
            } else {
                $response = ['status' => 'error', 'message' => 'ยูเซสเนมหรือรหัสผ่านไม่ถูกต้อง'];
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            }
        } else {
            $response = ['status' => 'error', 'message' => 'parameter not found or empty (username, password)'];
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    } else if ($_GET['action'] == 'add') {
        $checkToken = checkToken(getallheaders()['Authorization']);
        if ($checkToken) {
            echo json_encode($checkToken, JSON_UNESCAPED_UNICODE);
            exit();
        }
        if (
            isset($_POST['u_name']) && ($_POST['u_name'] != "") &&
            isset($_POST['u_phone']) && ($_POST['u_phone'] != "") &&
            isset($_POST['u_username']) && ($_POST['u_username'] != "") &&
            isset($_POST['u_password']) && ($_POST['u_password'] != "") &&
            isset($_POST['u_type']) && in_array($_POST['u_type'], ['adm', 'sta', 'vet']) && // support adm, sta, vet
            isset($_POST['u_active']) && in_array($_POST['u_active'], [0, 1])
        ) {
            $u_name = $_POST['u_name'];
            $u_phone = $_POST['u_phone'];
            $u_username = $_POST['u_username'];
            $u_password = password_hash($_POST['u_password'], PASSWORD_BCRYPT); // เข้ารหัสรหัสผ่าน
            $u_type = $_POST['u_type'];
            $u_active = $_POST['u_active'];

            $sqlQuery = "SELECT * FROM user u
            WHERE u.u_username = :u_username";
            $params = [
                ':u_username' => $u_username
            ];
            $results = selectData($sqlQuery, $params);
            if (count($results) > 0) {
                $response = ['status' => 'error', 'message' => 'ยูเซอร์เนมนี้มีอยู่แล้ว'];
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                // เพิ่มข้อมูลผู้ใช้ใหม่
                $params = [
                    'u_name' => $u_name,
                    'u_phone' => $u_phone,
                    'u_username' => $u_username,
                    'u_password' => $u_password,
                    'u_type' => $u_type,
                    'u_active' => $u_active
                ];
                $lastInsertId = insertData('user', $params);
                if (is_numeric($lastInsertId)) {
                    $response = ['status' => 'success', 'message' => 'เพิ่มข้อมูลสำเร็จ'];
                    echo json_encode($response, JSON_UNESCAPED_UNICODE);
                } else {
                    $response = ['status' => 'error', 'message' => $lastInsertId];
                    echo json_encode($response, JSON_UNESCAPED_UNICODE);
                }
            }
        } else {
            $response = ['status' => 'error', 'message' => 'parameter not found or empty (u_name, u_phone, u_username, u_password, u_type[adm, sta, vet], u_active[0, 1])'];
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
            isset($_POST['u_id']) && ($_POST['u_id'] > 0) &&
            isset($_POST['u_name']) && ($_POST['u_name'] != "") &&
            isset($_POST['u_phone']) && ($_POST['u_phone'] != "") &&
            isset($_POST['u_active']) && in_array($_POST['u_active'], [0, 1])
        ) {
            $u_id = $_POST['u_id'];
            $u_name = $_POST['u_name'];
            $u_phone = $_POST['u_phone'];
            $u_active = $_POST['u_active'];

            $params = [
                'u_name' => $u_name,
                'u_phone' => $u_phone,
                'u_active' => $u_active
            ];
            $condition = "u_id = " . $u_id;
            $result = updateData('user', $params, $condition);
            if (!$result) {
                $response = ['status' => 'success', 'message' => 'แก้ไขข้อมูลสำเร็จ'];
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $response = ['status' => 'error', 'message' => $result];
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            }
        } else {
            $response = ['status' => 'error', 'message' => 'parameter not found or empty (u_id, u_name, u_phone, u_active[0, 1])'];
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    } else if ($_GET['action'] == 'forgot') {
        if (
            isset($_POST['u_username']) && ($_POST['u_username'] != "") &&
            isset($_POST['u_phone']) && ($_POST['u_phone'] != "") &&
            isset($_POST['u_password_new']) && ($_POST['u_password_new'] != "")
        ) {
            $u_username = $_POST['u_username'];
            $u_phone = $_POST['u_phone'];
            $u_password_new = password_hash($_POST['u_password_new'], PASSWORD_BCRYPT); // เข้ารหัสรหัสผ่าน

            $sqlQuery = "SELECT * FROM user u
            WHERE u.u_username = :u_username AND u.u_phone = :u_phone";
            $params = [
                ':u_username' => $u_username,
                ':u_phone' => $u_phone
            ];
            $results = selectData($sqlQuery, $params);
            if (count($results) > 0) {
                $params = [
                    'u_password' => $u_password_new,
                ];
                $condition = "u_username = '" . $u_username . "' AND u_phone = '" . $u_phone . "'";
                $result = updateData('user', $params, $condition);
                if (!$result) {
                    $response = ['status' => 'success', 'message' => 'รีเซ็ตรหัสผ่านสมาชิกสำเร็จ'];
                    echo json_encode($response, JSON_UNESCAPED_UNICODE);
                } else {
                    $response = ['status' => 'error', 'message' =>  $result];
                    echo json_encode($response, JSON_UNESCAPED_UNICODE);
                }
            } else {
                $response = ['status' => 'error', 'message' => 'User not found'];
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            }
        } else {
            $response = ['status' => 'error', 'message' => 'parameter not found or empty (u_username, u_phone, u_password_new)'];
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    } else if ($_GET['action'] == 'add_admin') {
        if (
            isset($_POST['u_name']) && ($_POST['u_name'] != "") &&
            isset($_POST['u_phone']) && ($_POST['u_phone'] != "") &&
            isset($_POST['u_username']) && ($_POST['u_username'] != "") &&
            isset($_POST['u_password']) && ($_POST['u_password'] != "") &&
            isset($_POST['u_type']) && in_array($_POST['u_type'], ['adm', 'sta', 'vet']) && // support adm, sta, vet
            isset($_POST['u_active']) && in_array($_POST['u_active'], [0, 1])
        ) {
            $u_name = $_POST['u_name'];
            $u_phone = $_POST['u_phone'];
            $u_username = $_POST['u_username'];
            $u_password = password_hash($_POST['u_password'], PASSWORD_BCRYPT); // เข้ารหัสรหัสผ่าน
            $u_type = $_POST['u_type'];
            $u_active = $_POST['u_active'];

            $sqlQuery = "SELECT * FROM user u
            WHERE u.u_username = :u_username";
            $params = [
                ':u_username' => $u_username
            ];
            $results = selectData($sqlQuery, $params);
            if (count($results) > 0) {
                $response = ['status' => 'error', 'message' => 'ยูเซอร์เนมนี้มีอยู่แล้ว'];
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                // เพิ่มข้อมูลผู้ใช้ใหม่
                $params = [
                    'u_name' => $u_name,
                    'u_phone' => $u_phone,
                    'u_username' => $u_username,
                    'u_password' => $u_password,
                    'u_type' => $u_type,
                    'u_active' => $u_active
                ];
                $lastInsertId = insertData('user', $params);
                if (is_numeric($lastInsertId)) {
                    $response = ['status' => 'success', 'message' => 'เพิ่มข้อมูลสำเร็จ'];
                    echo json_encode($response, JSON_UNESCAPED_UNICODE);
                } else {
                    $response = ['status' => 'error', 'message' => $lastInsertId];
                    echo json_encode($response, JSON_UNESCAPED_UNICODE);
                }
            }
        } else {
            $response = ['status' => 'error', 'message' => 'parameter not found or empty (u_name, u_phone, u_username, u_password, u_type[adm, sta, vet], u_active[0, 1])'];
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