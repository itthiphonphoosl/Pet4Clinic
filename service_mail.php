<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

try {

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://softdemohub.online/Pet4Clinic/services/petcontact.php',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Basic cGV0NGNsaW5pYzpQZXQ0QDIwMjQ='
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    $data = json_decode($response, true);

    if ($data['status'] == 'success') {
        foreach ($data['data'] as $pet) {

            $mail = new PHPMailer(true);

            $p_name = $pet['p_name'];
            $p_contact = $pet['p_contact'];
            $p_email = $pet['p_email'];
            $u_name = $pet['u_name'];



            // ตั้งค่า SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'pet4clinic.tester@gmail.com'; // ใส่อีเมล Gmail
            $mail->Password = 'uzrc aaut aufx mtco'; // ใส่รหัสผ่านหรือ App Password ของ Gmail
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // ข้อมูลผู้ส่ง
            $mail->setFrom('pet4clinic.tester@gmail.com', mb_encode_mimeheader('เจ้าหน้าที่ฝ่ายประชาสัมพันธ์ Pet4Clinic'));

            // ข้อมูลผู้รับ
            $mail->addAddress($p_email);

            // เนื้อหาของอีเมล
            $mail->isHTML(true);  // กำหนดให้เนื้อหาเป็น HTML
            $mail->Subject =  mb_encode_mimeheader('การนัดหมายพาสัตว์เลี้ยงเข้าพบแพทย์ในวันนี้', 'UTF-8');
            $mail->Body = '
    <p>เรียน คุณ ' . $p_contact . ',</p>
    
    <p>
        ทางคลินิก <strong>Pet4Clinic</strong> ขอแจ้งเตือนการนัดหมายพาสัตว์เลี้ยงของคุณ <strong>' . $p_name . '</strong> เข้าพบสัตวแพทย์ ' . $u_name . ' ในวันนี้ ตามที่ได้ทำการนัดหมายไว้
    </p>
    
    <p>
        หากมีการเปลี่ยนแปลงหรือข้อสงสัยเพิ่มเติม สามารถติดต่อกลับทาง <strong>' . 'Pet4Clinic' . '</strong> เพื่อแก้ไขและปรับเปลี่ยนการนัดหมายได้
    </p>
    
    <p>
        ขอขอบพระคุณที่ไว้วางใจให้ทางคลินิกดูแลสุขภาพของสัตว์เลี้ยงของท่าน
    </p>
    
    <p>
        ขอแสดงความนับถือ,<br>
        ' . 'เจ้าหน้าที่ฝ่ายประชาสัมพันธ์' . '<br>
    </p>
';


            // ส่งอีเมล
            $mail->send();
        }
    }
    // echo 'Email has been sent successfully.';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
