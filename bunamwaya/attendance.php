<?php
require_once 'config.php';
if(!isset($_SESSION['user_id'])){ header("Location: login.php"); exit; }

$conn->query("CREATE TABLE IF NOT EXISTS attendance (
  att_id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT,
  class_id INT,
  att_date DATE,
  status ENUM('Present','Absent','Late','Sick','Permission') DEFAULT 'Present',
  reason VARCHAR(255),
  term VARCHAR(20),
  year YEAR,
  marked_by VARCHAR(100),
  marked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_att (student_id, att_date)
)");

$classes=$conn->query("SELECT * FROM classes ORDER BY class_id");
$sel_c=intval($_GET['class_id']??0); $sel_date=$_GET['att_date']??date('Y-m-d'); $sel_term=$_GET['term']??'Term 1'; $sel_y=$_GET['year']??date('Y');

if(isset($_POST['save_att'])){
  $cid=intval($_POST['class_id']); $date=$_POST['att_date']; $term=$conn->real_escape_string($_POST['term']); $yr=intval($_POST['year']); $by=$_SESSION['username']??'Admin';
  foreach($_POST['status'] as $sid=>$st){
    $sid=intval($sid); $status=$conn->real_escape_string($st); $reason=$conn->real_escape_string($_POST['reason'][$sid]??'');
    $conn->query("INSERT INTO attendance (student_id,class_id,att_date,status,reason,term,year,marked_by) VALUES ($sid,$cid,'$date','$status','$reason','$term',$yr,'$by') ON DUPLICATE KEY UPDATE status='$status', reason='$reason', term='$term', year=$yr, marked_by='$by'");
  }
  $msg="<div class='alert alert-success'>Attendance saved for ".date('d M Y',strtotime($date))." - $term</div>";
}
include 'includes/header.php'; include 'includes/sidebar.php';
?>
<div class="main-content">
<div class="d-flex justify-content-between align-items-center">
<div><h3 class="fw-bold mb-0" style="color:#0b4d1e">Student Attendance</h3><small>Daily per class | Reason | Term Summary | Boys/Girls/Total</small></div>
<div><a href="attendance_report.php" class="btn btn-sm text-white" style="background:#ff7a00;border-radius:20px">Term Summary Report</a></div>
</div>
<?php if(isset($msg)) echo $msg; ?>

<div class="card p-3 mt-3" style="border-radius:15px;border-top:4px solid #0b4d1e">
<form method="GET" class="row g-2"><div class="col-md-2"><label class="small fw-bold">Class *</label><select name="class_id" class="form-control" required><option value="">Select</option><?php if($classes){ $classes->data_seek(0); while($c=$classes->fetch_assoc()){ $sl=$sel_c==$c['class_id']?'selected':''; echo "<option value='{$c['class_id']}' $sl>{$c['class_name']}</option>"; }}?></select></div><div class="col-md-2"><label class="small fw-bold">Date</label><input type="date" name="att_date" class="form-control" value="<?= $sel_date ?>"></div><div class="col-md-2"><label class="small fw-bold">Term</label><select name="term" class="form-control"><option <?= $sel_term=='Term 1'?'selected':''?>>Term 1</option><option <?= $sel_term=='Term 2'?'selected':''?>>Term 2</option><option <?= $sel_term=='Term 3'?'selected':''?>>Term 3</option></select></div><div class="col-md-1"><label class="small fw-bold">Year</label><input type="number" name="year" class="form-control" value="<?= $sel_y ?>"></div><div class="col-md-2"><label class="small fw-bold">&nbsp;</label><button class="btn w-100 text-white" style="background:#0b4d1e">Load Class</button></div></form>
</div>

