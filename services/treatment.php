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
    if (isset($_GET['action']) && ($_GET['action'] == "fordoc")) {

        $a_token = getallheaders()['Authorization'];
        $sqlQuery = "SELECT u_id FROM access a  
         WHERE a.a_token = :a_token AND a.a_expired > CURDATE()";
        $params = [
            ':a_token' => $a_token
        ];
        $results = selectData($sqlQuery, $params);
        $response = [];
        if (count($results) > 0) {
            $sqlQuery = "SELECT t.*,
        p.p_name, p.p_type, p.p_contact, p.p_phone, p.p_email, 
        u.u_name, u.u_phone, u.u_username, u.u_type 
        FROM treatment t 
        LEFT JOIN pet p ON t.p_id = p.p_id 
        LEFT JOIN user u ON t.u_id = u.u_id WHERE u.u_id = :u_id AND t.t_status LIKE '%t%' AND DATE(t.t_appointment) = CURDATE()";
            $params = [
                ':u_id' => $results[0]['u_id']
            ];
            $results = selectData($sqlQuery, $params);
            $response = ['status' => 'success', 'message' => 'ดึงข้อมูลสำเร็จ', 'data' => $results];
        } else {
            $response = ['status' => 'denied', 'message' => 'token expired.'];
        }
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    } else if (isset($_GET['t_id']) && ($_GET['t_id'] != "")) {
        $sqlQuery = "SELECT t.*,
        p.p_name, p.p_type, p.p_contact, p.p_phone, p.p_email,
        u.u_name, u.u_phone, u.u_username, u.u_type 
        FROM treatment t 
        LEFT JOIN pet p ON t.p_id = p.p_id 
        LEFT JOIN user u ON t.u_id = u.u_id";
        $params = [
            ':t_id' => $_GET['t_id']
        ];
        $sqlQuery .= " WHERE t.t_id = :t_id";

        $results = selectData($sqlQuery, $params);
        if (count($results) > 0) {
            $result = $results[0];

            $sqlQuery = "SELECT td.*,
            d.d_name, d.d_detail
            FROM treatment_detail td 
            LEFT JOIN drug d ON td.d_id = d.d_id";
            $params = [
                ':t_id' => $_GET['t_id']
            ];
            $sqlQuery .= " WHERE td.t_id = :t_id";

            $results = selectData($sqlQuery, $params);

            $result['details'] = $results;

            $response = ['status' => 'success', 'message' => 'ดึงข้อมูลสำเร็จ', 'data' => $result];
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        } else {
            $response = ['status' => 'error', 'message' => 'ไม่พบข้อมูล'];
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    } else {
        $sqlQuery = "SELECT t.*,
        p.p_name, p.p_type, p.p_contact, p.p_phone, p.p_email, 
        u.u_name, u.u_phone, u.u_username, u.u_type 
        FROM treatment t 
        LEFT JOIN pet p ON t.p_id = p.p_id 
        LEFT JOIN user u ON t.u_id = u.u_id";
        $params = [];
        if (isset($_GET['p_type']) && in_array($_GET['p_type'], ['cat', 'dog'])) {
            $sqlQuery .= " WHERE p.p_type = :p_type";
            $params = [
                ':p_type' => $_GET['p_type']
            ];
        }

        if (isset($_GET['p_id']) && ($_GET['p_id'] != "")) {
            if (count($params) > 0) {
                $sqlQuery .= " AND p.p_id = :p_id";
                $params[':p_id'] = $_GET['p_id'];
            } else {
                $sqlQuery .= " WHERE p.p_id = :p_id";
                $params = [
                    ':p_id' => $_GET['p_id']
                ];
            }
        }

        if (isset($_GET['u_id']) && $_GET['u_id'] != '') {
            if (count($params) > 0) {
                $sqlQuery .= " AND t.u_id = :u_id";
            } else {
                $sqlQuery .= " WHERE t.u_id = :u_id";
            }
            $params[':u_id'] = $_GET['u_id'];
        }

        if (isset($_GET['t_status']) && $_GET['t_status'] != '') {
            if (count($params) > 0) {
                $sqlQuery .= (" AND t.t_status LIKE '%" . $_GET['t_status'] . "%'");
            } else {
                $sqlQuery .= (" WHERE t.t_status LIKE '%" . $_GET['t_status'] . "%'");
            }
        }
        $results = selectData($sqlQuery, $params);
        $response = ['status' => 'success', 'message' => 'ดึงข้อมูลสำเร็จ', 'data' => $results];
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }
} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_GET['action'] == 'add') {
        if (
            isset($_POST['p_id']) && ($_POST['p_id'] != "") &&
            isset($_POST['u_id']) && ($_POST['u_id'] != "") &&
            isset($_POST['t_appointment']) && ($_POST['t_appointment'] != "")
        ) {
            $p_id = $_POST['p_id'];
            $u_id = $_POST['u_id'];
            $t_appointment = $_POST['t_appointment'];

            $params = [
                'p_id' => $p_id,
                'u_id' => $u_id,
                't_detail' => '',
                't_price' => 0,
                't_price_other' => 0,
                't_status' => 'meet',
                't_appointment' => $t_appointment,
            ];
            $lastInsertId = insertData('treatment', $params);
            if (is_numeric($lastInsertId)) {
                $response = ['status' => 'success', 'message' => 'เพิ่มข้อมูลสำเร็จ'];
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $response = ['status' => 'error', 'message' => $lastInsertId];
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            }
        } else {
            $response = ['status' => 'error', 'message' => 'parameter not found or empty (p_id, u_id, t_appointment)'];
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    } else if ($_GET['action'] == 'treat') {
        if (
            isset($_POST['t_id']) && ($_POST['t_id'] != "") &&
            isset($_POST['t_detail']) && ($_POST['t_detail'] != "") &&
            isset($_POST['t_price_other']) && ($_POST['t_price_other'] != "")
        ) {
            $t_id = $_POST['t_id'];
            $t_detail = $_POST['t_detail'];
            $t_price_other = $_POST['t_price_other'];

            $treatmentInsertDetails = array();
            $drug_errormsg = null;
            $drug_errormsg_footer = '';
            if (isset($_POST['treatment_detail'])) {
                $treatment_detail = $_POST['treatment_detail'];
                $treatmentDetails = json_decode($treatment_detail);
                if ($treatmentDetails) {
                    foreach ($treatmentDetails as $treatmentDetail) {
                        if (
                            ($treatmentDetail->d_id) &&
                            ($treatmentDetail->d_amount)
                        ) {
                            $d_id = $treatmentDetail->d_id;
                            $d_amount = $treatmentDetail->d_amount;
                            $sqlQuery = "SELECT * FROM drug d
                            WHERE d.d_id = :d_id AND d.d_active = 1";
                            $params = [
                                ':d_id' => $d_id,
                            ];
                            $results = selectData($sqlQuery, $params);
                            if (count($results) > 0) {
                                foreach ($results as $row) {
                                    if ($row['d_amount'] < $d_amount) {
                                        $drug_errormsg .= '- ' . $row['d_name'] . '(คงเหลือ ' . $row['d_amount'] . ')\n';
                                    } else {
                                        $treatmentInsertDetails[] = [
                                            'd_id' => $row['d_id'],
                                            'd_amount_withdraw' => $d_amount,
                                            'd_price' => $row['d_price'],
                                            'd_amount_update' => ($row['d_amount'] - $d_amount),
                                        ];
                                    }
                                }
                            } else {
                                if (!$drug_errormsg) {
                                    $drug_errormsg = ' ';
                                }
                                $drug_errormsg_footer .= '* ไม่พบรายการยาบางตัว';
                            }
                        }
                    }
                }
            }

            if ($drug_errormsg) {
                $drug_errormsg = 'รายการยาไม่พอจ่าย\n' . $drug_errormsg . $drug_errormsg_footer;
                $response = ['status' => 'error', 'message' => $drug_errormsg];
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {

                $condition = "t_id = " . $t_id;
                $result = deleteData('treatment_detail', $condition);
                $t_price = 0.00;
                if (count($treatmentInsertDetails)) {
                    foreach ($treatmentInsertDetails as $treatmentInsertDetail) {
                        $treatmentDetailParams = [
                            't_id' => $t_id,
                            'd_id' => $treatmentInsertDetail['d_id'],
                            'd_amount' => $treatmentInsertDetail['d_amount_withdraw'],
                            'd_price' => $treatmentInsertDetail['d_price']
                        ];
                        $t_price += ($treatmentInsertDetail['d_amount_withdraw'] * $treatmentInsertDetail['d_price']);
                        $lastInsertId = insertData('treatment_detail', $treatmentDetailParams);

                        $drugParams = [
                            'd_amount' => $treatmentInsertDetail['d_amount_update'],
                        ];
                        $drugCondition = "d_id = " . $treatmentInsertDetail['d_id'];
                        updateData('drug', $drugParams, $drugCondition);
                    }
                }
                $params = [
                    't_appointment' => date('Y-m-d H:i:s'),
                    't_detail' => $t_detail,
                    't_price' => $t_price,
                    't_price_other' => $t_price_other,
                    't_status' => 'wait',
                ];
                $condition = "t_id = " . $t_id;
                $result = updateData('treatment', $params, $condition);
                if (!$result) {
                    $response = ['status' => 'success', 'message' => 'บักทึกข้อมูลการรักษาสำเร็จ'];
                    echo json_encode($response, JSON_UNESCAPED_UNICODE);
                } else {
                    $response = ['status' => 'error', 'message' => $result];
                    echo json_encode($response, JSON_UNESCAPED_UNICODE);
                }
            }
        } else {
            $response = ['status' => 'error', 'message' => 'parameter not found or empty (t_id, t_detail, t_price_other)'];
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    } else if ($_GET['action'] == 'pay') {
        if (
            isset($_POST['t_id']) && ($_POST['t_id'] != "")
        ) {
            $t_id = $_POST['t_id'];

            $params = [
                't_status' => 'finish',
                't_payment' => date('Y-m-d H:i:s')
            ];
            $condition = "t_id = " . $t_id;
            $result = updateData('treatment', $params, $condition);
            if (!$result) {
                $response = ['status' => 'success', 'message' => 'บักทึกข้อมูลการชำระเงินสำเร็จ'];
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $response = ['status' => 'error', 'message' => $result];
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            }
        } else {
            $response = ['status' => 'error', 'message' => 'parameter not found or empty (t_id)'];
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    } else if ($_GET['action'] == 'cancel') {
        if (
            isset($_POST['t_id']) && ($_POST['t_id'] != "")
        ) {
            $t_id = $_POST['t_id'];

            $params = [
                't_status' => 'cancel'
            ];
            $condition = "t_id = " . $t_id;
            $result = updateData('treatment', $params, $condition);
            if (!$result) {
                $response = ['status' => 'success', 'message' => 'ยกเลิกนัดหมายสำเร็จ'];
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $response = ['status' => 'error', 'message' => $result];
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            }
        } else {
            $response = ['status' => 'error', 'message' => 'parameter not found or empty (t_id)'];
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    } else if ($_GET['action'] == 'edit') {
        if (
            isset($_POST['t_id']) && ($_POST['t_id'] != "") &&
            isset($_POST['u_id']) && ($_POST['u_id'] != "") &&
            isset($_POST['t_appointment']) && ($_POST['t_appointment'] != "")
        ) {
            $t_id = $_POST['t_id'];
            $u_id = $_POST['u_id'];
            $t_appointment = $_POST['t_appointment'];

            $sqlQuery = "SELECT * FROM treatment t
            WHERE t.t_id = :t_id AND t.t_status = 'finish'";
            $params = [
                ':t_id' => $t_id,
            ];
            $results = selectData($sqlQuery, $params);
            if (count($results) > 0) {
                $response = ['status' => 'error', 'message' => 'ไม่สามาารถแก้ไขได้ เนื่องจากข้อมูลนี้สิ้นสุดแล้ว'];
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $params = [
                    'u_id' => $u_id,
                    't_appointment' => $t_appointment,
                ];
                $condition = "t_id = " . $t_id;
                $result = updateData('treatment', $params, $condition);
                if (!$result) {
                    $response = ['status' => 'success', 'message' => 'แก้ไขข้อมูลสำเร็จ'];
                    echo json_encode($response, JSON_UNESCAPED_UNICODE);
                } else {
                    $response = ['status' => 'error', 'message' => $result];
                    echo json_encode($response, JSON_UNESCAPED_UNICODE);
                }
            }
        } else {
            $response = ['status' => 'error', 'message' => 'parameter not found or empty (t_id, p_id, u_id, t_appointment)'];
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
