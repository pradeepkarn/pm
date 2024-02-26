<?php

class Pay_now_ctrl
{

    function create_token($amt=0)
    {
        $apiUrl = "https://secure.3gdirectpay.com/API/v6/";
        $companyToken = "8D3DA73D-9D7F-4E09-96D4-3D44E7A83EA3";
        $service_date = date("Y/m/d H:i:s");
        $service_name = "Testing name";
        $redirect_url = "<RedirectURL>" . BASEURI . route('home') . "</RedirectURL>";
        $back_url = "<BackURL>" . BASEURI . route('home') . "</BackURL>";
        $redirect_url = null;
        $back_url = null;
        $amount = $amt??0;
        $currency = "USD";
        $Company_ref = "The ticket City";
        $xmlreq = <<<XML
        <?xml version="1.0" encoding="utf-8"?>
        <API3G>
        <CompanyToken>$companyToken</CompanyToken>
        <Request>createToken</Request>
        <Transaction>
        <PaymentAmount>$amount</PaymentAmount>
        <PaymentCurrency>$currency</PaymentCurrency>
        <CompanyRef>$Company_ref</CompanyRef>
        $redirect_url
        $back_url
        <CompanyRefUnique>0</CompanyRefUnique>
        <PTL>5</PTL>
        </Transaction>
        <Services>
        <Service>
            <ServiceType>3854</ServiceType>
            <ServiceDescription>$service_name</ServiceDescription>
            <ServiceDate>$service_date</ServiceDate>
        </Service>
        </Services>
        </API3G>
        XML;
        return $this->req($apiUrl, $xmlreq);
    }

    function verify_token($transactionToken)
    {
        $apiUrl = "https://secure.3gdirectpay.com/API/v6/";
        $companyToken = "8D3DA73D-9D7F-4E09-96D4-3D44E7A83EA3";
        $xmlRequest = <<<XML
        <?xml version="1.0" encoding="utf-8"?>
        <API3G>
          <CompanyToken>$companyToken</CompanyToken>
          <Request>verifyToken</Request>
          <TransactionToken>$transactionToken</TransactionToken>
        </API3G>
        XML;
        return $this->req($apiUrl, $xmlRequest);
    }
    function pay()
    {
        // print_r($_SESSION['cp']);
        // return;
        $php = $this->create_token($_SESSION['cp']['amount']);
        // $php->Result;
        // $php->ResultExplanation;
        // $php->TransToken;
        // $php->TransRef;
        if ($php == null) {
            msg_set("No data while creating token");
            return false;
        }
        $db = new Dbobjects;
        $db->tableName = 'payment';
        $db->pk($_SESSION['cp']['payment_id']);
        $db->insertData['trans_token'] = $php->TransToken;
        $db->insertData['trans_ref'] = $php->TransRef;
        $db->update();
        $apiUrl = "https://secure.3gdirectpay.com/API/v6/";
        $companyToken = "8D3DA73D-9D7F-4E09-96D4-3D44E7A83EA3";
        $transactionToken = $php->TransToken;
        $xmlRequest = <<<XML
                <?xml version="1.0" encoding="utf-8"?>
                <API3G>
                <CompanyToken>$companyToken</CompanyToken>
                <Request>GetMobilePaymentOptions</Request>
                <TransactionToken>$transactionToken</TransactionToken>
                </API3G>
                XML;
        $obj =  $this->req($apiUrl, $xmlRequest);
        // print_r($obj);
        // return;

        // $obj = arr($obj);
        $mobileoptions = isset($obj->paymentoptions->mobileoption) ? $obj->paymentoptions->mobileoption : [];
        // Now, $array contains the XML data as a PHP associative array
        $context = new stdClass;
        $context->mobileoptions = $mobileoptions;
        $context->TransRef = $php->TransRef;
        echo render_template('payments/instruction.php', $context);
        return;
    }

    function verify_payment_v7($req=null)
    {
        if (!isset($_POST['trans_ref'])) {
            msg_set('Invalid reference');
            echo msg_ssn();
            return false;
        }
        $transRef = strval($_POST['trans_ref']);
        $endpoint = "https://secure.3gdirectpay.com/API/v7/";
        $companyToken = "8D3DA73D-9D7F-4E09-96D4-3D44E7A83EA3";
        $userToken = "<userToken>26E692C5-4F6F-46CA-AE86-6007DBDE4DDA</userToken>";
        $userToken = null;
        $xmlData = <<<XML
        <?xml version="1.0" encoding="utf-8"?>
        <API3G>
            <CompanyToken>$companyToken</CompanyToken>
            <Request>getTransactionByRef</Request>
            <CompanyRef>$transRef</CompanyRef>
            $userToken
            <allTrans>1</allTrans>
            <descOrder>1</descOrder>
        </API3G>
        XML;
        $obj = $this->req($endpoint, $xmlData);
        if ($obj) {
            $code = isset($obj->code) ? $obj->code : null;
            if($code!="000"){
                msg_set("No transaction found");
                echo msg_ssn();
                return false;
            }
            $transaction = isset($obj->Transactions->Transaction) ? $obj->Transactions->Transaction : null;
            $explanation = isset($obj->Explanation) ? $obj->Explanation : null;
            

            $db = new Dbobjects;
            $db->tableName = 'payment';
            $db->showOne("update payment set transaction_status='{$obj->Transactions->TransactionStatus}' where trans_ref = '$transRef';");

            // Now, $array contains the XML data as a PHP associative array
            $context = new stdClass;
            $context->transaction = $transaction;
            $context->explanation = $explanation;
            $context->code = $code;
            echo render_template('payments/transaction.php', $context);
            return;
        }else{
            echo "No data";
        }
        // myprint($data);
    }

