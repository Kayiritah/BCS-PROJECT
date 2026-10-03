<?php
require_once 'config.php';
if(!isset($_SESSION['user_id'])){ header("Location: login.php"); exit; }


$badges = ["badge.png","badge.jpeg","badge.jpg","images/logo.jpeg","images/badge.png","images/logo.png"];
$school_badge = "badge.png";
foreach($badges as $b){ if(file_exists($b)){ $school_badge=$b; break; } }

$conn->query("CREATE TABLE IF NOT EXISTS notices (
  notice_id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,
  date_posted DATETIME DEFAULT CURRENT_TIMESTAMP
)");

if(isset($_POST['add_notice'])){
  $t = $conn->real_escape_string($_POST['title']);
  $m = $conn->real_escape_string($_POST['message']);
  $conn->query("INSERT INTO notices (title, message, date_posted) VALUES ('$t','$m', NOW())");
  header("Location: dashboard.php"); exit;
}
if(isset($_GET['del_notice'])){
  $conn->query("DELETE FROM notices WHERE notice_id=".intval($_GET['del_notice']));
  header("Location: dashboard.php"); exit;
}

$total_students = 0; $total_classes = 0; $total_teachers = 0; $total_fees = 0;
$total_boys = 0; $total_girls = 0;

$q = $conn->query("SELECT COUNT(*) as c FROM students"); if($q) $total_students = $q->fetch_assoc()['c'];
$q = $conn->query("SELECT COUNT(*) as c FROM classes"); if($q) $total_classes = $q->fetch_assoc()['c'];
$q = $conn->query("SELECT COUNT(*) as c FROM teachers"); if($q) $total_teachers = $q->fetch_assoc()['c'];
$q = $conn->query("SELECT SUM(amount_paid) as s FROM fees"); if($q){ $r=$q->fetch_assoc(); $total_fees = $r['s'] ?? 0; }
$q = $conn->query("SELECT COUNT(*) as c FROM students WHERE sex='Male'"); if($q) $total_boys = $q->fetch_assoc()['c'];
$q = $conn->query("SELECT COUNT(*) as c FROM students WHERE sex='Female'"); if($q) $total_girls = $q->fetch_assoc()['c'];

