<?php
require_once 'config.php';
if(!isset($_SESSION['user_id'])){ header("Location: login.php"); exit; }

// Ensure tables exist
$conn->query("CREATE TABLE IF NOT EXISTS classes (class_id INT AUTO_INCREMENT PRIMARY KEY, class_name VARCHAR(50), class_teacher VARCHAR(100))");
$conn->query("CREATE TABLE IF NOT EXISTS students (student_id INT AUTO_INCREMENT PRIMARY KEY, admission_no VARCHAR(30), first_name VARCHAR(100), last_name VARCHAR(100), class_id INT, sex ENUM('Male','Female'), dob DATE, village VARCHAR(100), parent_name VARCHAR(100), parent_phone VARCHAR(20), status VARCHAR(20) DEFAULT 'Active', date_added DATETIME DEFAULT CURRENT_TIMESTAMP)");

$q=$conn->query("SELECT COUNT(*) as c FROM classes");
if($q && $q->fetch_assoc()['c']==0){
  $conn->query("INSERT INTO classes (class_name,class_teacher) VALUES ('P.4','Mr. Kato'),('P.5','Ms. Namata'),('P.6','Mr. Ssemakula'),('P.7','Mrs. Nankya')");
}

$classes = $conn->query("SELECT * FROM classes ORDER BY class_id ASC");
$sel_id = intval($_GET['class_id']?? 0);
if($sel_id==0 && $classes->num_rows>0){ $classes->data_seek(0); $sel_id=$classes->fetch_assoc()['class_id']; $classes->data_seek(0); }

