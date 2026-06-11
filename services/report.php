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
    if (isset($_GET['action']) && ($_GET['action'] == "dashboard")) {

        $subQueryCountPet = '';
        if ((isset($_GET['start']) && $_GET['start'] != '') && (isset($_GET['end']) && $_GET['end'] != '')) {
            $subQueryCountPet = 'WHERE p_created BETWEEN \'' . $_GET['start'] . '\' AND \'' . $_GET['end'] . '\'';
        } else if (isset($_GET['start']) && $_GET['start'] != '') {
            $subQueryCountPet = 'WHERE p_created >= \'' . $_GET['start'] . '\'';
        } else if ((isset($_GET['end']) && $_GET['end'] != '')) {
            $subQueryCountPet = 'WHERE p_created <= \'' . $_GET['end'] . '\'';
        }
        $sqlQuery = "SELECT COUNT(*) AS 'count_pet' FROM pet " . $subQueryCountPet;
        $results = selectData($sqlQuery, []);
        $countPet = 0;
        foreach ($results as $row) {
            $countPet = $row['count_pet'];
        }

        $subQueryCountIncome = '';
        if ((isset($_GET['start']) && $_GET['start'] != '') && (isset($_GET['end']) && $_GET['end'] != '')) {
            $subQueryCountIncome = 'AND t_payment BETWEEN \'' . $_GET['start'] . '\' AND \'' . $_GET['end'] . '\'';
        } else if (isset($_GET['start']) && $_GET['start'] != '') {
            $subQueryCountIncome = 'AND t_payment >= \'' . $_GET['start'] . '\'';
        } else if ((isset($_GET['end']) && $_GET['end'] != '')) {
            $subQueryCountIncome = 'AND t_payment <= \'' . $_GET['end'] . '\'';
        }
        $sqlQuery = "SELECT SUM(COALESCE(t_price, 0) + COALESCE(t_price_other, 0)) AS 'count_income' FROM treatment WHERE t_status = 'finish' " . $subQueryCountIncome;
        $results = selectData($sqlQuery, []);
        $countIncome = 0;
        foreach ($results as $row) {
            $countIncome = $row['count_income'];
        }

        $subQueryCountVet = '';
        if ((isset($_GET['start']) && $_GET['start'] != '') && (isset($_GET['end']) && $_GET['end'] != '')) {
            $subQueryCountVet = 'AND u_created BETWEEN \'' . $_GET['start'] . '\' AND \'' . $_GET['end'] . '\'';
        } else if (isset($_GET['start']) && $_GET['start'] != '') {
            $subQueryCountVet = 'AND u_created >= \'' . $_GET['start'] . '\'';
        } else if ((isset($_GET['end']) && $_GET['end'] != '')) {
            $subQueryCountVet = 'AND u_created <= \'' . $_GET['end'] . '\'';
        }
        $sqlQuery = "SELECT COUNT(*) AS 'count_vet' FROM user WHERE u_type = 'vet' AND u_active = 1 " . $subQueryCountVet;
        $results = selectData($sqlQuery, []);
        $countVet = 0;
        foreach ($results as $row) {
            $countVet = $row['count_vet'];
        }

        $subQueryCountDrug = '';
        if ((isset($_GET['start']) && $_GET['start'] != '') && (isset($_GET['end']) && $_GET['end'] != '')) {
            $subQueryCountDrug = 'WHERE td_created BETWEEN \'' . $_GET['start'] . '\' AND \'' . $_GET['end'] . '\'';
        } else if (isset($_GET['start']) && $_GET['start'] != '') {
            $subQueryCountDrug = 'WHERE td_created >= \'' . $_GET['start'] . '\'';
        } else if ((isset($_GET['end']) && $_GET['end'] != '')) {
            $subQueryCountDrug = 'WHERE td_created <= \'' . $_GET['end'] . '\'';
        }
        $sqlQuery = "SELECT COALESCE(SUM(d_amount), 0) AS 'count_drug' FROM treatment_detail " . $subQueryCountDrug;
        $results = selectData($sqlQuery, []);
        $countDrug = 0;
        foreach ($results as $row) {
            $countDrug = $row['count_drug'];
        }

        $subQueryCountDrugPerType = '';
        if ((isset($_GET['start']) && $_GET['start'] != '') && (isset($_GET['end']) && $_GET['end'] != '')) {
            $subQueryCountDrugPerType = 'AND td_created BETWEEN \'' . $_GET['start'] . '\' AND \'' . $_GET['end'] . '\'';
        } else if (isset($_GET['start']) && $_GET['start'] != '') {
            $subQueryCountDrugPerType = 'AND td_created >= \'' . $_GET['start'] . '\'';
        } else if ((isset($_GET['end']) && $_GET['end'] != '')) {
            $subQueryCountDrugPerType = 'AND td_created <= \'' . $_GET['end'] . '\'';
        }
        $sqlQuery = "SELECT 
        (SELECT COALESCE(SUM(td.d_amount), 0) FROM treatment_detail td LEFT JOIN drug d ON td.d_id = d.d_id WHERE d.d_type = 'vac' " . $subQueryCountDrugPerType . ") AS 'count_vac', 
        (SELECT COALESCE(SUM(td.d_amount), 0) FROM treatment_detail td LEFT JOIN drug d ON td.d_id = d.d_id WHERE d.d_type = 'ora' " . $subQueryCountDrugPerType . ") AS 'count_ora', 
        (SELECT COALESCE(SUM(td.d_amount), 0) FROM treatment_detail td LEFT JOIN drug d ON td.d_id = d.d_id WHERE d.d_type = 'top' " . $subQueryCountDrugPerType . ") AS 'count_top';";

        $results = selectData($sqlQuery, []);
        $countVac = 0;
        $countOra = 0;
        $countTop = 0;
        foreach ($results as $row) {
            $countVac = $row['count_vac'];
            $countOra = $row['count_ora'];
            $countTop = $row['count_top'];
        }

        $result = array(
            'head_count_pet' => $countPet,
            'head_count_income' => $countIncome,
            'head_count_vet' => $countVet,
            'head_count_drug' => $countDrug,
            'count_vac' => $countVac,
            'count_ora' => $countOra,
            'count_top' => $countTop
        );

        $response = ['status' => 'success', 'message' => 'ดึงข้อมูลสำเร็จ', 'data' => $result];
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    } else if (isset($_GET['action']) && ($_GET['action'] == "treat")) {

        $subQuery = '';
        if ((isset($_GET['start']) && $_GET['start'] != '') && (isset($_GET['end']) && $_GET['end'] != '')) {
            $subQuery = ' AND t_appointment BETWEEN \'' . $_GET['start'] . '\' AND \'' . $_GET['end'] . '\'';
        } else if (isset($_GET['start']) && $_GET['start'] != '') {
            $subQuery = ' AND t_appointment >= \'' . $_GET['start'] . '\'';
        } else if ((isset($_GET['end']) && $_GET['end'] != '')) {
            $subQuery = ' AND t_appointment <= \'' . $_GET['end'] . '\'';
        }

        if ((isset($_GET['keyword']) && $_GET['keyword'] != '')) {
            $subQuery .= ' AND p.p_name LIKE N\'%' . $_GET['keyword'] . '%\' 
            OR p.p_contact LIKE N\'%' . $_GET['keyword'] . '%\' 
            OR p.p_phone LIKE N\'%' . $_GET['keyword'] . '%\' 
            OR p.p_email LIKE N\'%' . $_GET['keyword'] . '%\' 
            OR u.u_name LIKE N\'%' . $_GET['keyword'] . '%\' 
            OR u.u_phone LIKE N\'%' . $_GET['keyword'] . '%\' 
            OR t.t_detail LIKE N\'%' . $_GET['keyword'] . '%\' 
            OR t.t_price LIKE N\'%' . $_GET['keyword'] . '%\' ';
        }

        $sqlQuery = "SELECT t.*,
        p.p_name, p.p_type, p.p_contact, p.p_phone, p.p_email, 
        u.u_name, u.u_phone, u.u_username, u.u_type 
        FROM treatment t 
        LEFT JOIN pet p ON t.p_id = p.p_id 
        LEFT JOIN user u ON t.u_id = u.u_id WHERE t.t_status IN ('wait', 'finish')" . $subQuery;
        $results = selectData($sqlQuery, []);
        $response = ['status' => 'success', 'message' => 'ดึงข้อมูลสำเร็จ', 'data' => $results];
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    } else if (isset($_GET['action']) && ($_GET['action'] == "paydrug")) {
        $subQuery = '';
        if ((isset($_GET['start']) && $_GET['start'] != '') && (isset($_GET['end']) && $_GET['end'] != '')) {
            $subQuery = ' WHERE td_created BETWEEN \'' . $_GET['start'] . '\' AND \'' . $_GET['end'] . '\'';
        } else if (isset($_GET['start']) && $_GET['start'] != '') {
            $subQuery = ' WHERE td_created >= \'' . $_GET['start'] . '\'';
        } else if ((isset($_GET['end']) && $_GET['end'] != '')) {
            $subQuery = ' WHERE td_created <= \'' . $_GET['end'] . '\'';
        }

        if ((isset($_GET['keyword']) && $_GET['keyword'] != '')) {
            if ($subQuery != '') {
                $subQuery .= ' AND (d.d_name LIKE N\'%' . $_GET['keyword'] . '%\' 
                OR d.d_detail LIKE N\'%' . $_GET['keyword'] . '%\') ';
            } else {
                $subQuery = ' WHERE d.d_name LIKE N\'%' . $_GET['keyword'] . '%\' 
                OR d.d_detail LIKE N\'%' . $_GET['keyword'] . '%\' ';
            }
        }
        $sqlQuery = "SELECT d.d_id, d.d_name, d.d_detail, COALESCE(SUM(td.d_amount * td.d_price), 0) AS 'total_sales'
        FROM drug d
        LEFT JOIN treatment_detail td ON d.d_id = td.d_id " . $subQuery . "
        GROUP BY d.d_id, d.d_name, d.d_detail";
        $results = selectData($sqlQuery, []);
        $response = ['status' => 'success', 'message' => 'ดึงข้อมูลสำเร็จ', 'data' => $results];
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    } else {
        $response = ['status' => 'error', 'message' => 'action not support'];
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }
} else {
    $response = ['status' => 'error', 'message' => 'method not support'];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}