include 'includes/header.php';
include 'includes/sidebar.php';
?>
<style>
.badge-box-dash{width:72px;height:72px;border-radius:16px;padding:0;overflow:hidden;border:3px solid #ff7a00;box-shadow:0 3px 8px rgba(0,0,0,0.2);background:#0b4d1e;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.badge-box-dash img{width:100%;height:100%;object-fit:cover}
</style>
<div class="main-content">
<div class="d-flex justify-content-between align-items-center mb-2">
<div class="d-flex align-items-center gap-3">

<div class="badge-box-dash"><img src="<?=$school_badge?>?v=<?=time()?>" alt="BU Badge"></div>
<div>
<h3 class="fw-bold mb-0" style="color:#0b4d1e;line-height:1.1">BUNAMWAYA CENTRAL<br>PARENTS SCHOOL</h3>
<small class="text-muted" style="font-size:12px;">P.O BOX 9270 Kampala | <b style="color:#0b4d1e;">KNOWLEDGE IS POWER</b></small>
</div>
</div>
<div><small class="badge" style="background:#0b4d1e"><?= date('d M Y') ?></small></div>
</div>

<div class="row g-3 mt-3">
<div class="col-md-3"><div class="card p-3 shadow-sm" style="border-radius:15px;border-left:5px solid #0b4d1e"><div class="d-flex justify-content-between"><div><h3 class="fw-bold" style="color:#0b4d1e"><?= $total_students ?></h3><small>Total Pupils</small></div><div class="p-3 rounded-circle" style="background:#e8f5e9"><i class="fa fa-users" style="color:#0b4d1e"></i></div></div><small class="text-muted"><?= $total_boys ?> Boys, <?= $total_girls ?> Girls</small></div></div>
<div class="col-md-3"><div class="card p-3 shadow-sm" style="border-radius:15px;border-left:5px solid #ff7a00"><div class="d-flex justify-content-between"><div><h3 class="fw-bold" style="color:#ff7a00"><?= $total_classes ?></h3><small>Total Classes</small></div><div class="p-3 rounded-circle" style="background:#fff3e0"><i class="fa fa-door-open" style="color:#ff7a00"></i></div></div><small class="text-muted">Nursery to P7</small></div></div>
<div class="col-md-3"><div class="card p-3 shadow-sm" style="border-radius:15px;border-left:5px solid #0b4d1e"><div class="d-flex justify-content-between"><div><h3 class="fw-bold" style="color:#0b4d1e"><?= $total_teachers ?></h3><small>Teachers</small></div><div class="p-3 rounded-circle" style="background:#e8f5e9"><i class="fa fa-chalkboard-teacher" style="color:#0b4d1e"></i></div></div><small class="text-muted">Staff members</small></div></div>
<div class="col-md-3"><div class="card p-3 shadow-sm" style="border-radius:15px;border-left:5px solid #ff7a00"><div class="d-flex justify-content-between"><div><h3 class="fw-bold" style="color:#ff7a00">UGX <?= number_format($total_fees) ?></h3><small>Fees Collected</small></div><div class="p-3 rounded-circle" style="background:#fff3e0"><i class="fa fa-coins" style="color:#ff7a00"></i></div></div><small class="text-muted">This term</small></div></div>
</div>

<div class="row g-3 mt-4">
<div class="col-md-8">
<div class="card p-3 shadow-sm" style="border-radius:15px; border-top:4px solid #ff7a00; min-height:380px">
<div class="d-flex justify-content-between align-items-center mb-3">
<h5 class="fw-bold mb-0" style="color:#0b4d1e"><i class="fa fa-bullhorn" style="color:#ff7a00"></i> Notice Board</h5>
<button class="btn btn-sm text-white" style="background:#0b4d1e;border-radius:20px" data-bs-toggle="modal" data-bs-target="#noticeModal"><i class="fa fa-plus"></i> Add Notice</button>
</div>
<?php
$notices = $conn->query("SELECT * FROM notices ORDER BY notice_id DESC LIMIT 10");
if($notices && $notices->num_rows > 0){
  while($n = $notices->fetch_assoc()){
$d = isset($n['date_posted']) && $n['date_posted'] ? date('d M Y', strtotime($n['date_posted'])) : date('d M Y');
echo "<div class='mb-2 p-3' style='background:#fff8f0;border-left:4px solid #ff7a00;border-radius:10px'>
    <div class='d-flex justify-content-between'><b style='color:#0b4d1e'>{$n['title']}</b><small class='text-muted'>$d</small></div>
    <div class='mt-1'><small>{$n['message']}</small></div>
    <div class='mt-2'><a href='?del_notice={$n['notice_id']}' onclick='return confirm(\"Delete this notice?\")' style='font-size:11px;color:#d00'><i class='fa fa-trash'></i> Delete</a></div>
    </div>";
  }
} else {
  echo "<div class='text-center py-5 text-muted'><i class='fa fa-clipboard fa-3x mb-3' style='color:#ddd'></i><br>No notices yet<br><small>Post fees deadlines, meetings, events</small></div>";
}
?>
</div>
</div>

<div class="col-md-4">
<div class="card p-3 shadow-sm" style="border-radius:15px; border-top:4px solid #0b4d1e">
<h6 class="fw-bold" style="color:#0b4d1e"><i class="fa fa-bolt" style="color:#ff7a00"></i> Quick Actions</h6>
<div class="d-grid gap-2 mt-3">
<a href="students.php" class="btn text-white" style="background:#0b4d1e;border-radius:10px"><i class="fa fa-user-plus"></i> Admit Pupil</a>
<a href="fees.php" class="btn text-white" style="background:#ff7a00;border-radius:10px"><i class="fa fa-money-bill-wave"></i> Fees Collection</a>
<a href="marks.php" class="btn text-white" style="background:#0b4d1e;border-radius:10px"><i class="fa fa-marker"></i> Enter Marks</a>
<a href="attendance.php" class="btn btn-outline-dark" style="border-radius:10px"><i class="fa fa-calendar-check"></i> Attendance</a>
</div>
<div class="mt-4 p-3 text-center" style="background:#0b4d1e;border-radius:12px">
<img src="<?=$school_badge?>?v=<?=time()?>" style="width:100%;height:130px;object-fit:contain;border-radius:10px;background:#0b4d1e;border:2px solid #ff7a00">
<h6 class="fw-bold mt-2 mb-0" style="color:#fff">BUNAMWAYA CENTRAL</h6><small style="color:#ffcc80;">KNOWLEDGE IS POWER</small><br><small class="text-white-50">P.O.BOX 9270 Kampala</small>
</div>
</div>
</div>
</div>
</div>

<div class="modal fade" id="noticeModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content" style="border-radius:15px">
<div class="modal-header text-white" style="background:linear-gradient(135deg,#0b4d1e,#ff7a00)"><h5 class="modal-title fw-bold"><i class="fa fa-bullhorn"></i> New Notice</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
<form method="POST">
<div class="modal-body">
<label class="small fw-bold">Title *</label><input type="text" name="title" class="form-control mb-3" placeholder="e.g. PTA Meeting on 10th June" required>
<label class="small fw-bold">Message *</label><textarea name="message" class="form-control" rows="4" placeholder="Write full notice..." required></textarea>
</div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="add_notice" class="btn text-white" style="background:#0b4d1e;font-weight:700">Post Notice</button></div>
</form>
</div></div></div>

<?php include 'includes/footer.php'; ?>