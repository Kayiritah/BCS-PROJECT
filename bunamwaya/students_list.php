<?php
require_once 'config.php';
if(!isset($_SESSION['user_id'])){ header("Location: login.php"); exit; }

$classes = $conn->query("SELECT * FROM classes");
$class_id = intval($_GET['class_id']?? 0);
$search = $_GET['search']?? '';
$msg = '';

// SAVE STUDENT
if(isset($_POST['save_student'])){
  $first = $conn->real_escape_string($_POST['first_name']);
  $last = $conn->real_escape_string($_POST['last_name']);
  $adm = $conn->real_escape_string($_POST['admission_no']);
  $class = intval($_POST['class_id']);
  $gender = $conn->real_escape_string($_POST['gender']);
  $photoName = '';

  if(!empty($_FILES['photo']['name'])){
    if(!is_dir("uploads/students")) mkdir("uploads/students", 0777, true);
    $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $photoName = "STU_".time()."_".rand(100,999).".".$ext;
    move_uploaded_file($_FILES['photo']['tmp_name'], "uploads/students/".$photoName);
  }

  if(isset($_POST['student_id']) && intval($_POST['student_id'])>0){
    $sid = intval($_POST['student_id']);
    if($photoName!=''){
      $conn->query("UPDATE students SET first_name='$first', last_name='$last', admission_no='$adm', class_id=$class, gender='$gender', photo='$photoName' WHERE student_id=$sid");
    } else {
      $conn->query("UPDATE students SET first_name='$first', last_name='$last', admission_no='$adm', class_id=$class, gender='$gender' WHERE student_id=$sid");
    }
    $msg = "✅ Updated Successfully!";
  } else {
    $conn->query("INSERT INTO students (first_name, last_name, admission_no, class_id, gender, photo) VALUES ('$first','$last','$adm',$class,'$gender','$photoName')");
    $msg = "✅ Pupil Added Successfully!";
  }
}

// DELETE
if(isset($_GET['delete'])){
  $sid = intval($_GET['delete']);
  $conn->query("DELETE FROM students WHERE student_id=$sid");
  $msg = "🗑️ Deleted!";
}

