<?php
$baseDir = "fees_data/"; if(!is_dir($baseDir)) mkdir($baseDir,0777,true);
$badges = ["badge.png","badge.jpeg","badge.jpg","logo.png","images/logo.jpeg"];
$school_badge="badge.jpeg"; foreach($badges as $b){ if(file_exists($b)){ $school_badge=$b; break; } }
$school_name = "BUNAMWAYA CENTRAL PARENTS' NUR. & PRI. SCH.";
$classes = ["BABY","MIDDLE","TOP","P.1","P.2","P.3","P.4","P.5","P.6","P.7"];
$terms = ["Term I 2025","Term II 2025","Term III 2025","Term I 2026"];
$default_fees = ["BABY"=>350000,"MIDDLE"=>350000,"TOP"=>400000,"P.1"=>450000,"P.2"=>450000,"P.3"=>500000,"P.4"=>550000,"P.5"=>600000,"P.6"=>650000,"P.7"=>700000];
$message = "";
$smsStatus = "";

if($_SERVER['REQUEST_METHOD']=='POST'){
 $pupil = trim($_POST['pupil']);
 $class = $_POST['class'];
 $term = $_POST['term'];
 $amount = (int)$_POST['amount'];
 $method = $_POST['method'];
 $received_by = $_POST['received_by'];
 $total_fee = (int)$_POST['total_fee'];
 $parent_phone = trim($_POST['parent_phone']?? ''); // NEW PHONE FIELD

 $classDir = $baseDir. preg_replace('/[^A-Za-z0-9]/','', $class). "/"; if(!is_dir($classDir)) mkdir($classDir,0777,true);
 $safeName = preg_replace('/[^A-Za-z0-9]/','_', $pupil);
 $file = $classDir. $safeName. ".json";
 $history = []; $paid_before = 0; $old_phone = $parent_phone;
 if(file_exists($file)){ $old = json_decode(file_get_contents($file), true); $history = $old['history']??[]; $paid_before = $old['total_paid']??0; $old_phone = $old['parent_phone']?? $parent_phone; if(!$parent_phone) $parent_phone = $old_phone; }
 $new_total_paid = $paid_before + $amount; $balance = $total_fee - $new_total_paid; if($balance<0) $balance=0;
 $receipt_no = "RCP-".strtoupper(substr(preg_replace('/[^A-Za-z0-9]/','',$class),0,2)).date("Ymd")."-".rand(1000,9999);
 $record = ['date'=>date('Y-m-d H:i:s'),'term'=>$term,'amount'=>$amount,'method'=>$method,'receipt'=>$receipt_no,'received_by'=>$received_by];
 $history[] = $record;
 $data = ['pupil'=>$pupil,'class'=>$class,'term'=>$term,'total_fee'=>$total_fee,'total_paid'=>$new_total_paid,'balance'=>$balance,'history'=>$history,'last_receipt'=>$receipt_no,'last_date'=>date('Y-m-d H:i:s'),'parent_phone'=>$parent_phone];
 file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));

 // === SMS INSERTED HERE ===
 if($parent_phone){
   require_once 'sms_helper.php';
   $msg = "Dear Parent, UGX ".number_format($amount)." received for ".$pupil." (".$class."). Total Paid: UGX ".number_format($new_total_paid).". Balance: UGX ".number_format($balance).". Receipt: ".$receipt_no.". Bunamwaya Central - KNOWLEDGE IS POWER.";
   $smsRes = sendSMS($parent_phone, $msg);
   if($smsRes['status']=='success'){ $smsStatus = " | 📱 SMS Sent to $parent_phone"; }
   else if($smsRes['status']=='disabled'){ $smsStatus = " | SMS Disabled (enable in settings.php)"; }
   else { $smsStatus = " | SMS Failed: ".$smsRes['msg']; }
 } else {
   $smsStatus = " | No parent phone - SMS not sent";
 }
 // === END SMS ===

 $message = "SUCCESS: $pupil paid UGX ".number_format($amount)." | Bal: ".number_format($balance)." | $receipt_no $smsStatus";
 $last_data = $data;
}
$selectedClass = $_GET['class']?? ''; $allFees = [];
if($selectedClass && is_dir($baseDir.$selectedClass)){ foreach(glob($baseDir.$selectedClass."/*.json") as $f){ $allFees[] = json_decode(file_get_contents($f), true); } }
else { foreach($classes as $c){ $cDir = $baseDir.preg_replace('/[^A-Za-z0-9]/','',$c)."/"; if(is_dir($cDir)){ foreach(glob($cDir."/*.json") as $f){ $allFees[] = json_decode(file_get_contents($f), true); } } } }
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Fees - Bunamwaya</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial}
body{background:#f0f2f5;padding:15px;display:flex;gap:18px;justify-content:center}
.form-box{width:390px;background:#fff;padding:18px;border-radius:12px;border:2px solid #0e4d1b;height:fit-content;position:sticky;top:15px}
.school-head{display:flex;align-items:center;gap:12px;margin-bottom:14px;padding-bottom:12px;border-bottom:2px solid #0e4d1b}
.school-head img{width:65px;height:65px;object-fit:cover;border:2px solid #0e4d1b;border-radius:10px;padding:2px;background:#0e4d1b}
.school-head h4{font-size:12px;color:#0e4d1b;line-height:1.4}
.form-box h3{background:#0e4d1b;color:#fff;text-align:center;padding:12px;border-radius:8px;margin-bottom:14px}
.form-box label{font-size:11px;font-weight:900;color:#0e4d1b;display:block;margin-top:8px}
.form-box input,.form-box select{width:100%;padding:10px;margin:5px 0 10px 0;border:1.5px solid #ccc;border-radius:6px;font-size:13px}
.row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.btn{width:100%;padding:12px;border:none;border-radius:8px;font-weight:900;cursor:pointer;margin-top:8px}
.btn-pay{background:#0e4d1b;color:#fff}.btn-back{background:#333;color:#fff;display:block;text-align:center;text-decoration:none;padding:10px;border-radius:8px;margin-bottom:8px;font-size:12px}
.alert{padding:10px;background:#d4edda;border:1.5px solid #0e4d1b;border-radius:6px;font-size:12px;font-weight:800;color:#0e4d1b;margin-bottom:12px}
.main{flex:1;max-width:850px;background:#fff;border-radius:12px;padding:18px;border:2px solid #0e4d1b}
.tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}
.tab{padding:8px 14px;background:#fff;border:2px solid #0e4d1b;border-radius:20px;font-weight:800;text-decoration:none;color:#0e4d1b;font-size:12px}
.tab.active,.tab:hover{background:#0e4d1b;color:#fff}
table{width:100%;border-collapse:collapse;font-size:12px;margin-top:10px}
th,td{border:1.5px solid #ccc;padding:8px;text-align:left} th{background:#0e4d1b;color:#fff;font-size:11px}
.bal{font-weight:900}.bal-zero{color:green}.bal-owe{color:red}
.search{width:100%;padding:10px;border:2px solid #0e4d1b;border-radius:8px;margin-bottom:12px;font-size:13px}
@media print{.form-box,.no-print{display:none!important}.main{border:none;width:100%}}
</style></head><body>
<div class="form-box no-print">
<div class="school-head">
<img src="<?=$school_badge?>?v=<?=time()?>" alt="Badge">
<div><h4><?=$school_name?><br><small style="color:#ff7a00;">"KNOWLEDGE IS POWER"</small></h4></div>
</div>
<a href="dashboard.php" class="btn-back">⬅️ DASHBOARD</a>
<a href="report_manager.php" class="btn-back" style="background:#0e4d1b;">📂 REPORTS BY CLASS</a>
<h3>💰 FEES COLLECTION</h3>
<?php if($message):?><div class="alert"><?=$message?><br><a href="fees_receipt.php?class=<?=preg_replace('/[^A-Za-z0-9]/','',$last_data['class'])?>&pupil=<?=preg_replace('/[^A-Za-z0-9]/','_',$last_data['pupil'])?>" style="color:#ff7a00;">🖨️ PRINT RECEIPT NOW</a></div><?php endif;?>
<form method="POST">
<label>Pupil Name *</label><input type="text" name="pupil" required placeholder="e.g. ABAMATSIKO LYT">
<label>Parent Phone (for SMS) *</label><input type="text" name="parent_phone" required placeholder="e.g. 0772896922" style="border-color:#ff7a00;background:#fff8f0">
<div class="row">
<div><label>Class</label><select name="class" id="classSel" onchange="updateFee()" required>
<?php foreach($classes as $c):?><option value="<?=$c?>"><?=$c?></option><?php endforeach;?></select></div>
<div><label>Term</label><select name="term" required><?php foreach($terms as $t):?><option><?=$t?></option><?php endforeach;?></select></div>
</div>
<label>Total Term Fee (UGX)</label><input type="number" name="total_fee" id="total_fee" value="600000" required>
<label>Amount Paying Now (UGX)</label><input type="number" name="amount" required placeholder="200000">
<div class="row">
<div><label>Method</label><select name="method"><option>Cash</option><option>Mobile Money</option><option>Bank</option><option>Bursary</option></select></div>
<div><label>Received By</label><input type="text" name="received_by" value="Bursar" required></div>
</div>
<button class="btn btn-pay" type="submit">💾 COLLECT & SEND SMS</button>
</form>
</div>
<div class="main">
<h3 style="color:#0e4d1b;margin-bottom:12px;">📊 FEES - <?= $selectedClass? $selectedClass : "ALL CLASSES"?> (<?=count($allFees)?>)</h3>
<div class="tabs no-print">
<a href="fees.php" class="tab <?= $selectedClass==''?'active':''?>">ALL</a>
<?php foreach($classes as $c): $cCode=preg_replace('/[^A-Za-z0-9]/','',$c); $cnt=is_dir($baseDir.$cCode)? count(glob($baseDir.$cCode."/*.json")):0;?>
<a href="?class=<?=$cCode?>" class="tab <?= $selectedClass==$cCode?'active':''?>"><?=$c?> (<?=$cnt?>)</a>
<?php endforeach;?>
</div>
<input type="text" id="searchInput" class="search no-print" placeholder="🔍 Search pupil..." onkeyup="searchTable()">
<table id="feesTable">
<tr><th>Pupil</th><th>Class</th><th>Total</th><th>Paid</th><th>Balance</th><th>Phone</th><th>Last</th><th class="no-print">Action</th></tr>
<?php usort($allFees, fn($a,$b)=> strcmp($a['pupil'],$b['pupil'])); foreach($allFees as $f):?>
<tr><td><b><?=$f['pupil']?></b><br><small><?=$f['last_receipt']?></small></td><td><?=$f['class']?></td><td><?=number_format($f['total_fee'])?></td><td style="color:#0e4d1b;font-weight:900"><?=number_format($f['total_paid'])?></td><td class="bal <?= $f['balance']==0?'bal-zero':'bal-owe'?>"><?=number_format($f['balance'])?></td><td><small><?=htmlspecialchars($f['parent_phone']??'')?></small></td><td><small><?=$f['last_date']?></small></td><td class="no-print"><a href="fees_receipt.php?class=<?=preg_replace('/[^A-Za-z0-9]/','',$f['class'])?>&pupil=<?=preg_replace('/[^A-Za-z0-9]/','_',$f['pupil'])?>" style="background:#ff7a00;color:#fff;padding:6px 10px;border-radius:5px;text-decoration:none;font-weight:800;font-size:11px;">🧾 RECEIPT</a></td></tr>
<?php endforeach;?>
</table>
</div>
<script>
const fees = <?=json_encode($default_fees)?>;
function updateFee(){ let c=document.getElementById('classSel').value; document.getElementById('total_fee').value=fees[c]||600000; }
function searchTable(){ let input=document.getElementById('searchInput').value.toLowerCase(); document.querySelectorAll('#feesTable tr').forEach((r,i)=>{ if(i==0) return; r.style.display=r.innerText.toLowerCase().includes(input)?'':'none'; }); }
updateFee();
</script>
</body></html>