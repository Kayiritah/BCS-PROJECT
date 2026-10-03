<?php
function cleanPhoneUG($phone){
  $phone = preg_replace('/[^0-9]/','',$phone);
  if(strlen($phone)==10 && $phone[0]=='0') $phone='256'.substr($phone,1);
  if(strlen($phone)==9) $phone='256'.$phone;
  return $phone;
}
function getAvg($sid,$sub,$term){
  global $conn; $y=date('Y');
  $q=$conn->query("SELECT AVG(marks) as a FROM marks WHERE student_id=$sid AND subject='$sub' AND term='$term' AND year=$y");
  return $q?round($q->fetch_assoc()['a']??0,1):0;
}
function sendSMS($phone,$msg){
  global $conn;
  $pr=cleanPhoneUG($phone); $plus='+'.$pr;
  $s=$conn->query("SELECT * FROM sms_settings LIMIT 1"); $s=$s?$s->fetch_assoc():['at_username'=>'sandbox','at_apikey'=>'','sender_id'=>'BUNAMWAYA'];
  if(!empty($s['at_apikey']) && strlen($s['at_apikey'])>15){
    $url='https://api.africastalking.com/version1/messaging';
    $data=http_build_query(['username'=>$s['at_username'],'to'=>$plus,'message'=>$msg,'from'=>$s['sender_id']]);
    $ch=curl_init($url); curl_setopt($ch,CURLOPT_POST,true); curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
    curl_setopt($ch,CURLOPT_HTTPHEADER,["apikey: ".$s['at_apikey'],"Content-Type: application/x-www-form-urlencoded","Accept: application/json"]);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true); curl_exec($ch); curl_close($ch);
  }
  return true;
}
function notifyParent($sid,$sub,$ca,$mark,$term){
  global $conn;
  $st=$conn->query("SELECT s.*,c.class_name FROM students s LEFT JOIN classes c ON s.class_id=c.class_id WHERE s.student_id=$sid")->fetch_assoc();
  if(!$st || empty($st['parent_phone'])) return;
  $avg=getAvg($sid,$sub,$term);
  $msg="BUNAMWAYA CENTRAL: Dear {$st['parent_name']}, {$st['first_name']} {$st['last_name']} ({$st['class_name']}) scored {$mark}% in $sub $ca $term. Avg $avg%. Knowledge is Power.";
  sendSMS($st['parent_phone'],$msg);
  $conn->query("INSERT INTO notifications (student_id,parent_phone,message,type,status) VALUES ($sid,'{$st['parent_phone']}','".$conn->real_escape_string($msg)."','sms','sent')");
  $conn->query("INSERT INTO notifications (student_id,parent_phone,message,type,status) VALUES ($sid,'{$st['parent_phone']}','".$conn->real_escape_string($msg)."','whatsapp','logged')");
}
?>