include 'includes/header.php'; include 'includes/sidebar.php';
?>
<div class="main-content" style="background:#f0f4ff; min-height:100vh; padding:15px;">
<style>
.card{border-radius:18px; border:0; box-shadow:0 8px 30px rgba(0,0,0,0.06)}
.avatar{width:50px; height:50px; border-radius:50%; object-fit:cover; border:2px solid #164a9e}
.form-control{border-radius:20px}
.btn{border-radius:20px; font-weight:700}
</style>

<?php if($msg) echo "<div class='alert alert-success' style='border-radius:15px'>$msg</div>";?>

<div class="row">
<!-- LEFT: ADD FORM -->
<div class="col-md-4">
<div class="card p-4">
<h5 style="font-weight:800; color:#0e4d1b">➕ Add / Edit Pupil</h5>
<p style="font-size:11px; color:#666">Add existing pupils with photo + class</p>

<?php
$editData = null;
if(isset($_GET['edit'])){
  $eid = intval($_GET['edit']);
  $editData = $conn->query("SELECT * FROM students WHERE student_id=$eid")->fetch_assoc();
}
?>

<form method="POST" enctype="multipart/form-data">
<?php if($editData){ echo "<input type='hidden' name='student_id' value='{$editData['student_id']}'>"; }?>

<div class="mb-2">
<label style="font-size:11px; font-weight:800">FIRST NAME *</label>
<input name="first_name" class="form-control" required value="<?= $editData['first_name']?? ''?>" placeholder="e.g John">
</div>

<div class="mb-2">
<label style="font-size:11px; font-weight:800">LAST NAME *</label>
<input name="last_name" class="form-control" required value="<?= $editData['last_name']?? ''?>" placeholder="e.g Mukasa">
</div>

<div class="row g-2 mb-2">
<div class="col-6"><label style="font-size:11px; font-weight:800">ADMISSION NO</label><input name="admission_no" class="form-control" value="<?= $editData['admission_no']?? 'ADM'.rand(1000,9999)?>" placeholder="ADM001"></div>
<div class="col-6"><label style="font-size:11px; font-weight:800">GENDER</label><select name="gender" class="form-control"><option value="Male" <?= ($editData['gender']??'')=='Male'?'selected':''?>>Male</option><option value="Female" <?= ($editData['gender']??'')=='Female'?'selected':''?>>Female</option></select></div>
</div>

<div class="mb-2">
<label style="font-size:11px; font-weight:800">CLASS *</label>
<select name="class_id" class="form-control" required>
<option value="">-- Select Class --</option>
<?php $classes->data_seek(0); while($c=$classes->fetch_assoc()){ $sel = ($editData['class_id']??0)==$c['class_id']?'selected':''; echo "<option $sel value='{$c['class_id']}'>{$c['class_name']}</option>"; }?>
</select>
</div>

<div class="mb-3">
<label style="font-size:11px; font-weight:800">PHOTO (from phone/camera)</label>
<input type="file" name="photo" class="form-control" accept="image/*" onchange="previewImg(this)">
<div class="text-center mt-2">
<img id="preview" src="<?=!empty($editData['photo'])? "uploads/students/".$editData['photo'] : "https://ui-avatars.com/api/?name=New+Pupil&background=164a9e&color=fff"?>" style="width:90px; height:90px; border-radius:50%; border:3px solid #0e4d1b; object-fit:cover">
</div>
</div>

<button name="save_student" class="btn btn-success w-100 py-2" style="background:linear-gradient(90deg,#0e4d1b,#164a9e); border:0">💾 <?= $editData?'UPDATE':'ADD PUPIL'?></button>
<?php if($editData) echo "<a href='students_list.php' class='btn btn-secondary w-100 mt-2'>Cancel</a>";?>
</form>

</div>
</div>

<!-- RIGHT: LIST -->
<div class="col-md-8">
<div class="card p-3">
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
<h5 style="font-weight:800; color:#164a9e; margin:0">👥 Students List (<?= $conn->query("SELECT COUNT(*) as c FROM students".($class_id?" WHERE class_id=$class_id":""))->fetch_assoc()['c']?>)</h5>
<form method="GET" class="d-flex gap-2">
<select name="class_id" class="form-control" style="max-width:160px" onchange="this.form.submit()"><option value="0">All Classes</option><?php $classes->data_seek(0); while($c=$classes->fetch_assoc()){ $s=$class_id==$c['class_id']?'selected':''; echo "<option $s value='{$c['class_id']}'>{$c['class_name']}</option>"; }?></select>
<input name="search" value="<?= htmlspecialchars($search)?>" class="form-control" placeholder="Search name" style="max-width:150px">
<button class="btn btn-primary btn-sm">Search</button>
</form>
</div>

<div class="table-responsive">
<table class="table table-hover">
<thead style="background:#164a9e; color:#fff; font-size:11px"><tr><th>Photo</th><th>Name</th><th>Adm No</th><th>Class</th><th>Action</th></tr></thead>
<tbody>
<?php
$where = [];
if($class_id>0) $where[]="s.class_id=$class_id";
if($search!='') $where[]="(s.first_name LIKE '%$search%' OR s.last_name LIKE '%$search%' OR s.admission_no LIKE '%$search%')";
$whereSql = $where? "WHERE ".implode(" AND ", $where) : "";
$q = $conn->query("SELECT s.*, c.class_name FROM students s LEFT JOIN classes c ON c.class_id=s.class_id $whereSql ORDER BY s.class_id, s.first_name LIMIT 200");
while($row=$q->fetch_assoc()){
  $photoPath =!empty($row['photo']) && file_exists("uploads/students/".$row['photo'])? "uploads/students/".$row['photo'] : "https://ui-avatars.com/api/?name=".urlencode($row['first_name'].' '.$row['last_name'])."&background=164a9e&color=fff";
  echo "<tr>
  <td><img src='$photoPath' class='avatar' onerror=\"this.src='https://ui-avatars.com/api/?name=".urlencode($row['first_name'])."'\" ></td>
  <td><b>{$row['first_name']} {$row['last_name']}</b><br><small style='color:#666'>{$row['gender']}</small></td>
  <td><small>{$row['admission_no']}</small></td>
  <td><span style='background:#e3f2fd; color:#164a9e; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700'>{$row['class_name']}</span></td>
  <td>
  <a href='?edit={$row['student_id']}' class='btn btn-sm btn-warning'>✏️</a>
  <a href='report_manager.php?tab=enter&class_id={$row['class_id']}' class='btn btn-sm btn-success'>📝 Report</a>
  <a href='?delete={$row['student_id']}' onclick=\"return confirm('Delete?')\" class='btn btn-sm btn-danger'>🗑️</a>
  </td>
  </tr>";
}
?>
</tbody>
</table>
</div>

<div class="mt-3 p-3" style="background:#e8f5e9; border-radius:12px; font-size:12px">
<b>Next:</b> After adding pupils here with photos, go to <a href="report_manager.php?tab=enter" style="font-weight:800">Report Manager → Enter Marks</a> - Photos will show automatically on report cards!
</div>

</div>
</div>
</div>

</div>

<script>
function previewImg(input){
  if(input.files && input.files[0]){
    var reader = new FileReader();
    reader.onload = function(e){ document.getElementById('preview').src = e.target.result; }
    reader.readAsDataURL(input.files[0]);
  }
}
</script>

<?php include 'includes/footer.php';?>