if(isset($_GET['delete'])){
  $id=intval($_GET['delete']);
  $conn->query("DELETE FROM students WHERE student_id=$id");
  header("Location: students_records.php?class_id=$sel_id"); exit;
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>
<div class="main-content">
<style>
.class-btn{ border:2px solid #0e4d1b; color:#0e4d1b; border-radius:25px; font-weight:700; padding:8px 18px; margin:3px; text-decoration:none; background:#fff; display:inline-block; }
.class-btn.active,.class-btn:hover{ background:#0e4d1b; color:#fff; }
.stat-card{ border-radius:15px; padding:18px; text-align:center; border:none; box-shadow:0 4px 12px rgba(0,0,0,0.05); }
</style>

<div class="d-flex justify-content-between align-items-center flex-wrap">
<div>
<h3 class="fw-bold" style="color:#0e4d1b">Master Records by Class</h3>
<p class="text-muted" style="font-size:13px">Grouped by class P.4 - P.7 | Boys / Girls / Total count | Admitted from Admission page</p>
</div>
<a href="students.php?class_id=<?= $sel_id?>" class="btn text-white" style="background:#0e4d1b; border-radius:25px;"><i class="fa fa-user-plus"></i> Admit to this Class</a>
</div>

<!-- CLASS SELECTOR -->
<div class="card p-3 mt-3" style="border-radius:15px;">
<h6 class="fw-bold" style="color:#0e4d1b">SELECT CLASS:</h6>
<?php
if($classes){
  $classes->data_seek(0);
  while($c=$classes->fetch_assoc()){
    $cid=$c['class_id'];
    $b=$conn->query("SELECT COUNT(*) as cc FROM students WHERE class_id=$cid AND sex='Male'")->fetch_assoc()['cc']??0;
    $g=$conn->query("SELECT COUNT(*) as cc FROM students WHERE class_id=$cid AND sex='Female'")->fetch_assoc()['cc']??0;
    $t=$b+$g;
    $act = $sel_id==$cid? 'active' : '';
    echo "<a href='?class_id=$cid' class='class-btn $act'>{$c['class_name']} <span style='background:".($act?'#ff7a00':'#e8f5e9')."; color:".($act?'#fff':'#0e4d1b')."; padding:2px 8px; border-radius:12px; font-size:12px'>B:$b G:$g T:$t</span></a>";
  }
}
?>
</div>

<?php if($sel_id){
  $cl=$conn->query("SELECT * FROM classes WHERE class_id=$sel_id")->fetch_assoc();
  $boys=$conn->query("SELECT COUNT(*) as c FROM students WHERE class_id=$sel_id AND sex='Male'")->fetch_assoc()['c']??0;
  $girls=$conn->query("SELECT COUNT(*) as c FROM students WHERE class_id=$sel_id AND sex='Female'")->fetch_assoc()['c']??0;
  $total=$boys+$girls;
?>
<div class="row g-3 mt-3">
<div class="col-md-3"><div class="stat-card" style="background:#0e4d1b; color:#fff;"><h3 class="fw-bold"><?= $cl['class_name']??''?></h3><small style="color:#ff7a00"><?= $cl['class_teacher']??''?></small><br><small>Class Teacher</small></div></div>
<div class="col-md-3"><div class="stat-card" style="background:#fff; border-left:5px solid #0e4d1b;"><h3 class="fw-bold" style="color:#0e4d1b"><?= $boys?></h3><small>Boys</small></div></div>
<div class="col-md-3"><div class="stat-card" style="background:#fff; border-left:5px solid #ff7a00;"><h3 class="fw-bold" style="color:#ff7a00"><?= $girls?></h3><small>Girls</small></div></div>
<div class="col-md-3"><div class="stat-card" style="background:#fff3e0; border-left:5px solid #ff7a00;"><h3 class="fw-bold"><?= $total?></h3><small style="color:#0e4d1b">TOTAL PUPILS</small></div></div>
</div>

<div class="card p-3 mt-3" style="border-radius:15px;">
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
<h6 class="fw-bold mb-0" style="color:#0e4d1b"><i class="fa fa-users" style="color:#ff7a00"></i> <?= $cl['class_name']??''?> Pupils - Master List (<?= $total?>)</h6>
<div class="d-flex gap-2">
<input type="text" id="searchBox" class="form-control form-control-sm" placeholder="Search name / adm no..." style="border-radius:20px; width:220px;" onkeyup="searchTable()">
<a href="ca_report.php?class_id=<?= $sel_id?>" class="btn btn-sm text-white" style="background:#ff7a00; border-radius:20px;">CA Report</a>
</div>
</div>

<div class="table-responsive">
<table class="table table-bordered table-striped table-hover" id="masterTable" style="font-size:13px;">
<thead style="background:#0e4d1b; color:#fff;"><tr><th>#</th><th>ADM NO</th><th>Full Name</th><th>Sex</th><th>DOB</th><th>Parent</th><th>Phone</th><th>Village</th><th>Status</th><th>Action</th></tr></thead>
<tbody>
<?php
$res=$conn->query("SELECT * FROM students WHERE class_id=$sel_id ORDER BY first_name ASC");
$n=1;
if($res && $res->num_rows>0){
 while($s=$res->fetch_assoc()){
   // FIXED - NO MORE UNDEFINED KEY dob
   $admission_no = $s['admission_no'] ?? '';
   $first_name = $s['first_name'] ?? '';
   $last_name = $s['last_name'] ?? '';
   $sex = $s['sex'] ?? '';
   $dob_raw = $s['dob'] ?? $s['date_of_birth'] ?? '';
   $dob = ($dob_raw && $dob_raw!='0000-00-00') ? date('d/m/Y',strtotime($dob_raw)) : '-';
   $parent_name = $s['parent_name'] ?? '';
   $parent_phone = $s['parent_phone'] ?? '';
   $village = $s['village'] ?? '';
   $status = $s['status'] ?? 'Active';
   $student_id = $s['student_id'] ?? 0;

   $sex_color = ($sex=='Male') ? '#0e4d1b' : '#ff7a00';
   $status_color = ($status=='Active') ? '#0e4d1b' : '#dc3545';

   echo "<tr>
   <td>$n</td>
   <td><b>$admission_no</b></td>
   <td><b>$first_name $last_name</b></td>
   <td><span class='badge' style='background:$sex_color'>$sex</span></td>
   <td><small>$dob</small></td>
   <td><small>$parent_name</small></td>
   <td><small>$parent_phone</small></td>
   <td><small>$village</small></td>
   <td><span class='badge' style='background:$status_color'>$status</span></td>
   <td>
     <a href='pupil_report.php?student_id=$student_id' class='btn btn-sm' style='background:#e8f5e9; color:#0e4d1b;'><i class='fa fa-file'></i></a>
     <a href='students.php?edit=$student_id' class='btn btn-sm' style='background:#fff3e0; color:#ff7a00;'><i class='fa fa-edit'></i></a>
     <a href='?class_id=$sel_id&delete=$student_id' onclick=\"return confirm('Delete $first_name?')\" class='btn btn-sm btn-danger'><i class='fa fa-trash'></i></a>
   </td></tr>";
   $n++;
 }
}else{
 echo "<tr><td colspan='10' class='text-center py-5'><h6 class='text-muted'>No pupils in ".($cl['class_name']??'')." yet</h6><a href='students.php?class_id=$sel_id' class='btn btn-sm mt-2 text-white' style='background:#0e4d1b; border-radius:20px;'>Admit Pupils Now</a></td></tr>";
}
?>
</tbody>
</table>
</div>
</div>
<?php }?>

</div>

<script>
function searchTable(){
  let input=document.getElementById('searchBox').value.toLowerCase();
  let rows=document.getElementById('masterTable').getElementsByTagName('tr');
  for(let i=1;i<rows.length;i++){
    rows[i].style.display = rows[i].innerText.toLowerCase().includes(input)? '' : 'none';
  }
}
</script>

<?php include 'includes/footer.php';?>