    function req($apiUrl, $xmlRequest)
    {
        $ch = curl_init();

        if (!$ch) {
            die("Couldn't initialize a cURL handle");
        }
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);

        $response = curl_exec($ch);
        $httpStatusCode = http_response_code();
        if ($httpStatusCode!="200") {
            msg_set("server busy");
            return false;
        }
        $this->save_xml_file($response);
        curl_close($ch);
        if ($response !== false) {
            $apiResponse = str_replace('&#8218', ',', $response);
            $apiResponse = str_replace('&#8216;', "'", $apiResponse);
            $apiResponse = str_replace('&#8217;', "'", $apiResponse);
            $apiResponse = str_replace('&#8220;', '"', $apiResponse);
            $apiResponse = str_replace('&#8221;', '"', $apiResponse);
            try {
                $phpobj = new SimpleXMLElement(strval($apiResponse));
            } catch (\Throwable $th) {
                msg_set("Too many request please wait some times");
                $phpobj = null;
            }
            if ($phpobj !== false) {
                return $phpobj;
            } else {
                msg_set("No data");
                return false;
            }
        } else {
            msg_set("Empty response from payment gateway");
            return false;
        }
    }

    function save_xml_file($response)
    {
        $filename = "payref/" . uniqid(time() . '_xmlfile') . '.xml'; // Specify the name of the file you want to create
        $content = $response; // Specify the content you want to insert
        // Open the file for writing (create it if it doesn't exist)
        $file = fopen($filename, 'w');
        if ($file) {
            // Write the content to the file
            fwrite($file, $content);
            // Close the file
            fclose($file);
        }
    }
    function clean_xml($xml)
    {
        $apiResponse = str_replace('&#8218', ',', $xml);
        $apiResponse = str_replace('&#8216;', "'", $apiResponse);
        $apiResponse = str_replace('&#8217;', "'", $apiResponse);
        $apiResponse = str_replace('&#8220;', '"', $apiResponse);
        $apiResponse = str_replace('&#8221;', '"', $apiResponse);
        return $apiResponse;
    }
    function test()
    {
        $token = $this->clean_xml(file_get_contents('payref/1695796514_xmlfile6513cd224856f.xml'));
        $instructions = $this->clean_xml(file_get_contents('payref/1695796845_xmlfile6513ce6da2932.xml'));
        $tkphp = new SimpleXMLElement(strval($token));
        $obj = new SimpleXMLElement(strval($instructions));
        $mobileoptions = isset($obj->paymentoptions->mobileoption) ? $obj->paymentoptions->mobileoption : [];
        // Now, $array contains the XML data as a PHP associative array
        $context = new stdClass;
        $context->mobileoptions = $mobileoptions;
        $context->TransRef = $tkphp->TransRef;
        echo render_template('payments/instruction.php', $context);
        return;
    }
    function verify_test()
    {
        $transref = $this->clean_xml(file_get_contents('payref/1695802893_xmlfile6513e60d9f536.xml'));
        $obj = new SimpleXMLElement(strval($transref));
        if (isset($obj->code) && $obj->code != "000") {
            return false;
        }
        $transaction = isset($obj->Transactions->Transaction) ? $obj->Transactions->Transaction : null;
        $explanation = isset($obj->Explanation) ? $obj->Explanation : null;
        $code = isset($obj->code) ? $obj->code : null;
        // Now, $array contains the XML data as a PHP associative array
        $context = new stdClass;
        $context->transaction = $transaction;
        $context->explanation = $explanation;
        $context->code = $code;
        echo render_template('payments/transaction.php', $context);
        return;
    }



    function handle_verify_token_response($xml)
    {
        // Extract specific data
        $arr['result_code'] = (string)$xml->Result;
        $arr['result_explanation'] = (string)$xml->ResultExplanation;
        $arr['customer_name'] = (string)$xml->CustomerName;
        $arr['customer_credit'] = (string)$xml->CustomerCredit;
        $arr['customer_credit_type'] = (string)$xml->CustomerCreditType;
        $arr['transaction_approval'] = (string)$xml->TransactionApproval;
        $arr['transaction_currency'] = (string)$xml->TransactionCurrency;
        $arr['transaction_amount'] = (float)$xml->TransactionAmount;
        $arr['fraud_alert'] = (string)$xml->FraudAlert;
        $arr['fraud_explanation'] = (string)$xml->FraudExplnation;
        $arr['transaction_net_amount'] = (float)$xml->TransactionNetAmount;
        $arr['transaction_settlement_date'] = (string)$xml->TransactionSettlementDate;
        $arr['rolling_reserve_amount'] = (float)$xml->TransactionRollingReserveAmount;
        $arr['rolling_reserve_date'] = (string)$xml->TransactionRollingReserveDate;
        $arr['customer_phone'] = (string)$xml->CustomerPhone;
        $arr['customer_country'] = (string)$xml->CustomerCountry;
        $arr['customer_address'] = (string)$xml->CustomerAddress;
        $arr['customer_city'] = (string)$xml->CustomerCity;
        $arr['customer_zip'] = (string)$xml->CustomerZip;
        $arr['mobile_payment_request'] = (string)$xml->MobilePaymentRequest;
        $arr['acc_ref'] = (string)$xml->AccRef;
        try {
            $db = new Dbobjects;
            $db->tableName = 'dp_response';
            $db->insertData = $arr;
            $db->create();
            return true;
        } catch (PDOException $th) {
            //throw $th;
            return false;
        }
    }
}
