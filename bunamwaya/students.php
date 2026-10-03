<?php
require_once 'config.php';
if(!isset($_SESSION['user_id'])){ header("Location: login.php"); exit; }

function genAdmNo($conn){
  $year = date('Y');
  $q = $conn->query("SELECT admission_no FROM students ORDER BY student_id DESC LIMIT 1");
  if($q->num_rows>0){
    $last = $q->fetch_assoc()['admission_no'];
    $num = intval(substr($last, -4)) + 1;
  } else $num=1;
  return 'BCP/'.$year.'/'.str_pad($num,4,'0',STR_PAD_LEFT);
}

if(isset($_POST['add_student'])){
  $adm = genAdmNo($conn);
  $photoName = null;
  if(isset($_FILES['photo']) && $_FILES['photo']['error']==0 && $_FILES['photo']['size']>0){
    $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $photoName = str_replace('/','_', $adm).'_'.time().'.'.$ext;
    if(!is_dir("images/students")) mkdir("images/students", 0777, true);
    move_uploaded_file($_FILES['photo']['tmp_name'], "images/students/".$photoName);
  }
  $age = intval($_POST['age']);
  $class_id = intval($_POST['class_id'])?: null;
  $fn = $_POST['father_name']?? null; if($fn=="") $fn=null;
  $fc = $_POST['father_contact']?? null; if($fc=="") $fc=null;
  $mn = $_POST['mother_name']?? null; if($mn=="") $mn=null;
  $mc = $_POST['mother_contact']?? null; if($mc=="") $mc=null;
  $gn = $_POST['guardian_name']?? null; if($gn=="") $gn=null;
  $gc = $_POST['guardian_contact']?? null; if($gc=="") $gc=null;
  $gr = $_POST['guardian_relation']?? null; if($gr=="") $gr=null;
  $prev = $_POST['prev_school']?? null; if($prev=="") $prev=null;

  $stmt = $conn->prepare("INSERT INTO students (admission_no, first_name, last_name, date_of_birth, age, sex, religion, village, father_name, father_contact, mother_name, mother_contact, guardian_name, guardian_contact, guardian_relation, disability, class_id, previous_school, talent, photo) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
  $stmt->bind_param("ssssisssssssssssisss", $adm, $_POST['first_name'], $_POST['last_name'], $_POST['dob'], $age, $_POST['sex'], $_POST['religion'], $_POST['village'], $fn, $fc, $mn, $mc, $gn, $gc, $gr, $_POST['disability'], $class_id, $prev, $_POST['talent'], $photoName);

  if($stmt->execute()){
    $msg = "<div class='alert alert-success'>Pupil Admitted! <b>$adm</b> - ".$_POST['first_name']." ".$_POST['last_name']."</div>";
  } else {
    $msg = "<div class='alert alert-danger'>Error: ".$conn->error."</div>";
  }
}

if(isset($_GET['del'])){
  $id=intval($_GET['del']);
  $p=$conn->query("SELECT photo FROM students WHERE student_id=$id")->fetch_assoc()['photo'];
  if($p && file_exists("images/students/$p")) unlink("images/students/$p");
  $conn->query("DELETE FROM students WHERE student_id=$id");
  header("Location: students.php"); exit;
}

if(isset($_GET['print'])){
  $id=intval($_GET['print']);
  $s=$conn->query("SELECT s.*, c.class_name FROM students s LEFT JOIN classes c ON s.class_id=c.class_id WHERE s.student_id=$id")->fetch_assoc();
  if(!$s) die("Student not found");
  $photo = $s['photo'] && file_exists("images/students/".$s['photo'])? "images/students/".$s['photo'] : "images/pupils.jpeg";
  echo "<html><head><title>Admission Form - {$s['admission_no']}</title><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'><style>@media print{.no-print{display:none}} body{font-family:sans-serif}.form-box{border:3px solid #0b4d1e; padding:25px; border-radius:15px}.header{border-bottom:4px solid #0b4d1e; padding-bottom:12px; margin-bottom:15px}</style></head><body class='p-3'>
  <div class='container' style='max-width:800px'><div class='form-box'>
  <div class='header d-flex justify-content-between align-items-center'><div class='d-flex align-items-center gap-3'><img src='images/logo.jpeg' style='width:75px;height:75px;object-fit:contain'><div><h4 class='fw-bold mb-0' style='color:#0b4d1e'>BUNAMWAYA CENTRAL PARENTS' NURSERY & PRIMARY SCHOOL</h4><small>KNOWLEDGE IS POWER | Bunamwaya, Kampala</small></div></div><img src='$photo' style='width:100px;height:100px;border-radius:12px;object-fit:cover;border:3px solid #ff7a00'></div>
  <h5 class='text-center fw-bold mt-3' style='background:#0b4d1e;color:#fff;padding:8px;border-radius:20px'>PUPIL ADMISSION FORM</h5>
  <table class='table table-bordered mt-3'><tr><td width='50%'><b>Admission No:</b> <span style='color:#ff7a00;font-weight:800'>{$s['admission_no']}</span></td><td><b>Date:</b> {$s['admission_date']}</td></tr>
  <tr><td colspan='2'><b>Name:</b> {$s['first_name']} {$s['last_name']} | <b>Sex:</b> {$s['sex']} | <b>DOB:</b> {$s['date_of_birth']} | <b>Age:</b> {$s['age']} | <b>Religion:</b> {$s['religion']}</td></tr>
  <tr><td><b>Village:</b> {$s['village']}</td><td><b>Class:</b> <span style='background:#ff7a00;color:#fff;padding:3px 12px;border-radius:12px'>{$s['class_name']}</span></td></tr>
  <tr><td><b>Prev School:</b> {$s['previous_school']}</td><td><b>Disability:</b> {$s['disability']} | <b>Talent:</b> {$s['talent']}</td></tr></table>
  <h6 class='fw-bold' style='color:#0b4d1e'>Parents / Guardian</h6>
  <table class='table table-bordered'><tr><td><b>Father:</b> {$s['father_name']} {$s['father_contact']}</td><td><b>Mother:</b> {$s['mother_name']} {$s['mother_contact']}</td></tr><tr><td colspan='2'><b>Guardian:</b> {$s['guardian_name']} ({$s['guardian_relation']}) - {$s['guardian_contact']}</td></tr></table>
  <div class='row mt-5'><div class='col-6 text-center'><p>_______________________<br><b>Parent/Guardian</b></p></div><div class='col-6 text-center'><p>_______________________<br><b>Headteacher - Stamp</b></p></div></div>
  <div class='text-center mt-4 no-print'><button onclick='window.print()' class='btn text-white' style='background:#0b4d1e'>Print</button> <button onclick='window.close()' class='btn btn-secondary'>Close</button></div></div></div></body></html>"; exit;
}

$classes = $conn->query("SELECT * FROM classes ORDER BY class_id");
$students = $conn->query("SELECT s.*, c.class_name FROM students s LEFT JOIN classes c ON s.class_id=c.class_id ORDER BY s.student_id DESC");
include 'includes/header.php'; include 'includes/sidebar.php';
?>
<div class="main-content">
<div class="d-flex justify-content-between align-items-center mb-4">
<div><h3 class="fw-bold" style="color:#0b4d1e">Pupil Admissions</h3><small>Bunamwaya Central - Admissions</small></div>
<button class="btn text-white" style="background:#ff7a00;border-radius:12px;font-weight:700" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fa fa-plus"></i> Admit New Pupil</button>
</div>
<?php if(isset($msg)) echo $msg;?>
<div class="card p-3"><div class="table-responsive">
<table class="table table-hover align-middle"><thead style="background:#0b4d1e;color:#fff"><tr><th>Adm No</th><th>Photo</th><th>Name / DOB / Age</th><th>Class</th><th>Village</th><th>Guardian/Parents</th><th>Action</th></tr></thead><tbody>
<?php while($s=$students->fetch_assoc()):
$photo = $s['photo'] && file_exists("images/students/".$s['photo'])? "images/students/".$s['photo'] : "images/pupils.jpeg";
?>
<tr>
<td><span class="badge" style="background:#0b4d1e"><?= $s['admission_no']?></span><br><small><?= $s['admission_date']?></small></td>
<td><img src="<?= $photo?>" style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid #ff7a00"></td>
<td><b><?= $s['first_name'].' '.$s['last_name']?></b><br><small>DOB: <?= $s['date_of_birth']?> | <b><?= $s['age']?>yrs</b> | <?= $s['sex']?></small></td>
<td><span class="badge" style="background:#ff7a00"><?= $s['class_name']?></span><br><small><?= $s['previous_school']?></small></td>
<td><?= $s['village']?></td>
<td><small><?php if($s['guardian_name']) echo "<b>G:</b> {$s['guardian_name']} ({$s['guardian_relation']}) {$s['guardian_contact']}<br>"; if($s['father_name']) echo "F: {$s['father_name']} {$s['father_contact']}<br>"; if($s['mother_name']) echo "M: {$s['mother_name']} {$s['mother_contact']}";?></small></td>
<td><a href="students.php?print=<?= $s['student_id']?>" target="_blank" class="btn btn-sm btn-success"><i class="fa fa-print"></i></a> <a href="?del=<?= $s['student_id']?>" onclick="return confirm('Delete?')" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></a></td>
</tr>
<?php endwhile;?></tbody></table>
</div></div>
</div>

<div class="modal fade" id="addModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content" style="border-radius:20px">
<div class="modal-header text-white" style="background:linear-gradient(135deg,#0b4d1e,#ff7a00)"><h5 class="modal-title fw-bold">New Admission Form</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
<form method="POST" enctype="multipart/form-data">
<div class="modal-body" style="max-height:75vh;overflow-y:auto">

<h6 class="fw-bold" style="color:#0b4d1e">1. Pupil Details</h6>
<div class="row g-2 mb-3">
<div class="col-md-4"><label class="small fw-bold">First Name *</label><input name="first_name" class="form-control" required></div>
<div class="col-md-4"><label class="small fw-bold">Last Name *</label><input name="last_name" class="form-control" required></div>
<div class="col-md-4"><label class="small fw-bold">Sex *</label><select name="sex" class="form-control" required><option value="">Select</option><option>Male</option><option>Female</option></select></div>
<div class="col-md-4"><label class="small fw-bold">Date of Birth *</label><input type="date" name="dob" class="form-control" required></div>
<div class="col-md-2"><label class="small fw-bold">Age *</label><input type="number" name="age" class="form-control" placeholder="e.g. 8" required></div>
<div class="col-md-3"><label class="small fw-bold">Religion</label><select name="religion" class="form-control"><option>Christian</option><option>Muslim</option><option>Catholic</option><option>Anglican</option><option>Other</option></select></div>
<div class="col-md-3"><label class="small">Photo (optional)</label><input type="file" name="photo" id="photoInput" class="form-control" accept="image/*" onchange="previewPhoto(event)"><img id="preview" style="width:100%;height:70px;object-fit:cover;border-radius:8px;margin-top:5px;display:none;border:2px solid #ff7a00"></div>
<div class="col-md-6"><label class="small fw-bold">Village *</label><input name="village" class="form-control" required></div>
<div class="col-md-3"><label class="small">Disability</label><select name="disability" class="form-control"><option>None</option><option>Physical</option><option>Visual</option><option>Hearing</option><option>Other</option></select></div>
<div class="col-md-3"><label class="small">Talent</label><select name="talent" class="form-control"><option>None</option><option>Dancing</option><option>Singing</option><option>Football</option><option>Netball</option><option>Drawing</option><option>Public Speaking</option></select></div>
</div>

<h6 class="fw-bold" style="color:#ff7a00">2. Parents </h6>
<div class="row g-2 mb-3">
<div class="col-md-6"><label class="small">Father Name</label><input name="father_name" class="form-control"></div>
<div class="col-md-6"><label class="small">Father Contact</label><input name="father_contact" class="form-control"></div>
<div class="col-md-6"><label class="small">Mother Name</label><input name="mother_name" class="form-control"></div>
<div class="col-md-6"><label class="small">Mother Contact</label><input name="mother_contact" class="form-control"></div>
</div>

<h6 class="fw-bold" style="color:#0b4d1e">3. Guardian</h6>
<div class="row g-2 mb-3">
<div class="col-md-4"><label class="small">Guardian Name</label><input name="guardian_name" class="form-control"></div>
<div class="col-md-4"><label class="small">Guardian Contact</label><input name="guardian_contact" class="form-control"></div>
<div class="col-md-4"><label class="small">Relation</label><select name="guardian_relation" class="form-control"><option value="">Select</option><option>Uncle</option><option>Aunt</option><option>Grandfather</option><option>Grandmother</option><option>Brother</option><option>Sister</option><option>Guardian</option><option>Other</option></select></div>
</div>

<h6 class="fw-bold" style="color:#0b4d1e">4. Academic</h6>
<div class="row g-2">
<div class="col-md-6"><label class="small fw-bold">Class Admitted To *</label><select name="class_id" class="form-control" required><option value="">Select Class</option><?php $classes->data_seek(0); while($c=$classes->fetch_assoc()) echo "<option value='{$c['class_id']}'>{$c['class_name']}</option>";?></select></div>
<div class="col-md-6"><label class="small">Previous School (where she/he comes from)</label><input name="prev_school" class="form-control"></div>
</div>

</div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="add_student" class="btn text-white" style="background:#0b4d1e;font-weight:700">Admit Pupil</button></div>
</form>
</div></div></div>

<script>
function previewPhoto(e){
  let r = new FileReader();
  r.onload = function(){ let img=document.getElementById('preview'); img.src=r.result; img.style.display='block'; }
  r.readAsDataURL(e.target.files[0]);
}
</script>
<?php include 'includes/footer.php';?>