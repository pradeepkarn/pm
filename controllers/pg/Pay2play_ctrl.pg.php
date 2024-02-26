<?php

use Paynow\Payments\Paynow;
use PHPMailer\PHPMailer\PHPMailer;

class Pay2play_ctrl
{
    public $paynow;
    public function __construct()
    {
        $this->paynow = new Paynow(
            INTEGRATION_ID,
            INTEGRATION_KEY,
            BASEURI,
            // The return url can be set at later stages. You might want to do this if you want to pass data to the return url (like the reference of the transaction)
            BASEURI
        );
    }
    function check_status()
    {
        if (isset($_POST['paymentid']) && is_numeric($_POST['paymentid'])) {
            $db = new Dbobjects;
            $db->tableName = 'payment';
            $pmt = $db->pk(intval($_POST['paymentid']));
            if (!$pmt) {
                $data['msg'] = "Payment not found";
                $data['success'] = false;
                $data['data'] = null;
                echo json_encode($data);
                exit;
            }
            $pmt = obj($pmt);
            if ($pmt->user_id != USER['id']) {
                $data['msg'] = "You are not authorized to check this payment status";
                $data['success'] = false;
                $data['data'] = null;
                echo json_encode($data);
                exit;
            }
            $pollUrl = $pmt->pollurl;
            $paymentid = $pmt->id;
        } else {
            $data['msg'] = "Invalid payment id";
            $data['success'] = false;
            $data['data'] = null;
            echo json_encode($data);
            exit;
        }
        if ($pollUrl == '') {
            $data['msg'] = "Payment not done";
            $data['success'] = false;
            $data['data'] = null;
            echo json_encode($data);
            exit;
        }
        $sms = new SMS_ctrl;
        $mobile = "$pmt->isd_code" . "$pmt->mobile";
        $db->tableName = 'payment';
        $pmtarr = $db->pk($paymentid);
        $co = (object)$db->showOne("select id,link,customer_email from customer_order where payment_id = '$pmt->id'");
        $emailbody = "Payment confirmed!! TR No. {$pmt->unique_id}.  you have only one chance to use this coupon link, so don't share with anyone: {$co->link} Good luck!";
        if ($pmtarr['status'] == 'paid') {
            $pmtdata = json_decode($pmtarr['paynowjson'] ?? []);
            $stsd = $pmtdata->status ?? null;
            if ($stsd) {
                $data['msg'] = "Payment status";
                $data['success'] = $stsd->status == 'paid' ? true : false;
                $data['data'] = $stsd;
                $this->send_email($receiver = $co->customer_email, $subject = "Secret Link", $body = $emailbody);
                // $sms->send(strval($pmt->id),strval($pmt->unique_id),$co->link??null,["$mobile"]);
                echo json_encode($data);
                $parr = null;
                exit;
            } else {
                $data['msg'] = "Something went wrong";
                $data['success'] = false;
                $data['data'] = null;
                echo json_encode($data);
                $parr = null;
                exit;
            }
        }
        if ($pmtarr['status'] == 'cancelled') {
            $pmtdata = json_decode($pmtarr['paynowjson'] ?? []);
            $stsd = $pmtdata->status ?? null;
            if ($stsd) {
                $data['msg'] = "Payment status";
                $data['success'] = $stsd->status == 'paid' ? true : false;
                $data['data'] = $stsd;
                echo json_encode($data);
                $parr = null;
                exit;
            } else {
                $data['msg'] = "Something went wrong";
                $data['success'] = false;
                $data['data'] = null;
                echo json_encode($data);
                $parr = null;
                exit;
            }
        }
        $status = $this->paynow->pollTransaction($pollUrl);
        if ($status->paid()) {
            $parr = null;
            $parr['reference'] = $status->reference();
            $parr['paynowReference'] = $status->paynowReference();
            $parr['amount'] = $status->amount();
            $parr['status'] = $status->status();
            $pd = array('status' => $parr);
            $json = json_encode($pd);
            $db->insertData['paynowjson'] = $json;
            $db->insertData['status'] = 'paid';
            $db->update();
            $data['msg'] = "Status found";
            $data['success'] = true;
            $data['data'] = $parr;
            $this->send_email($receiver = $co->customer_email, $subject = "Secret Link", $body = $emailbody);
            $tryifsuccess = $sms->clicksms_send(strval($pmt->id), strval($pmt->unique_id), $co->link ?? null, $mobile);
            if ($tryifsuccess==true) {
                $db->insertData['sms_sent'] = $pmt->sms_sent??0+1;
            }
            echo json_encode($data);
            $parr = null;
            exit;
        } else if ($status->status()) {
            $parr = null;
            $parr['reference'] = $status->reference() ?? 'NA';
            $parr['paynowReference'] = $status->paynowReference() ?? 'NA';
            $parr['amount'] = $status->amount() ?? 0;
            $parr['status'] = $status->status() ?? 'NA';
            $pd = array('status' => $parr);
            $json = json_encode($pd);
            $db->insertData['paynowjson'] = $json;
            $db->insertData['status'] = $status->status() ?? 'NA';
            $send=false;
            if ($pmt->sms_sent=='0' || $pmt->sms_sent==null) {
                switch (strtolower($db->insertData['status'])) {
                    case 'paid':
                        $send=true;
                        break;
                    case 'awaiting delivery':
                        $send=true;
                        break;
                    case 'delivered':
                        $send=true;
                        break;
                    default:
                        $send=false;
                        break;
                }
            }
            if ($send===true) {
                $try = $sms->clicksms_send(strval($pmt->id), strval($pmt->unique_id), $co->link ?? null, $mobile);
                if ($try==true) {
                    $db->insertData['sms_sent'] = $pmt->sms_sent??0+1;
                }
            }
            $db->update();
            $data['msg'] = "Your game link will be sent to your email shortly";
            $data['success'] = true;
            $data['data'] = $parr;
            echo json_encode($data);
            $parr = null;
            exit;
        }
    }
    // admin status check
    function check_status_admin()
    {
        if (isset($_POST['paymentid']) && is_numeric($_POST['paymentid'])) {
            $db = new Dbobjects;
            $db->tableName = 'payment';
            $pmt = $db->pk(intval($_POST['paymentid']));
            if (!$pmt) {
                $data['msg'] = "Payment not found";
                $data['success'] = false;
                $data['data'] = null;
                echo json_encode($data);
                exit;
            }
            $pmt = obj($pmt);
            if (!is_superuser()) {
                $data['msg'] = "You are not authorized to check this payment status";
                $data['success'] = false;
                $data['data'] = null;
                echo json_encode($data);
                exit;
            }
            $pollUrl = $pmt->pollurl;
            $paymentid = $pmt->id;
        } else {
            $data['msg'] = "Invalid payment id";
            $data['success'] = false;
            $data['data'] = null;
            echo json_encode($data);
            exit;
        }
        if ($pollUrl == '') {
            $data['msg'] = "Payment not done";
            $data['success'] = false;
            $data['data'] = null;
            echo json_encode($data);
            exit;
        }
        $sms = new SMS_ctrl;
        $mobile = "$pmt->isd_code" . "$pmt->mobile";
        $db->tableName = 'payment';
        $pmtarr = $db->pk($paymentid);
        $co = (object)$db->showOne("select id,link,customer_email from customer_order where payment_id = '$pmt->id'");
        $emailbody = "Payment confirmed!! TR No. {$pmt->unique_id}.  you have only one chance to use this coupon link, so don't share with anyone: {$co->link} Good luck!";
        $send_forcely_if_already_paid = false;
        if (isset($_POST['send_forcely_if_already_paid'])) {
            $send_forcely_if_already_paid= true;
        }
        if ($pmtarr['status'] == 'paid') {
            $pmtdata = json_decode($pmtarr['paynowjson'] ?? []);
            $stsd = $pmtdata->status ?? null;
            if ($stsd) {
                $data['msg'] = "Payment status";
                $data['success'] = $stsd->status == 'paid' ? true : false;
                $data['data'] = $stsd;
                if ($send_forcely_if_already_paid==true) {
                    $try = $sms->clicksms_send(strval($pmt->id), strval($pmt->unique_id), $co->link ?? null, $mobile);
                    if ($try==true) {
                        $db->insertData['sms_sent'] = $pmt->sms_sent??0+1;
                    }
                }
                echo json_encode($data);
                $parr = null;
                exit;
            } else {
                $data['msg'] = "Something went wrong";
                $data['success'] = false;
                $data['data'] = null;
                echo json_encode($data);
                $parr = null;
                exit;
            }
        }
        if ($pmtarr['status'] == 'cancelled') {
            $pmtdata = json_decode($pmtarr['paynowjson'] ?? []);
            $stsd = $pmtdata->status ?? null;
            if ($stsd) {
                $data['msg'] = "Payment status";
                $data['success'] = $stsd->status == 'paid' ? true : false;
                $data['data'] = $stsd;
                echo json_encode($data);
                $parr = null;
                exit;
            } else {
                $data['msg'] = "Something went wrong";
                $data['success'] = false;
                $data['data'] = null;
                echo json_encode($data);
                $parr = null;
                exit;
            }
        }
        $status = $this->paynow->pollTransaction($pollUrl);
        if ($status->paid()) {
            $parr = null;
            $parr['reference'] = $status->reference();
            $parr['paynowReference'] = $status->paynowReference();
            $parr['amount'] = $status->amount();
            $parr['status'] = $status->status();
            $pd = array('status' => $parr);
            $json = json_encode($pd);
            $db->insertData['paynowjson'] = $json;
            $db->insertData['status'] = 'paid';
            $this->send_email($receiver = $co->customer_email, $subject = "Secret Link", $body = $emailbody);
            $tryifsuccess = $sms->clicksms_send(strval($pmt->id), strval($pmt->unique_id), $co->link ?? null, $mobile);
            if ($tryifsuccess==true) {
                $db->insertData['sms_sent'] = $pmt->sms_sent??0+1;
            }
            $db->update();
            $data['msg'] = "Status found";
            $data['success'] = true;
            $data['data'] = $parr;
            echo json_encode($data);
            $parr = null;
            exit;
        } else if ($status->status()) {
            $parr = null;
            $parr['reference'] = $status->reference() ?? 'NA';
            $parr['paynowReference'] = $status->paynowReference() ?? 'NA';
            $parr['amount'] = $status->amount() ?? 0;
            $parr['status'] = $status->status() ?? 'NA';
            $pd = array('status' => $parr);
            $json = json_encode($pd);
            $db->insertData['paynowjson'] = $json;
            $db->insertData['status'] = $status->status() ?? 'NA';
            $send=false;
            if ($pmt->sms_sent=='0' || $pmt->sms_sent==null) {
                switch (strtolower($db->insertData['status'])) {
                    case 'paid':
                        $send=true;
                        break;
                    case 'awaiting delivery':
                        $send=true;
                        break;
                    case 'delivered':
                        $send=true;
                        break;
                    default:
                        $send=false;
                        break;
                }
            }
            if ($send===true) {
                $try = $sms->clicksms_send(strval($pmt->id), strval($pmt->unique_id), $co->link ?? null, $mobile);
                if ($try==true) {
                    $db->insertData['sms_sent'] = $pmt->sms_sent??0+1;
                }
            }
            $db->update();
            $data['msg'] = "Your game link will be sent to your email shortly";
            $data['success'] = true;
            $data['data'] = $parr;
            echo json_encode($data);
            $parr = null;
            exit;
        }
    }
    // admin satus end
    function save_json_file($response)
    {
        $filename = "payref/" . uniqid(time() . '_json') . '.json';
        $content = json_encode($response);
        $file = fopen($filename, 'w');
        if ($file) {
            // Write the content to the file
            fwrite($file, $content);
            // Close the file
            fclose($file);
        }
    }
    function send_email($receiver, $subject = null, $body = null)
    {
        try {
            $mail = php_mailer(new PHPMailer());
            $mail->setFrom(email, SITE_NAME . " SECRET LINK");
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->addAddress("$receiver", "$receiver");
            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
