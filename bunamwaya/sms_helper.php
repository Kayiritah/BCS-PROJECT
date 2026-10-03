<?php
function loadSmsSettings(){
 $file = __DIR__."/settings_data.json";
 $defaults = ['at_username'=>'sandbox','at_api_key'=>'','at_sender_id'=>'BUNAMWAYA','sms_enabled'=>'0'];
 if(!file_exists($file)) return $defaults;
 $data = json_decode(@file_get_contents($file), true);
 return array_merge($defaults, $data?:[]);
}

function sendSMS($phone, $message){
 $s = loadSmsSettings();
 if(($s['sms_enabled']??'0')!='1') return ['status'=>'disabled','msg'=>'SMS disabled - enable in settings.php'];

 $username = trim($s['at_username']??'sandbox');
 $apiKey = trim($s['at_api_key']??'');
 $from = trim($s['at_sender_id']??'BUNAMWAYA');

 if(!$apiKey) return ['status'=>'error','msg'=>'No API Key - set in settings.php'];

 // Format phone
 $phone = preg_replace('/[^0-9+]/','',$phone);
 if(str_starts_with($phone,'0')) $phone = '+256'.substr($phone,1);
 if(!str_starts_with($phone,'+')) $phone = '+'.$phone;
 if(!str_starts_with($phone,'+256')) $phone = '+256'.ltrim($phone,'+');

 $url = ($username=='sandbox')? 'https://api.sandbox.africastalking.com/version1/messaging' : 'https://api.africastalking.com/version1/messaging';

 $data = http_build_query([
   'username' => $username,
   'to' => $phone,
   'message' => $message,
   'from' => $from
 ]);

 $ch = curl_init();
 curl_setopt($ch, CURLOPT_URL, $url);
 curl_setopt($ch, CURLOPT_POST, 1);
 curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 curl_setopt($ch, CURLOPT_HTTPHEADER, ["apikey: $apiKey","Content-Type: application/x-www-form-urlencoded","Accept: application/json"]);
 $response = curl_exec($ch);
 $err = curl_error($ch);
 curl_close($ch);

 if($err) return ['status'=>'error','msg'=>$err];
 $json = json_decode($response, true);
 if(isset($json['SMSMessageData']['Recipients'][0]['status']) && $json['SMSMessageData']['Recipients'][0]['status']=='Success'){
   return ['status'=>'success','msg'=>'Sent to '.$phone,'data'=>$json];
 } else {
   return ['status'=>'error','msg'=>$response,'data'=>$json];
 }
}
?>