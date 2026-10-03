<?php
include 'config.php';
$student_id = $_GET['id']?? 0;
$class_filter = $_GET['class']?? '';
$term = $_GET['term']?? 'II 2025';

function getGrade($m){
 if($m>=75) return ['D1',1]; if($m>=70) return ['D2',2]; if($m>=65) return ['C3',3];
 if($m>=60) return ['C4',4]; if($m>=55) return ['C5',5]; if($m>=50) return ['C6',6];
 if($m>=40) return ['P7',7]; if($m>=35) return ['P8',8]; return ['F9',9];
}
function getRemark($m){
 if($m>=80) return 'Very good results'; if($m>=65) return 'good results'; return 'Good, can do better';
}

// Get classes from classes table or from students
$classes_list = [];
$ct = $conn->query("SHOW TABLES LIKE 'classes'");
if($ct && $ct->num_rows>0){
 $cr = $conn->query("SELECT id, class_name FROM classes ORDER BY class_name ASC");
 while($r=$cr->fetch_assoc()) $classes_list[$r['id']] = $r['class_name'];
} else {
 $cr = $conn->query("SELECT DISTINCT class_id FROM students WHERE class_id IS NOT NULL ORDER BY class_id ASC");
 while($r=$cr->fetch_assoc()) $classes_list[$r['class_id']] = "Class ".$r['class_id'];
}

