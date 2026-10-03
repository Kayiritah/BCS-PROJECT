<?php
require_once 'config.php';
require_once 'includes/sms_functions.php';
if(!isset($_SESSION['user_id'])){ header("Location: login.php"); exit; }
$conn->query("CREATE TABLE IF NOT EXISTS marks (mark_id INT AUTO_INCREMENT PRIMARY KEY, student_id INT, class_id INT, subject VARCHAR(50), term VARCHAR(20), ca_no VARCHAR(10), marks INT, year YEAR, date_recorded DATETIME DEFAULT CURRENT_TIMESTAMP)");
$conn->query("CREATE TABLE IF NOT EXISTS subjects (subject_id INT AUTO_INCREMENT PRIMARY KEY, subject_name VARCHAR(50))");
$conn->query("CREATE TABLE IF NOT EXISTS sms_settings (id INT AUTO_INCREMENT PRIMARY KEY, at_username VARCHAR(100), at_apikey VARCHAR(255), sender_id VARCHAR(20))");
$conn->query("CREATE TABLE IF NOT EXISTS notifications (id INT AUTO_INCREMENT PRIMARY KEY, student_id INT, parent_phone VARCHAR(20), message TEXT, type ENUM('sms','whatsapp'), status VARCHAR(20), sent_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
$q=$conn->query("SHOW COLUMNS FROM students LIKE 'parent_phone'"); if(!$q||$q->num_rows==0){ $conn->query("ALTER TABLE students ADD COLUMN parent_phone VARCHAR(20)"); $conn->query("ALTER TABLE students ADD COLUMN parent_name VARCHAR(100)"); }
$conn->query("INSERT INTO sms_settings (at_username,at_apikey,sender_id) SELECT 'sandbox','your_api_key_here','BUNAMWAYA' WHERE NOT EXISTS (SELECT 1 FROM sms_settings)");
$q=$conn->query("SELECT COUNT(*) as c FROM subjects"); if($q && $q->fetch_assoc()['c']==0){ $conn->query("INSERT INTO subjects (subject_name) VALUES ('English'),('Mathematics'),('Science'),('SST')"); }
$classes=$conn->query("SELECT * FROM classes ORDER BY class_id"); $subjects_q=$conn->query("SELECT * FROM subjects");
$sel_c=intval($_GET['class_id']??0); $sel_s=$_GET['subject']??''; $sel_t=$_GET['term']??'Term 1'; $sel_ca=$_GET['ca_no']??'CA 1'; $sel_y=$_GET['year']??date('Y');
if(isset($_POST['save_marks'])){
  $cid=intval($_POST['class_id']); $sub=$conn->real_escape_string($_POST['subject']); $term=$conn->real_escape_string($_POST['term']); $ca=$conn->real_escape_string($_POST['ca_no']); $yr=intval($_POST['year']); $cnt=0;
  foreach($_POST['marks'] as $sid=>$mk){
    if($_POST['marks_raw'][$sid]==='' && $mk==='') continue;
    $sid=intval($sid); $mk=intval($mk); if($mk<0)$mk=0; if($mk>100)$mk=100;
    $chk=$conn->query("SELECT mark_id FROM marks WHERE student_id=$sid AND subject='$sub' AND term='$term' AND ca_no='$ca' AND year=$yr");
    if($chk && $chk->num_rows>0) $conn->query("UPDATE marks SET marks=$mk,class_id=$cid WHERE student_id=$sid AND subject='$sub' AND term='$term' AND ca_no='$ca' AND year=$yr");
    else $conn->query("INSERT INTO marks (student_id,class_id,subject,term,ca_no,marks,year) VALUES ($sid,$cid,'$sub','$term','$ca',$mk,$yr)");
    if($mk>0) notifyParent($sid,$sub,$ca,$mk,$term);
    $cnt++;
  }
  $msg="<div class='alert alert-success'>$cnt saved & parents notified! <a href='notifications.php'>View Log</a></div>";
}
include 'includes/header.php'; include 'includes/sidebar.php';
?>
<div class="main-content">
<h3 class="fw-bold" style="color:#0b4d1e">Continuous Assessment - P.4 to P.7</h3><small>6 CAs per term | Auto SMS/WhatsApp</small>
<?php if(isset($msg)) echo $msg;?>
<div class="card p-3 mt-3" style="border-radius:15px;border-top:4px solid #0b4d1e">
<form method="GET" class="row g-2"><div class="col-md-2"><label class="small fw-bold">Class</label><select name="class_id" class="form-control" required><option value="">Select</option><?php $classes->data_seek(0); while($c=$classes->fetch_assoc()){ $sl=$sel_c==$c['class_id']?'selected':''; echo "<option value='{$c['class_id']}' $sl>{$c['class_name']}</option>"; }?></select></div><div class="col-md-2"><label class="small fw-bold">Subject</label><select name="subject" class="form-control" required><option value="">Select</option><?php $subjects_q->data_seek(0); while($s=$subjects_q->fetch_assoc()){ $sl=$sel_s==$s['subject_name']?'selected':''; echo "<option $sl>{$s['subject_name']}</option>"; }?></select></div><div class="col-md-2"><label class="small fw-bold">Term</label><select name="term" class="form-control"><option <?= $sel_t=='Term 1'?'selected':''?>>Term 1</option><option <?= $sel_t=='Term 2'?'selected':''?>>Term 2</option><option <?= $sel_t=='Term 3'?'selected':''?>>Term 3</option></select></div><div class="col-md-2"><label class="small fw-bold">CA</label><select name="ca_no" class="form-control"><?php for($i=1;$i<=6;$i++){ $ca="CA $i"; $sl=$sel_ca==$ca?'selected':''; echo "<option $sl>$ca</option>"; }?></select></div><div class="col-md-1"><label class="small fw-bold">Year</label><input type="number" name="year" class="form-control" value="<?= $sel_y?>"></div><div class="col-md-3"><label class="small fw-bold">&nbsp;</label><button class="btn text-white w-100" style="background:#0b4d1e">Load Pupils</button></div></form>
</div>
<?php if($sel_c && $sel_s){ $students=$conn->query("SELECT * FROM students WHERE class_id=$sel_c ORDER BY first_name ASC"); if($students && $students->num_rows>0){?>
<div class="card p-3 mt-3" style="border-radius:15px"><div class="d-flex justify-content-between"><h5 class="fw-bold" style="color:#ff7a00"><?= $sel_s?> - <?= $sel_ca?> - <?= $sel_t?></h5><span class="badge" style="background:#0b4d1e"><?= $students->num_rows?> pupils</span></div>
<form method="POST"><input type="hidden" name="class_id" value="<?= $sel_c?>"><input type="hidden" name="subject" value="<?= $sel_s?>"><input type="hidden" name="term" value="<?= $sel_t?>"><input type="hidden" name="ca_no" value="<?= $sel_ca?>"><input type="hidden" name="year" value="<?= $sel_y?>">
<div class="table-responsive mt-3"><table class="table table-bordered"><thead style="background:#0b4d1e;color:#fff"><tr><th>#</th><th>Adm No</th><th>Name</th><th>Parent Phone</th><th width="140">Marks /100</th></tr></thead><tbody>
<?php $i=1; while($st=$students->fetch_assoc()){ $sid=$st['student_id']; $q=$conn->query("SELECT marks FROM marks WHERE student_id=$sid AND subject='$sel_s' AND term='$sel_t' AND ca_no='$sel_ca' AND year=$sel_y"); $ex=$q && $q->num_rows>0?$q->fetch_assoc()['marks']:''; echo "<tr><td>$i</td><td><small>{$st['admission_no']}</small></td><td>{$st['first_name']} {$st['last_name']}</td><td><small>{$st['parent_phone']}</small></td><td><input type='hidden' name='marks_raw[$sid]' value='$ex'><input type='number' name='marks[$sid]' value='$ex' class='form-control' min='0' max='100' placeholder='0-100'></td></tr>"; $i++; }?>
</tbody></table></div><div class="text-end"><button type="submit" name="save_marks" class="btn text-white px-5" style="background:#ff7a00;border-radius:20px;font-weight:700">Save & Notify Parents</button></div></form></div>
<?php } else echo "<div class='alert alert-warning mt-3'>No pupils in class</div>"; }?>
</div>
<?php include 'includes/footer.php';?>