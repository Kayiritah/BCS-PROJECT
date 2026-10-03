<?php
require_once 'config.php';
if(!isset($_SESSION['user_id'])){ header("Location: login.php"); exit; }

// AUTO CREATE TABLES IF NOT EXIST
$conn->query("CREATE TABLE IF NOT EXISTS classes (class_id INT AUTO_INCREMENT PRIMARY KEY, class_name VARCHAR(50), class_teacher VARCHAR(100))");
$conn->query("CREATE TABLE IF NOT EXISTS students (student_id INT AUTO_INCREMENT PRIMARY KEY, admission_no VARCHAR(30), first_name VARCHAR(100), last_name VARCHAR(100), class_id INT, sex ENUM('Male','Female'), parent_name VARCHAR(100), parent_phone VARCHAR(20), dob DATE, village VARCHAR(100), status VARCHAR(20) DEFAULT 'Active', date_added DATETIME DEFAULT CURRENT_TIMESTAMP)");
$conn->query("CREATE TABLE IF NOT EXISTS staff (staff_id INT AUTO_INCREMENT PRIMARY KEY, full_name VARCHAR(100), role VARCHAR(50), subjects VARCHAR(255), employment_type VARCHAR(20), phone VARCHAR(20), email VARCHAR(100), address VARCHAR(100), qualification VARCHAR(100), date_joined DATE, status VARCHAR(20) DEFAULT 'Active')");
$conn->query("CREATE TABLE IF NOT EXISTS attendance (att_id INT AUTO_INCREMENT PRIMARY KEY, student_id INT, class_id INT, att_date DATE, status VARCHAR(20), reason VARCHAR(255), term VARCHAR(20), year YEAR, marked_by VARCHAR(100), marked_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
$conn->query("CREATE TABLE IF NOT EXISTS marks (mark_id INT AUTO_INCREMENT PRIMARY KEY, student_id INT, class_id INT, subject VARCHAR(50), term VARCHAR(20), ca_no VARCHAR(10), marks INT, year YEAR, date_recorded DATETIME DEFAULT CURRENT_TIMESTAMP)");
$conn->query("CREATE TABLE IF NOT EXISTS notifications (id INT AUTO_INCREMENT PRIMARY KEY, student_id INT, parent_phone VARCHAR(20), message TEXT, type VARCHAR(20), status VARCHAR(20), sent_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
$conn->query("CREATE TABLE IF NOT EXISTS subjects (subject_id INT AUTO_INCREMENT PRIMARY KEY, subject_name VARCHAR(50))");

// Insert default classes if none
$q=$conn->query("SELECT COUNT(*) as c FROM classes"); if($q){ $r=$q->fetch_assoc(); if($r['c']==0){ $conn->query("INSERT INTO classes (class_name,class_teacher) VALUES ('P.4','Not set'),('P.5','Not set'),('P.6','Not set'),('P.7','Not set')"); } }
$q=$conn->query("SELECT COUNT(*) as c FROM subjects"); if($q){ $r=$q->fetch_assoc(); if($r['c']==0){ $conn->query("INSERT INTO subjects (subject_name) VALUES ('English'),('Mathematics'),('Science'),('SST')"); } }

// SAFE COUNT FUNCTION
function safeCount($conn, $sql){
  $q=$conn->query($sql);
  if(!$q) return 0;
  $r=$q->fetch_assoc();
  return $r['c'] ?? 0;
}

$total_students = safeCount($conn, "SELECT COUNT(*) as c FROM students");
$total_boys = safeCount($conn, "SELECT COUNT(*) as c FROM students WHERE sex='Male'");
$total_girls = safeCount($conn, "SELECT COUNT(*) as c FROM students WHERE sex='Female'");
$total_staff = safeCount($conn, "SELECT COUNT(*) as c FROM staff");
$total_classes = safeCount($conn, "SELECT COUNT(*) as c FROM classes");
$today_att_present = safeCount($conn, "SELECT COUNT(*) as c FROM attendance WHERE att_date=CURDATE() AND status='Present'");
$today_att_absent = safeCount($conn, "SELECT COUNT(*) as c FROM attendance WHERE att_date=CURDATE() AND status='Absent'");
$total_ca = safeCount($conn, "SELECT COUNT(*) as c FROM marks WHERE year=YEAR(CURDATE())");
$sms_today = safeCount($conn, "SELECT COUNT(*) as c FROM notifications WHERE DATE(sent_at)=CURDATE()");

include 'includes/header.php';
include 'includes/sidebar.php';
?>
<div class="main-content">
<div class="d-flex justify-content-between align-items-center">
<div>
<h3 class="fw-bold mb-0" style="color:#0b4d1e">Bunamwaya Central P/S Dashboard</h3>
<small class="text-muted">P.4 - P.7 | Knowledge is Power | <?= date('l, d M Y') ?></small>
</div>
<div class="d-flex gap-2">
<a href="students.php" class="btn btn-sm text-white" style="background:#0b4d1e;border-radius:20px"><i class="fa fa-user-plus"></i> Admit Pupil</a>
<a href="marks.php" class="btn btn-sm text-white" style="background:#ff7a00;border-radius:20px"><i class="fa fa-edit"></i> Enter CA</a>
</div>
</div>

<div class="row g-3 mt-3">
<div class="col-md-3"><div class="card p-3" style="border-radius:15px;border-left:5px solid #0b4d1e"><small class="text-muted">Total Pupils</small><h3 class="fw-bold mb-0" style="color:#0b4d1e"><?= $total_students ?></h3><small style="color:#ff7a00">B: <?= $total_boys ?> | G: <?= $total_girls ?></small><br><a href="students_records.php" class="small text-decoration-none" style="color:#0b4d1e">View Master <i class="fa fa-arrow-right"></i></a></div></div>
<div class="col-md-3"><div class="card p-3" style="border-radius:15px;border-left:5px solid #ff7a00"><small class="text-muted">Staff</small><h3 class="fw-bold mb-0" style="color:#ff7a00"><?= $total_staff ?></h3><small>Teachers & Support</small><br><a href="staff.php" class="small text-decoration-none" style="color:#ff7a00">Manage Staff <i class="fa fa-arrow-right"></i></a></div></div>
<div class="col-md-3"><div class="card p-3" style="border-radius:15px;border-left:5px solid #0b4d1e"><small class="text-muted">Today Attendance</small><h3 class="fw-bold mb-0" style="color:#0b4d1e"><?= $today_att_present ?> <small style="font-size:14px;color:#d00">Abs: <?= $today_att_absent ?></small></h3><small><?= date('d M Y') ?></small><br><a href="attendance.php" class="small text-decoration-none" style="color:#0b4d1e">Mark Attendance <i class="fa fa-arrow-right"></i></a></div></div>
<div class="col-md-3"><div class="card p-3" style="border-radius:15px;border-left:5px solid #25D366"><small class="text-muted">CA Records / SMS Today</small><h3 class="fw-bold mb-0" style="color:#25D366"><?= $total_ca ?> / <?= $sms_today ?></h3><small>Year <?= date('Y') ?></small><br><a href="notifications.php" class="small text-decoration-none" style="color:#25D366">SMS Log <i class="fa fa-arrow-right"></i></a></div></div>
</div>

<div class="row g-3 mt-3">
<div class="col-md-8">
<div class="card p-3" style="border-radius:15px"><h6 class="fw-bold" style="color:#0b4d1e"><i class="fa fa-school" style="color:#ff7a00"></i> Pupils by Class - Boys / Girls / Total</h6>
<div class="table-responsive mt-3"><table class="table table-bordered table-sm"><thead style="background:#0b4d1e;color:#fff"><tr><th>Class</th><th>Teacher</th><th>Boys</th><th>Girls</th><th>Total</th><th>Action</th></tr></thead><tbody>
<?php 
$q=$conn->query("SELECT * FROM classes ORDER BY class_id ASC"); 
if($q){
 while($c=$q->fetch_assoc()){ 
   $cid=intval($c['class_id']); 
   $b=safeCount($conn, "SELECT COUNT(*) as c FROM students WHERE class_id=$cid AND sex='Male'");
   $g=safeCount($conn, "SELECT COUNT(*) as c FROM students WHERE class_id=$cid AND sex='Female'");
   $t=$b+$g; 
   echo "<tr><td><b>{$c['class_name']}</b></td><td><small>{$c['class_teacher']}</small></td><td><span class='badge' style='background:#0b4d1e'>$b</span></td><td><span class='badge' style='background:#ff7a00'>$g</span></td><td><b>$t</b></td><td><a href='students_records.php?class_id=$cid' class='btn btn-sm' style='background:#e8f5e9;color:#0b4d1e;border-radius:20px'>View</a> <a href='ca_report.php?class_id=$cid' class='btn btn-sm' style='background:#0b4d1e;color:#fff;border-radius:20px'>CA</a></td></tr>"; 
 }
}
?>
</tbody></table></div>
</div>
<div class="card p-3 mt-3" style="border-radius:15px"><h6 class="fw-bold" style="color:#0b4d1e">Recent CA Entries</h6><div class="table-responsive"><table class="table table-sm table-bordered"><thead style="background:#e8f5e9"><tr><th>Date</th><th>Pupil</th><th>Subject</th><th>CA</th><th>Marks</th></tr></thead><tbody><?php $qr=$conn->query("SELECT m.*, s.first_name, s.last_name FROM marks m LEFT JOIN students s ON m.student_id=s.student_id ORDER BY m.mark_id DESC LIMIT 8"); if($qr) while($r=$qr->fetch_assoc()){ echo "<tr><td><small>".date('d/m',strtotime($r['date_recorded']??'now'))."</small></td><td><small>{$r['first_name']} {$r['last_name']}</small></td><td><small>{$r['subject']}</small></td><td><small>{$r['ca_no']}</small></td><td><span class='badge' style='background:".($r['marks']>=50?'#0b4d1e':'#d00')."'>{$r['marks']}%</span></td></tr>"; }?></tbody></table></div></div>
</div>
<div class="col-md-4">
<div class="card p-3" style="border-radius:15px;border-top:4px solid #ff7a00"><h6 class="fw-bold" style="color:#ff7a00"><i class="fa fa-bolt"></i> Quick Actions</h6>
<div class="d-grid gap-2 mt-3">
<a href="students.php" class="btn text-white" style="background:#0b4d1e;border-radius:10px"><i class="fa fa-user-plus"></i> Admit New Pupil</a>
<a href="marks.php" class="btn text-white" style="background:#ff7a00;border-radius:10px"><i class="fa fa-edit"></i> Enter CA 1-6 Marks</a>
<a href="attendance.php" class="btn btn-light" style="border-radius:10px;border:1px solid #0b4d1e;color:#0b4d1e"><i class="fa fa-calendar-check"></i> Mark Daily Attendance</a>
<a href="pupil_report.php" class="btn btn-light" style="border-radius:10px;border:1px solid #ff7a00;color:#ff7a00"><i class="fa fa-file-alt"></i> Generate Report Card</a>
</div>
</div>
<div class="card p-3 mt-3" style="border-radius:15px;background:#0b4d1e;color:#fff"><h6 class="fw-bold" style="color:#ff7a00">Bunamwaya Central</h6><small>Knowledge is Power<br>P.4 - P.7 Continuous Assessment<br>6 CAs per Term<br>Auto SMS to Parents</small></div>
</div>
</div>
</div>
<?php include 'includes/footer.php'; ?>