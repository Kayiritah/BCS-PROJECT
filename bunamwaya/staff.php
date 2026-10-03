<?php
require_once 'config.php';
if(!isset($_SESSION['user_id'])){ header("Location: login.php"); exit; }

$conn->query("CREATE TABLE IF NOT EXISTS staff (
  staff_id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100),
  role VARCHAR(50),
  subjects VARCHAR(255),
  employment_type ENUM('Full-time','Part-time','Contract') DEFAULT 'Full-time',
  phone VARCHAR(20),
  email VARCHAR(100),
  address VARCHAR(100),
  qualification VARCHAR(100),
  date_joined DATE,
  status ENUM('Active','Inactive') DEFAULT 'Active',
  photo VARCHAR(255)
)");
$conn->query("CREATE TABLE IF NOT EXISTS staff_assignment (
  assign_id INT AUTO_INCREMENT PRIMARY KEY,
  staff_id INT,
  class_id INT,
  subject VARCHAR(50),
  term VARCHAR(20),
  year YEAR,
  assigned_date DATE
)");
$conn->query("CREATE TABLE IF NOT EXISTS staff_performance (
  perf_id INT AUTO_INCREMENT PRIMARY KEY,
  staff_id INT,
  note TEXT,
  rating INT,
  added_by VARCHAR(100),
  date_added DATETIME DEFAULT CURRENT_TIMESTAMP
)");
$conn->query("CREATE TABLE IF NOT EXISTS staff_attendance (
  att_id INT AUTO_INCREMENT PRIMARY KEY,
  staff_id INT,
  att_date DATE,
  status ENUM('Present','Absent','Late','Leave') DEFAULT 'Present',
  reason VARCHAR(255),
  marked_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

if(isset($_POST['add_staff'])){
  $fn=$conn->real_escape_string($_POST['full_name']); $role=$conn->real_escape_string($_POST['role']);
  $subs=$conn->real_escape_string($_POST['subjects']); $emp=$conn->real_escape_string($_POST['employment_type']);
  $ph=$conn->real_escape_string($_POST['phone']); $em=$conn->real_escape_string($_POST['email']);
  $add=$conn->real_escape_string($_POST['address']); $qual=$conn->real_escape_string($_POST['qualification']);
  $dj=$conn->real_escape_string($_POST['date_joined']);
  $conn->query("INSERT INTO staff (full_name,role,subjects,employment_type,phone,email,address,qualification,date_joined) VALUES ('$fn','$role','$subs','$emp','$ph','$em','$add','$qual','$dj')");
  header("Location: staff.php?msg=added"); exit;
}
if(isset($_GET['delete'])){ $id=intval($_GET['delete']); $conn->query("DELETE FROM staff WHERE staff_id=$id"); header("Location: staff.php"); exit; }
if(isset($_POST['add_assign'])){ $sid=intval($_POST['staff_id']); $cid=intval($_POST['class_id']); $sub=$conn->real_escape_string($_POST['subject']); $term=$conn->real_escape_string($_POST['term']); $yr=intval($_POST['year']); $conn->query("INSERT INTO staff_assignment (staff_id,class_id,subject,term,year,assigned_date) VALUES ($sid,$cid,'$sub','$term',$yr,CURDATE())"); header("Location: staff.php?view=$sid"); exit; }
if(isset($_POST['add_perf'])){ $sid=intval($_POST['staff_id']); $note=$conn->real_escape_string($_POST['note']); $rate=intval($_POST['rating']); $by=$_SESSION['username']??'Admin'; $conn->query("INSERT INTO staff_performance (staff_id,note,rating,added_by) VALUES ($sid,'$note',$rate,'$by')"); header("Location: staff.php?view=$sid"); exit; }
if(isset($_POST['mark_staff_att'])){ $sid=intval($_POST['staff_id']); $date=$_POST['att_date']; $st=$conn->real_escape_string($_POST['status']); $rs=$conn->real_escape_string($_POST['reason']); $conn->query("INSERT INTO staff_attendance (staff_id,att_date,status,reason) VALUES ($sid,'$date','$st','$rs') ON DUPLICATE KEY UPDATE status='$st', reason='$rs'"); 
$conn->query("CREATE UNIQUE INDEX idx_staff_date ON staff_attendance (staff_id, att_date)"); // ensure unique
$conn->query("INSERT INTO staff_attendance (staff_id,att_date,status,reason) VALUES ($sid,'$date','$st','$rs') ON DUPLICATE KEY UPDATE status='$st', reason='$rs'");
header("Location: staff.php?view=$sid"); exit;
}

$classes=$conn->query("SELECT * FROM classes ORDER BY class_id");
$view_id=intval($_GET['view']??0);
include 'includes/header.php'; include 'includes/sidebar.php';
?>
<div class="main-content">
<div class="d-flex justify-content-between align-items-center">
<div><h3 class="fw-bold mb-0" style="color:#0b4d1e">Staff Management</h3><small>Profile, Assignment, Performance & Attendance</small></div>
<button class="btn text-white" data-bs-toggle="modal" data-bs-target="#addStaffModal" style="background:#ff7a00;border-radius:20px;font-weight:700"><i class="fa fa-plus"></i> Add Staff</button>
</div>

<?php if($view_id==0){ ?>
<div class="card p-3 mt-3" style="border-radius:15px">
<div class="table-responsive"><table class="table table-bordered table-hover table-sm"><thead style="background:#0b4d1e;color:#fff"><tr><th>#</th><th>Full Name</th><th>Role</th><th>Subjects</th><th>Type</th><th>Contact</th><th>Status</th><th>Action</th></tr></thead><tbody>
<?php $q=$conn->query("SELECT * FROM staff ORDER BY staff_id DESC"); $i=1; if($q) while($s=$q->fetch_assoc()){ echo "<tr><td>$i</td><td><b>{$s['full_name']}</b><br><small>{$s['qualification']}</small></td><td><span class='badge' style='background:#0b4d1e'>{$s['role']}</span></td><td><small>{$s['subjects']}</small></td><td>{$s['employment_type']}</td><td><small>{$s['phone']}<br>{$s['email']}</small></td><td><span class='badge bg-success'>{$s['status']}</span></td><td><a href='?view={$s['staff_id']}' class='btn btn-sm' style='background:#0b4d1e;color:#fff'><i class='fa fa-eye'></i> View</a> <a href='?delete={$s['staff_id']}' onclick='return confirm(\"Delete?\")' class='btn btn-sm btn-danger'><i class='fa fa-trash'></i></a></td></tr>"; $i++; }?>
</tbody></table></div>
</div>
<?php } else {
  $st=$conn->query("SELECT * FROM staff WHERE staff_id=$view_id")->fetch_assoc();
  if(!$st) echo "<div class='alert alert-danger'>Not found</div>";
  else {
?>
<a href="staff.php" class="btn btn-sm btn-light mb-3" style="border-radius:20px"><i class="fa fa-arrow-left"></i> Back to All Staff</a>
<div class="row g-3">
<div class="col-md-4"><div class="card p-3" style="border-radius:15px;border-top:5px solid #0b4d1e"><h5 class="fw-bold" style="color:#0b4d1e"><?= $st['full_name'] ?></h5><small class="text-muted"><?= $st['role'] ?> | <?= $st['employment_type'] ?></small><hr><p class="small mb-1"><b>Subjects:</b> <?= $st['subjects'] ?></p><p class="small mb-1"><b>Phone:</b> <?= $st['phone'] ?></p><p class="small mb-1"><b>Email:</b> <?= $st['email'] ?></p><p class="small mb-1"><b>Qualification:</b> <?= $st['qualification'] ?></p><p class="small mb-1"><b>Joined:</b> <?= $st['date_joined'] ?></p><p class="small mb-1"><b>Address:</b> <?= $st['address'] ?></p></div>
<div class="card p-3 mt-3" style="border-radius:15px"><h6 class="fw-bold" style="color:#ff7a00">Mark Attendance</h6><form method="POST"><input type="hidden" name="staff_id" value="<?= $view_id ?>"><input type="date" name="att_date" class="form-control form-control-sm mb-2" value="<?= date('Y-m-d') ?>" required><select name="status" class="form-control form-control-sm mb-2"><option>Present</option><option>Absent</option><option>Late</option><option>Leave</option></select><input type="text" name="reason" class="form-control form-control-sm mb-2" placeholder="Reason if absent/late"><button name="mark_staff_att" class="btn btn-sm w-100 text-white" style="background:#0b4d1e">Save Attendance</button></form></div>
</div>
<div class="col-md-8">
<div class="card p-3 mb-3" style="border-radius:15px"><h6 class="fw-bold" style="color:#0b4d1e">Class & Subject Assignment</h6><form method="POST" class="row g-2"><input type="hidden" name="staff_id" value="<?= $view_id ?>"><div class="col-md-3"><select name="class_id" class="form-control form-control-sm" required><option value="">Class</option><?php $classes->data_seek(0); while($c=$classes->fetch_assoc()) echo "<option value='{$c['class_id']}'>{$c['class_name']}</option>"; ?></select></div><div class="col-md-3"><input type="text" name="subject" class="form-control form-control-sm" placeholder="Subject" required></div><div class="col-md-2"><select name="term" class="form-control form-control-sm"><option>Term 1</option><option>Term 2</option><option>Term 3</option></select></div><div class="col-md-2"><input type="number" name="year" class="form-control form-control-sm" value="<?= date('Y') ?>"></div><div class="col-md-2"><button name="add_assign" class="btn btn-sm w-100 text-white" style="background:#ff7a00">Assign</button></div></form><div class="table-responsive mt-3"><table class="table table-sm table-bordered"><thead style="background:#e8f5e9"><tr><th>Class</th><th>Subject</th><th>Term</th><th>Year</th></tr></thead><tbody><?php $qa=$conn->query("SELECT a.*, c.class_name FROM staff_assignment a LEFT JOIN classes c ON a.class_id=c.class_id WHERE a.staff_id=$view_id ORDER BY a.assign_id DESC"); if($qa) while($a=$qa->fetch_assoc()) echo "<tr><td>{$a['class_name']}</td><td>{$a['subject']}</td><td>{$a['term']}</td><td>{$a['year']}</td></tr>"; ?></tbody></table></div></div>
<div class="card p-3 mb-3" style="border-radius:15px"><h6 class="fw-bold" style="color:#ff7a00">Performance Notes</h6><form method="POST" class="row g-2"><input type="hidden" name="staff_id" value="<?= $view_id ?>"><div class="col-md-2"><select name="rating" class="form-control form-control-sm"><option value="5">5 - Excellent</option><option value="4">4 - Good</option><option value="3">3 - Average</option><option value="2">2 - Poor</option><option value="1">1 - Very Poor</option></select></div><div class="col-md-8"><input type="text" name="note" class="form-control form-control-sm" placeholder="Performance note..." required></div><div class="col-md-2"><button name="add_perf" class="btn btn-sm w-100 text-white" style="background:#0b4d1e">Add</button></div></form><div class="mt-3"><?php $qp=$conn->query("SELECT * FROM staff_performance WHERE staff_id=$view_id ORDER BY perf_id DESC LIMIT 10"); if($qp) while($p=$qp->fetch_assoc()){ echo "<div class='border-bottom py-2'><small><b>Rating: {$p['rating']}/5</b> - {$p['note']} <span class='text-muted'>by {$p['added_by']} on ".date('d M Y',strtotime($p['date_added']))."</span></small></div>"; }?></div></div>
<div class="card p-3" style="border-radius:15px"><h6 class="fw-bold" style="color:#0b4d1e">Attendance History (This Month)</h6><div class="table-responsive"><table class="table table-sm table-bordered"><thead style="background:#0b4d1e;color:#fff"><tr><th>Date</th><th>Status</th><th>Reason</th></tr></thead><tbody><?php $qat=$conn->query("SELECT * FROM staff_attendance WHERE staff_id=$view_id AND MONTH(att_date)=MONTH(CURDATE()) ORDER BY att_date DESC"); if($qat) while($a=$qat->fetch_assoc()){ $col=$a['status']=='Present'?'#0b4d1e':($a['status']=='Absent'?'#d00':'#ff7a00'); echo "<tr><td>{$a['att_date']}</td><td><span class='badge' style='background:$col'>{$a['status']}</span></td><td>{$a['reason']}</td></tr>"; }?></tbody></table></div></div>
</div>
</div>
<?php }} ?>
</div>

<div class="modal fade" id="addStaffModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content" style="border-radius:15px"><form method="POST"><div class="modal-header" style="background:#0b4d1e;color:#fff;border-radius:15px 15px 0 0"><h5 class="modal-title">Add Staff</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body row g-2"><div class="col-md-6"><label class="small fw-bold">Full Name *</label><input type="text" name="full_name" class="form-control" required></div><div class="col-md-3"><label class="small fw-bold">Role *</label><select name="role" class="form-control"><option>Teacher</option><option>Head Teacher</option><option>Deputy</option><option>Bursar</option><option>Secretary</option><option>Matron</option><option>Security</option><option>Cleaner</option><option>Cook</option></select></div><div class="col-md-3"><label class="small fw-bold">Employment Type</label><select name="employment_type" class="form-control"><option>Full-time</option><option>Part-time</option><option>Contract</option></select></div><div class="col-md-6"><label class="small fw-bold">Subjects Taught</label><input type="text" name="subjects" class="form-control" placeholder="English, Math"></div><div class="col-md-3"><label class="small fw-bold">Phone</label><input type="text" name="phone" class="form-control" placeholder="0700..."></div><div class="col-md-3"><label class="small fw-bold">Email</label><input type="email" name="email" class="form-control"></div><div class="col-md-4"><label class="small fw-bold">Qualification</label><input type="text" name="qualification" class="form-control" placeholder="Diploma, Degree"></div><div class="col-md-4"><label class="small fw-bold">Address</label><input type="text" name="address" class="form-control"></div><div class="col-md-4"><label class="small fw-bold">Date Joined</label><input type="date" name="date_joined" class="form-control" value="<?= date('Y-m-d') ?>"></div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" name="add_staff" class="btn text-white" style="background:#ff7a00">Save Staff</button></div></form></div></div></div>

<?php include 'includes/footer.php'; ?>