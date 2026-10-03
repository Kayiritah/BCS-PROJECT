<?php
require_once 'config.php';
if(!isset($_SESSION['user_id'])){ header("Location: login.php"); exit; }
$classes=$conn->query("SELECT * FROM classes ORDER BY class_id");
$sel_c=intval($_GET['class_id']??0); $sel_term=$_GET['term']??'Term 1'; $sel_y=$_GET['year']??date('Y');
include 'includes/header.php'; include 'includes/sidebar.php';
?>
<div class="main-content">
<h3 class="fw-bold" style="color:#0b4d1e">Attendance Term Summary</h3><small>Total Boys, Girls, Total per Class + Absence Reasons</small>
<div class="card p-3 mt-3" style="border-radius:15px"><form method="GET" class="row g-2"><div class="col-md-3"><select name="class_id" class="form-control" required><option value="">Select Class</option><?php if($classes){ $classes->data_seek(0); while($c=$classes->fetch_assoc()){ $sl=$sel_c==$c['class_id']?'selected':''; echo "<option value='{$c['class_id']}' $sl>{$c['class_name']}</option>"; }}?></select></div><div class="col-md-2"><select name="term" class="form-control"><option <?= $sel_term=='Term 1'?'selected':''?>>Term 1</option><option <?= $sel_term=='Term 2'?'selected':''?>>Term 2</option><option <?= $sel_term=='Term 3'?'selected':''?>>Term 3</option></select></div><div class="col-md-2"><input type="number" name="year" class="form-control" value="<?= $sel_y ?>"></div><div class="col-md-2"><button class="btn w-100 text-white" style="background:#0b4d1e">View Summary</button></div></form></div>

<?php if($sel_c){
  $cl=$conn->query("SELECT * FROM classes WHERE class_id=$sel_c")->fetch_assoc();
  $boys=$conn->query("SELECT COUNT(*) as c FROM students WHERE class_id=$sel_c AND sex='Male'")->fetch_assoc()['c'];
  $girls=$conn->query("SELECT COUNT(*) as c FROM students WHERE class_id=$sel_c AND sex='Female'")->fetch_assoc()['c'];
  $total=$boys+$girls;
  $days=$conn->query("SELECT COUNT(DISTINCT att_date) as c FROM attendance WHERE class_id=$sel_c AND term='$sel_term' AND year=$sel_y")->fetch_assoc()['c']??0;
?>
<div class="row g-3 mt-3"><div class="col-md-2"><div class="card p-3 text-center" style="border-radius:15px"><h4 class="fw-bold" style="color:#0b4d1e"><?= $total ?></h4><small>Total Pupils</small></div></div><div class="col-md-2"><div class="card p-3 text-center" style="border-radius:15px"><h4 class="fw-bold" style="color:#0b4d1e"><?= $boys ?></h4><small>Boys</small></div></div><div class="col-md-2"><div class="card p-3 text-center" style="border-radius:15px"><h4 class="fw-bold" style="color:#ff7a00"><?= $girls ?></h4><small>Girls</small></div></div><div class="col-md-2"><div class="card p-3 text-center" style="border-radius:15px"><h4 class="fw-bold"><?= $days ?></h4><small>School Days</small></div></div><div class="col-md-4"><div class="card p-3" style="border-radius:15px;background:#e8f5e9"><small><b><?= $cl['class_name'] ?> - <?= $sel_term ?> <?= $sel_y ?></b><br>Boys: <?= $boys ?> | Girls: <?= $girls ?> | Total: <?= $total ?></small></div></div></div>

<div class="card p-3 mt-3" style="border-radius:15px"><div class="table-responsive"><table class="table table-bordered table-sm"><thead style="background:#0b4d1e;color:#fff"><tr><th>#</th><th>Adm</th><th>Name</th><th>Sex</th><th>Present</th><th>Absent</th><th>Late</th><th>Sick</th><th>Permission</th><th>% Present</th><th>Common Reason</th></tr></thead><tbody>
<?php
$students=$conn->query("SELECT * FROM students WHERE class_id=$sel_c ORDER BY first_name ASC");
$i=1; while($s=$students->fetch_assoc()){
  $sid=$s['student_id'];
  $q=$conn->query("SELECT status, COUNT(*) as c, GROUP_CONCAT(DISTINCT reason SEPARATOR ', ') as reasons FROM attendance WHERE student_id=$sid AND term='$sel_term' AND year=$sel_y GROUP BY status");
  $present=0; $absent=0; $late=0; $sick=0; $perm=0; $reasons='';
  if($q) while($r=$q->fetch_assoc()){ if($r['status']=='Present') $present=$r['c']; if($r['status']=='Absent') $absent=$r['c']; if($r['status']=='Late') $late=$r['c']; if($r['status']=='Sick') $sick=$r['c']; if($r['status']=='Permission') $perm=$r['c']; if(!empty($r['reasons']) && $r['reasons']!='') $reasons.=$r['reasons'].', '; }
  $total_days=$present+$absent+$late+$sick+$perm; $perc=$total_days>0?round(($present/$total_days)*100,1):0;
  $color=$perc>=80?'#0b4d1e':($perc>=60?'#ff7a00':'#d00');
  echo "<tr><td>$i</td><td><small>{$s['admission_no']}</small></td><td><b>{$s['first_name']} {$s['last_name']}</b></td><td>{$s['sex']}</td><td class='text-center' style='background:#e8f5e9'>$present</td><td class='text-center' style='background:#ffcccc'>$absent</td><td class='text-center'>$late</td><td class='text-center'>$sick</td><td class='text-center'>$perm</td><td class='text-center fw-bold' style='color:$color'>$perc%</td><td><small>$reasons</small></td></tr>";
  $i++;
}
?>
</tbody></table></div></div>
<?php } ?>
</div>
<?php include 'includes/footer.php'; ?>