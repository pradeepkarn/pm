<?php

// use GuzzleHttp\Client;

class SMS_ctrl
{
  function send(string $pmtid, string $trn, string $link, array $mobiles = array("254706936267"))
  {
    $username = 'D-Account';
    $password = '@Demo2019';
    $trn = strtoupper($trn);
    // Create Basic Auth header by encoding username and password in Base64
    $base64Credentials = base64_encode("$username:$password");
    $client = new \GuzzleHttp\Client();
    $body = array(
      "to" => $mobiles,
      "from" => "ORACOM-KE",
      "text" => "Payment confirmed!! TR No. $trn.  you have only one chance to use this coupon link, so don't share with anyone: $link Good luck!"
    );
    try {
      $response = $client->request('POST', 'http://api.messaging-service.com/sms/1/text/single', [
        'body' => json_encode($body),
        'headers' => [
          'accept' => 'application/json',
          'content-type' => 'application/json',
          'Authorization' => 'Basic ' . $base64Credentials
        ],
      ]);

      // Check if the response status code is 200
      if ($response->getStatusCode() == 200) {
        // Save the response body to a JSON file
        $this->save_json_file($response->getBody());
        return true;
        // myprint($response);
        // echo $response->getBody();
      } else {
        return false;
        // echo 'Error: Unexpected response status code - ' . $response->getStatusCode();
      }
    } catch (\GuzzleHttp\Exception\RequestException $e) {
      return false;
      // Handle Guzzle request exceptions
      // echo 'Error: ' . $e->getMessage();
    }
  }

  function clicksms_send(string $pmtid, string $trn, string $link, string $to = "263780995266")
  {

    $curl = curl_init();

    $postdata =  array(
      "from" => "Pay2Play",
      "to" => "$to",
      "message" => "Payment confirmed!! TR No. $trn.  you have only one chance to use this coupon link, so don't share with anyone: $link Good luck!",
      "refId" => "INV{$pmtid}"
    );
    try {
      curl_setopt_array($curl, array(

        CURLOPT_URL => 'https://clicksmsgateway.com',

        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_ENCODING => '',

        CURLOPT_MAXREDIRS => 10,

        CURLOPT_TIMEOUT => 0,

        CURLOPT_FOLLOWLOCATION => true,

        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

        CURLOPT_CUSTOMREQUEST => 'POST',

        CURLOPT_POSTFIELDS => json_encode($postdata),

        CURLOPT_HTTPHEADER => array(

          'Accept: application/json',

          'Content-Type: application/json',

          'Authorization: Bearer eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiI0OTYiLCJvaWQiOjQ5NiwidWlkIjoiYjUzNDEwODYtNmMzNy00NjQ5LWJjNzYtZDFkOTdiOWZlNWQ4IiwiYXBpZCI6Mjc3LCJpYXQiOjE2OTYyMzk0OTQsImV4cCI6MjAzNjIzOTQ5NH0.tLyDYskyHR2_ZhXkCR9WEQKGidSN_lqMYy8YNqLJnPJYG7qtmecEPWmeA7BLhAZ2ijcfF-hqPjnfykoVymab1A'

        ),

      ));
      $response = curl_exec($curl);
      curl_close($curl);
      return true;
    } catch (Exception $e) {
      return false;
    }


    // echo $response;
  }
  function test()
  {
    $this->send("100", "JAH7H6H897BW8734", "http://google.com", $mobiles = array("254706936267"));
  }
  function save_json_file($body)
  {
    $filename = "payref/sms/" . uniqid(time() . '_json') . '.json';
    $file = fopen($filename, 'w');
    if ($file) {
      // Write the response body to the file
      fwrite($file, $body);
      // Close the file
      fclose($file);
    }
  }
}