<?php if($sel_c){
  $cl=$conn->query("SELECT * FROM classes WHERE class_id=$sel_c")->fetch_assoc();
  $tot=$conn->query("SELECT COUNT(*) as c FROM students WHERE class_id=$sel_c")->fetch_assoc()['c'];
  $boys=$conn->query("SELECT COUNT(*) as c FROM students WHERE class_id=$sel_c AND sex='Male'")->fetch_assoc()['c'];
  $girls=$conn->query("SELECT COUNT(*) as c FROM students WHERE class_id=$sel_c AND sex='Female'")->fetch_assoc()['c'];
  $present_today=$conn->query("SELECT COUNT(*) as c FROM attendance WHERE class_id=$sel_c AND att_date='$sel_date' AND status='Present'")->fetch_assoc()['c']??0;
?>
<div class="row g-3 mt-2">
<div class="col-md-3"><div class="card p-2 text-center" style="border-radius:15px;border-left:5px solid #0b4d1e"><small>Total Pupils</small><h4 class="fw-bold mb-0" style="color:#0b4d1e"><?= $tot ?> (B:<?= $boys ?> G:<?= $girls ?>)</h4></div></div>
<div class="col-md-2"><div class="card p-2 text-center" style="border-radius:15px"><small>Present Today</small><h4 class="fw-bold mb-0" style="color:#0b4d1e"><?= $present_today ?></h4></div></div>
<div class="col-md-2"><div class="card p-2 text-center" style="border-radius:15px"><small>Absent</small><h4 class="fw-bold mb-0" style="color:#d00"><?php $q=$conn->query("SELECT COUNT(*) as c FROM attendance WHERE class_id=$sel_c AND att_date='$sel_date' AND status='Absent'"); echo $q?$q->fetch_assoc()['c']:0; ?></h4></div></div>
<div class="col-md-5"><div class="card p-2" style="border-radius:15px;background:#e8f5e9"><small><b><?= $cl['class_name'] ?> - <?= date('l, d M Y',strtotime($sel_date)) ?> - <?= $sel_term ?></b><br>Mark daily attendance. Reason required for Absent/Sick/Permission. This feeds Term Summary.</small></div></div>
</div>

<div class="card p-3 mt-3" style="border-radius:15px">
<form method="POST">
<input type="hidden" name="class_id" value="<?= $sel_c ?>"><input type="hidden" name="att_date" value="<?= $sel_date ?>"><input type="hidden" name="term" value="<?= $sel_term ?>"><input type="hidden" name="year" value="<?= $sel_y ?>">
<div class="table-responsive"><table class="table table-bordered table-sm"><thead style="background:#0b4d1e;color:#fff"><tr><th>#</th><th>Adm No</th><th>Name</th><th>Sex</th><th width="140">Status</th><th>Reason for Absence</th></tr></thead><tbody>
<?php
$students=$conn->query("SELECT * FROM students WHERE class_id=$sel_c ORDER BY first_name ASC");
$i=1; while($s=$students->fetch_assoc()){
  $sid=$s['student_id']; $chk=$conn->query("SELECT status,reason FROM attendance WHERE student_id=$sid AND att_date='$sel_date'"); $cur=$chk && $chk->num_rows>0 ? $chk->fetch_assoc() : ['status'=>'Present','reason'=>''];
  echo "<tr><td>$i</td><td><small>{$s['admission_no']}</small></td><td><b>{$s['first_name']} {$s['last_name']}</b></td><td><span class='badge' style='background:".($s['sex']=='Male'?'#0b4d1e':'#ff7a00')."'>{$s['sex']}</span></td>
  <td><select name='status[$sid]' class='form-control form-control-sm' onchange='this.style.background=this.value==\"Present\"?\"#e8f5e9\":this.value==\"Absent\"?\"#ffcccc\":\"#fff3cd\"' style='background:".($cur['status']=='Present'?'#e8f5e9':($cur['status']=='Absent'?'#ffcccc':'#fff3cd'))."'>
  <option ".($cur['status']=='Present'?'selected':'').">Present</option><option ".($cur['status']=='Absent'?'selected':'').">Absent</option><option ".($cur['status']=='Late'?'selected':'').">Late</option><option ".($cur['status']=='Sick'?'selected':'').">Sick</option><option ".($cur['status']=='Permission'?'selected':'').">Permission</option></select></td>
  <td><input type='text' name='reason[$sid]' class='form-control form-control-sm' placeholder='e.g Malaria, Funeral' value='{$cur['reason']}'></td></tr>";
  $i++;
}
?>
</tbody></table></div>
<div class="text-end"><button type="submit" name="save_att" class="btn text-white px-5" style="background:#ff7a00;border-radius:20px;font-weight:700">Save Attendance</button></div>
</form>
</div>
<?php } ?>
</div>
<?php include 'includes/footer.php'; ?>