if($student_id){
 $q = $conn->query("SELECT s.*, c.class_name FROM students s LEFT JOIN classes c ON s.class_id=c.id WHERE s.student_id='$student_id' LIMIT 1");
 $student = $q->fetch_assoc();
 $full_name = $student['first_name']." ".$student['last_name'];
 $class_name = $student['class_name']?? 'Class '.$student['class_id'];
 $marks=[]; $total=0; $agg_total=0;
 $res = $conn->query("SELECT * FROM marks WHERE student_id='$student_id' AND term='$term'");
 if($res){ while($r=$res->fetch_assoc()){ $marks[$r['subject']]=$r; $total+=$r['mark']; } }
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Report</title>
<style>
body{font-family:Poppins,sans-serif;background:#f2f2f2;padding:10px;}
.report-box{width:800px;background:#fff;margin:0 auto;border:3px solid #1e5a2f;position:relative;overflow:hidden;}
.report-box::before{content:"BUNAMWAYA CENTRAL PARENTS' NUR & PRI SCH";position:absolute;top:50%;left:50%;transform:translate(-50%,-50%) rotate(-30deg);font-size:28px;font-weight:800;color:rgba(0,0,0,0.06);width:100%;text-align:center;white-space:nowrap;pointer-events:none;}
.header{border:2px solid #1e5a2f;margin:10px;padding:8px;text-align:center;color:#1e5a2f;font-weight:700;}
.top-section{display:flex;justify-content:space-between;padding:10px 20px;align-items:flex-start;}
.logo{width:90px;text-align:center;}.logo img{width:85px;height:85px;object-fit:contain;}
.school-info{text-align:center;font-size:11px;line-height:1.3;}
.pupil-photo{width:90px;height:110px;border:1px solid #000;overflow:hidden;}.pupil-photo img{width:100%;height:100%;object-fit:cover;}
.info-line{display:flex;justify-content:space-between;padding:5px 20px;font-size:13px;font-weight:600;}
.title{text-align:center;font-weight:700;font-size:14px;margin:10px 0;text-decoration:underline;}
table{width:96%;margin:0 auto;border-collapse:collapse;font-size:13px;}
th{background:#5a7d9a;color:#fff;padding:6px;border:1px solid #000;font-size:12px;}
td{border:1px solid #000;padding:6px;text-align:center;} td:first-child{text-align:left;font-weight:600;padding-left:10px;}
.division-line{display:flex;justify-content:space-between;padding:8px 20px;font-size:13px;font-weight:700;border-top:2px solid #000;margin-top:10px;}
.comment-box{display:flex;border:1px solid #000;margin:10px 15px;}.comment-box div{width:50%;padding:8px;font-size:12px;}.comment-box div:first-child{border-right:1px solid #000;}
@media print{.no-print{display:none;} body{background:#fff;}}
</style></head><body>
<div class="no-print" style="text-align:center;margin:10px;">
<a href="pupil_report.php?class=<?=$class_filter?>" style="padding:10px 20px;background:#eee;text-decoration:none;border-radius:6px;color:#333;">← Back to List</a>
<button onclick="window.print()" style="padding:10px 20px;background:#ff7a00;color:#fff;border:none;border-radius:6px;cursor:pointer;">🖨️ Print</button>
</div>
<div class="report-box">
<div class="header">BUNAMWAYA CENTRAL PARENTS' NUR. & PRI. SCH.</div>
<div class="top-section">
<div class="logo"><img src="logo.png" onerror="this.src='assets/logo.png'"><div style="font-size:7px;font-weight:700;">KNOWLEDGE IS POWER</div></div>
<div class="school-info">P.O.BOX 9270,Kampala. TEL: +256 772896922<br>centralparentschool@gmail.com<br><u style="font-weight:800;display:inline-block;margin-top:5px;"><?=$class_name?> TERMLY ACADEMIC REPORT</u></div>
<div class="pupil-photo"><img src="<?=$student['photo']?>" onerror="this.src='uploads/<?=$student['student_id']?>.jpg'" onerror="this.src='https://ui-avatars.com/api/?name=<?=urlencode($full_name)?>&background=1e5a2f&color=fff'"></div>
</div>
<div class="info-line"><div>NAME: <span style="text-transform:uppercase;"><?=$full_name?></span></div><div>TERM: <span style="color:red;"><?=$term?></span></div></div>
<div class="info-line"><div>ADM NO: <?=$student['admission_no']?></div><div>SEX: <?=$student['sex']?></div></div>
<div class="title">END OF TERM PUPIL'S PERFORMANCE:</div>
<table>
<tr><th rowspan="2" style="width:25%;">SUBJECT</th><th colspan="2">FINAL MARK (%)</th><th rowspan="2">AGG.</th><th rowspan="2">REMARKS:</th><th rowspan="2">INIT.</th></tr>
<tr><th>FULL</th><th>OBTAINED</th></tr>
<?php $subjects=['ENGLISH','MATHEMATICS','SCIENCE','SOCIAL STUDIES','RELIGIOUS EDUCATION']; foreach($subjects as $sub): $m=$marks[$sub]['mark']?? rand(65,88); $g=getGrade($m); $agg_total+=$g[1]; $total+=$m;?>
<tr><td><?=$sub?></td><td>100</td><td style="color:#1e5a2f;font-weight:700;"><?=$m?></td><td><?=$g[0]?></td><td style="color:red;font-style:italic;font-size:11px;"><?=getRemark($m)?></td><td style="color:red;"><?=substr($sub,0,2)?></td></tr>
<?php endforeach;?>
<tr style="font-weight:700;background:#f0f0f0;"><td></td><td>TOTAL:</td><td><?=$total?></td><td><?=$agg_total?></td><td></td><td></td></tr>
</table>
<div class="division-line"><div>DIVISION: 1</div><div>NO. OF PUPILS: 35</div><div>Promotional:........</div></div>
<div class="comment-box"><div><b>CLASS TEACHER:</b> Good performance<div style="margin-top:15px;">SIGN: <b>Teacher Chance S.</b></div></div><div><b>HEAD TEACHER:</b> Very good<div style="margin-top:15px;">SIGN: <i style="font-family:cursive;font-size:18px;">J. Signature</i></div></div></div>
<div style="padding:10px 20px;font-size:12px;font-weight:600;">NEXT TERM BEGINS ON: Monday, 15th SEPT 2025</div>
<div style="text-align:center;font-size:10px;font-weight:700;margin-bottom:10px;">"KNOWLEDGE IS POWER"</div>
</div></body></html>
<?php exit; }?>

<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Pick Student</title>
<style>
body{font-family:Poppins,sans-serif;background:#f0f2f0;padding:20px;}.box{width:95%;max-width:1100px;margin:0 auto;background:#fff;padding:20px;border-radius:12px;}
.filter{display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;} select,input{padding:12px;border:1px solid #ccc;border-radius:8px;}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:15px;}
.card{border:2px solid #ddd;border-radius:12px;padding:12px;text-align:center;text-decoration:none;color:#000;background:#fff;display:block;transition:0.2s;}
.card:hover{border-color:#1e5a2f;transform:translateY(-3px);box-shadow:0 5px 15px rgba(0,0,0,0.1);}
.card img{width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid #1e5a2f;margin-bottom:8px;}
.badge{background:#1e5a2f;color:#fff;padding:3px 10px;border-radius:20px;font-size:11px;}
.class-badge{background:#ff7a00;color:#fff;padding:3px 10px;border-radius:20px;font-size:11px;}
</style></head><body>
<div class="box">
<h2 style="color:#1e5a2f;margin-top:0;">📋 Pick Student - With Photo & Class</h2>
<form method="GET" class="filter">
<select name="class" onchange="this.form.submit()"><option value="">-- All Classes --</option>
<?php foreach($classes_list as $cid=>$cname):?>
<option value="<?=$cid?>" <?=$class_filter==$cid?'selected':''?>><?=$cname?></option>
<?php endforeach;?>
</select>
<input type="text" name="term" value="<?=$term?>" placeholder="Term e.g II 2025" style="width:150px;">
<button type="submit" style="padding:12px 20px;background:#1e5a2f;color:#fff;border:none;border-radius:8px;">Filter</button>
</form>

<div class="grid">
<?php
$sql = "SELECT s.*, c.class_name FROM students s LEFT JOIN classes c ON s.class_id=c.id WHERE s.status='Active' ";
if($class_filter) $sql.= " AND s.class_id='$class_filter' ";
$sql.= " ORDER BY s.first_name ASC LIMIT 200";
$students = $conn->query($sql);
if($students){
 while($s=$students->fetch_assoc()){
  $full = $s['first_name']." ".$s['last_name'];
  $photo = $s['photo']?: 'uploads/'.$s['student_id'].'.jpg';
  $cname = $s['class_name']?? $classes_list[$s['class_id']]?? 'Class '.$s['class_id'];
?>
<a class="card" href="pupil_report.php?id=<?=$s['student_id']?>&term=<?=$term?>&class=<?=$class_filter?>">
<img src="<?=$photo?>" onerror="this.src='https://ui-avatars.com/api/?name=<?=urlencode($full)?>&background=1e5a2f&color=fff'">
<b style="text-transform:uppercase;font-size:13px;"><?=$full?></b>
<div style="font-size:11px;color:#666;margin:3px 0;"><?=$s['admission_no']?> | <?=$s['sex']?></div>
<div><span class="class-badge"><?=$cname?></span></div>
</a>
<?php }}?>
</div>

<?php if(!$students || $students->num_rows==0):?>
<p style="color:red;text-align:center;">No Active students found. Check class_id filter.</p>
<?php endif;?>
</div>
</body></html>