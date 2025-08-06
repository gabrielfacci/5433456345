<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;


class AresSMSService extends Controller
{

  public static function sendSMS($payload) {
    $data = $payload;

    // URL Integration ()
    $urlIntegration = "https://sms.aresfun.com/integration/52bb9b4b-a109-4767-8eef-c4c2770ee993";

    // Check if type exists
    $types = ['new', 'new-pix', 'pix-paid', 'new-withdraw'];
    $type = '';
    if (!in_array($data['type'], $types)) {
      return false;
    }else{
      $type = $data['type'];
    }


    // Payload
    $payload = [
      "cpf" => $data['cpf'],
      "name" => $data['name'],
      "email" => $data['email'],
      "event" => $type,
      "event_identify" => $data['event_identify'],
      "phone" => $data['phone'],
      "username" => $data['username'],
      "checkout" => $data['checkout'],
      "value" => $data['value'],
    ];

    // Send SMS
    $jsonData = json_encode($payload);
    $ch = curl_init($urlIntegration);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $response = curl_exec($ch);
    curl_close($ch);

    // Check if the request was successful
    if ($response !== 'OK') {
      return false;
    }
    
    return true;
  }
  

}