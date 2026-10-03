<?php
require_once 'config.php';
if(!isset($_SESSION['user_id'])){ header("Location: login.php"); exit; }
$classes=$conn->query("SELECT * FROM classes ORDER BY class_id"); $sel_c=intval($_GET['class_id']??0); $sel_t=$_GET['term']??'Term 1'; $sel_y=$_GET['year']??date('Y');
function grade($m){ if($m>=80)return'D1';if($m>=70)return'D2';if($m>=60)return'C3';if($m>=50)return'C4';if($m>=40)return'C5';if($m>=35)return'C6';return'F9'; }
include 'includes/header.php'; include 'includes/sidebar.php';
?>
<div class="main-content">
<h3 class="fw-bold" style="color:#0b4d1e">CA Class Report + Ranking</h3>
<div class="card p-3 mt-3" style="border-radius:15px"><form method="GET" class="row g-2"><div class="col-md-3"><select name="class_id" class="form-control" required><option value="">Select Class</option><?php $classes->data_seek(0); while($c=$classes->fetch_assoc()){ $sl=$sel_c==$c['class_id']?'selected':''; echo "<option value='{$c['class_id']}' $sl>{$c['class_name']}</option>"; }?></select></div><div class="col-md-2"><select name="term" class="form-control"><option <?= $sel_t=='Term 1'?'selected':''?>>Term 1</option><option <?= $sel_t=='Term 2'?'selected':''?>>Term 2</option><option <?= $sel_t=='Term 3'?'selected':''?>>Term 3</option></select></div><div class="col-md-2"><input type="number" name="year" class="form-control" value="<?= $sel_y?>"></div><div class="col-md-5"><button class="btn text-white w-100" style="background:#0b4d1e">Generate Report with Ranking</button></div></form></div>
<?php if($sel_c){ $students=$conn->query("SELECT * FROM students WHERE class_id=$sel_c ORDER BY first_name ASC"); $subs_q=$conn->query("SELECT subject_name FROM subjects"); $subs=[]; if($subs_q) while($s=$subs_q->fetch_assoc()) $subs[]=$s['subject_name']; else $subs=['English','Mathematics','Science','SST'];
$ranking=[]; $qr=$conn->query("SELECT s.student_id, AVG(m.marks) as av FROM students s LEFT JOIN marks m ON s.student_id=m.student_id AND m.term='$sel_t' AND m.year=$sel_y WHERE s.class_id=$sel_c GROUP BY s.student_id ORDER BY av DESC"); if($qr){ $p=1; while($r=$qr->fetch_assoc()) $ranking[$r['student_id']]=$p++; }
?>
<div class="card p-3 mt-3" style="border-radius:15px"><div class="table-responsive"><table class="table table-bordered table-sm"><thead style="background:#0b4d1e;color:#fff"><tr><th>#</th><th>Adm</th><th>Name</th><?php foreach($subs as $sb) echo "<th>$sb<br><small>Av</small></th>";?><th>Total</th><th>Average</th><th>Div</th><th>Pos</th></tr></thead><tbody>
<?php $i=1; while($st=$students->fetch_assoc()){ $sid=$st['student_id']; $tot=0; $cnt=0; echo "<tr><td>$i</td><td><small>{$st['admission_no']}</small></td><td><b>{$st['first_name']} {$st['last_name']}</b></td>"; foreach($subs as $sb){ $q=$conn->query("SELECT AVG(marks) as av, COUNT(*) as c FROM marks WHERE student_id=$sid AND subject='$sb' AND term='$sel_t' AND year=$sel_y"); $rw=$q->fetch_assoc(); $av=$rw['av']?round($rw['av'],1):0; $cc=$rw['c']; if($cc>0){ $tot+=$av; $cnt++; } echo "<td class='text-center'>".($cc>0?$av:'-')."<br><small>$cc/6</small></td>"; } $ov=$cnt>0?round($tot/$cnt,1):0; $pos=$ranking[$sid]??'-'; echo "<td class='text-center fw-bold'>".round($tot,1)."</td><td class='text-center fw-bold' style='background:#fff8f0'>$ov%</td><td><span class='badge' style='background:".($ov>=50?'#0b4d1e':'#d00')."'>".grade($ov)."</span></td><td class='text-center fw-bold'>$pos</td></tr>"; $i++; }?>
</tbody></table></div></div>
<?php }?>
</div>
<?php include 'includes/footer.php';?>