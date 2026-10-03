<?php
require_once 'config.php';
if(!isset($_SESSION['user_id'])){ header("Location: login.php"); exit; }
include 'includes/header.php'; include 'includes/sidebar.php';
?>
<div class="main-content">
<h3 class="fw-bold" style="color:#0b4d1e">SMS / WhatsApp Log</h3>
<div class="card p-3 mt-3" style="border-radius:15px">
<div class="table-responsive"><table class="table table-bordered table-sm"><thead style="background:#0b4d1e;color:#fff"><tr><th>Date</th><th>Pupil</th><th>Phone</th><th>Message</th><th>Type</th><th>Action</th></tr></thead><tbody>
<?php $q=$conn->query("SELECT n.*,s.first_name,s.last_name FROM notifications n LEFT JOIN students s ON n.student_id=s.student_id ORDER BY n.id DESC LIMIT 200"); if($q) while($r=$q->fetch_assoc()){ $wa="https://wa.me/".preg_replace('/[^0-9]/','',$r['parent_phone'])."?text=".urlencode($r['message']); echo "<tr><td><small>{$r['sent_at']}</small></td><td>{$r['first_name']} {$r['last_name']}</td><td>{$r['parent_phone']}</td><td><small>{$r['message']}</small></td><td><span class='badge' style='background:".($r['type']=='sms'?'#0b4d1e':'#25D366')."'>{$r['type']}</span></td><td><a href='$wa' target='_blank' class='btn btn-sm' style='background:#25D366;color:#fff'>WhatsApp</a></td></tr>"; }?>
</tbody></table></div>
</div></div>
<?php include 'includes/footer.php';?>