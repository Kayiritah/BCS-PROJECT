<?php
require_once 'config.php';
if(!isset($_SESSION['user_id'])){ header("Location: login.php"); exit; }
require_once 'sms_helper.php';

// Badge
$badges = ["badge.png","badge.jpeg","images/logo.jpeg","images/badge.png"];
$school_badge = "badge.png";
foreach($badges as $b){ if(file_exists($b)){ $school_badge=$b; break; } }

// Handle send
$feedback = "";
if(isset($_POST['send_sms'])){
 $to = trim($_POST['to']);
 $msg = trim($_POST['message']);
 if($to && $msg){
   // If bulk: to = class
   if(str_starts_with($to,'CLASS:')){
     $class = substr($to,6);
     $q = $conn->query("SELECT parent_phone, student_name FROM students WHERE class='$class' AND parent_phone!=''");
     $sent=0;$fail=0;
     while($r=$q->fetch_assoc()){
       $res = sendSMS($r['parent_phone'], $msg);
       if($res['status']=='success') $sent++; else $fail++;
     }
     $feedback = "<div class='alert alert-success'>✅ Sent to $sent parents of $class, $fail failed</div>";
   } else {
     $res = sendSMS($to, $msg);
     if($res['status']=='success') $feedback = "<div class='alert alert-success'>✅ SMS Sent to $to</div>";
     else $feedback = "<div class='alert alert-danger'>❌ Failed: ".htmlspecialchars($res['msg'])."</div>";
   }
 }
}

include 'includes/header.php';
include 'includes/sidebar.php';
$smsSet = json_decode(@file_get_contents("settings_data.json"), true);
?>
<div class="main-content">
<div class="d-flex align-items-center gap-3 mb-3">
<div style="width:60px;height:60px;border-radius:12px;overflow:hidden;border:2px solid #ff7a00;background:#0b4d1e"><img src="<?=$school_badge?>?v=<?=time()?>" style="width:100%;height:100%;object-fit:cover"></div>
<div><h4 class="fw-bold mb-0" style="color:#0b4d1e">SMS Center</h4><small class="text-muted">Bunamwaya Central - <?=$smsSet['at_username']??'No AT set'?> | <?=$smsSet['at_sender_id']??''?></small></div>
<div class="ms-auto"><a href="settings.php" class="btn btn-sm" style="background:#0b4d1e;color:#fff;border-radius:20px">⚙️ AT Settings</a></div>
</div>

<?=$feedback?>

<div class="row g-3">
<div class="col-md-5">
<div class="card p-3 shadow-sm" style="border-radius:15px;border-top:4px solid #0b4d1e">
<h6 class="fw-bold" style="color:#0b4d1e"><i class="fa fa-paper-plane" style="color:#ff7a00"></i> Send SMS</h6>
<form method="POST" class="mt-3">
<label class="small fw-bold">To *</label>
<select name="to" class="form-select mb-2" required>
<option value="">-- Select --</option>
<?php
$cq=$conn->query("SELECT DISTINCT class FROM students ORDER BY class");
while($c=$cq->fetch_assoc()){
 echo "<option value='CLASS:{$c['class']}'>📚 All Parents - {$c['class']}</option>";
}
?>
<option value="">-- Or Single Number --</option>
</select>
<input type="text" name="to" id="singlePhone" class="form-control mb-2" placeholder="Or enter phone e.g. 0772896922" onfocus="document.querySelector('select[name=to]').value=''">
<small class="text-muted">Select class for bulk or type number for single</small>

<label class="small fw-bold mt-3">Message *</label>
<textarea name="message" id="smsMsg" class="form-control" rows="5" maxlength="160" required placeholder="Dear Parent, ..."></textarea>
<div class="d-flex justify-content-between mt-1"><small class="text-muted"><span id="charCount">0</span>/160</small><small class="text-muted">From: <?=$smsSet['at_sender_id']??'BUNAMWAYA'?></small></div>

<div class="mt-2">
<button type="button" class="btn btn-sm btn-outline-dark" onclick="document.getElementById('smsMsg').value='Dear Parent, Fees balance for {{NAME}} is UGX {{BALANCE}}. Please clear before exams. Bunamwaya Central.'">💰 Fees Reminder</button>
<button type="button" class="btn btn-sm btn-outline-dark" onclick="document.getElementById('smsMsg').value='Dear Parent, {{NAME}} results are out. Total: {{MARKS}}. Come collect report. Bunamwaya Central.'">📊 Results</button>
<button type="button" class="btn btn-sm btn-outline-dark" onclick="document.getElementById('smsMsg').value='Dear Parent, School meeting on {{DATE}} at 9am. Please attend. Bunamwaya Central.'">📅 Meeting</button>
</div>

<button type="submit" name="send_sms" class="btn w-100 mt-3 text-white" style="background:#0b4d1e;border-radius:10px;font-weight:800"><i class="fa fa-paper-plane"></i> SEND NOW</button>
</form>
<div class="mt-3 p-2" style="background:#fff3e0;border-radius:8px;font-size:11px">Test: <a href="sms_helper.php?test=0772896922">Send test to your number</a> (sandbox uses AFRICASTALKING)</div>
</div>
</div>

<div class="col-md-7">
<div class="card p-3 shadow-sm" style="border-radius:15px;border-top:4px solid #ff7a00">
<h6 class="fw-bold" style="color:#0b4d1e"><i class="fa fa-users"></i> Pupils & Parent Phones</h6>
<div style="max-height:500px;overflow:auto" class="mt-2">
<table class="table table-sm table-hover" style="font-size:12px">
<tr style="background:#0b4d1e;color:#fff"><th>Name</th><th>Class</th><th>Parent Phone</th><th>Action</th></tr>
<?php
$q=$conn->query("SELECT student_name, class, parent_phone FROM students ORDER BY class, student_name LIMIT 100");
while($r=$q->fetch_assoc()){
 $ph = htmlspecialchars($r['parent_phone']);
 echo "<tr><td>{$r['student_name']}</td><td>{$r['class']}</td><td>$ph</td><td><button class='btn btn-sm' style='background:#e8f5e9;color:#0b4d1e;font-size:10px' onclick=\"document.getElementById('singlePhone').value='$ph'; window.scrollTo(0,0)\">Select</button></td></tr>";
}
?>
</table>
</div>
</div>
</div>
</div>
</div>

<script>
document.getElementById('smsMsg').addEventListener('input', e=>{document.getElementById('charCount').innerText=e.target.value.length});
</script>
<?php include 'includes/footer.php'; ?>