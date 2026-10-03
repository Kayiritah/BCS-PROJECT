<?php
$school_name = "BUNAMWAYA CENTRAL PARENTS' NUR. & PRI. SCH.";
$school_contacts= "P.O.BOX 9270, Kampala.<br>TEL: +256 772896922 / +256 751431922<br>EMAIL: bunamwayacentralparentsschool@gmail.com";
$school_motto = '"KNOWLEDGE IS POWER"';
$badges = ["badge.png","badge.jpeg","badge.jpg","logo.png","images/logo.jpeg"];
$school_badge="badge.jpeg"; foreach($badges as $b){ if(file_exists($b)){ $school_badge=$b; break; } }
$sigs = ["hm_signature.png","hm_signature_transparent.png","hm_signature.jpeg"];
$hm_signature="hm_signature.png"; foreach($sigs as $s){ if(file_exists($s)){ $hm_signature=$s; break; } }

$pupil = $_POST['pupil']?? 'ABAMATSIKO LYT';
$class = $_POST['class']?? 'P.2';
$term = $_POST['term']?? 'Term II 2025';
$teacher_name = $_POST['teacher_name']?? 'Tr. Chance S.';
$t_comment = $_POST['t_comment']?? 'Very good, keep it up!';
$hm_comment = $_POST['hm_comment']?? 'Promoted to next class.';
$promotion = $_POST['promotion']?? 'Promoted';
$next_term = $_POST['next_term']?? '15th SEPTEMBER 2025';
$position = $_POST['position']?? '5';

$isNursery = in_array($class, ["BABY","MIDDLE","TOP"]);
$isLower = in_array($class, ["P.1","P.2","P.3"]);
$isUpper =!$isNursery &&!$isLower;

// NURSERY
$n_lang1 = $_POST['n_lang1']?? '82'; $n_math = $_POST['n_math']?? '85'; $n_social = $_POST['n_social']?? '80'; $n_health = $_POST['n_health']?? '88'; $n_lang2 = $_POST['n_lang2']?? '84';
$n_lang1_init = $_POST['n_lang1_init']?? 'LN'; $n_math_init = $_POST['n_math_init']?? 'MC'; $n_social_init = $_POST['n_social_init']?? 'SD'; $n_health_init = $_POST['n_health_init']?? 'HH'; $n_lang2_init = $_POST['n_lang2_init']?? 'L2';
// LOWER
$l_eng = $_POST['l_eng']?? '67'; $l_mtc = $_POST['l_mtc']?? '78'; $l_re = $_POST['l_re']?? '75'; $l_lit1 = $_POST['l_lit1']?? '70'; $l_lug = $_POST['l_lug']?? '80'; $l_lit2 = $_POST['l_lit2']?? '72';
$l_eng_init = $_POST['l_eng_init']?? 'EN'; $l_mtc_init = $_POST['l_mtc_init']?? 'MT'; $l_re_init = $_POST['l_re_init']?? 'RE'; $l_lit1_init = $_POST['l_lit1_init']?? 'L1'; $l_lug_init = $_POST['l_lug_init']?? 'LG'; $l_lit2_init = $_POST['l_lit2_init']?? 'L2';
// UPPER
$eng = $_POST['eng']?? '67'; $mtc = $_POST['mtc']?? '88'; $sci = $_POST['sci']?? '74'; $sst = $_POST['sst']?? '72'; $re = $_POST['re']?? '68';
$eng_init = $_POST['eng_init']?? 'NR'; $mtc_init = $_POST['mtc_init']?? 'CS'; $sci_init = $_POST['sci_init']?? 'PW'; $sst_init = $_POST['sst_init']?? 'AJ'; $re_init = $_POST['re_init']?? 'MK';

$photo = "https://ui-avatars.com/api/?name=".urlencode($pupil)."&background=1e5a2f&color=fff";
if(isset($_FILES['photo']) && $_FILES['photo']['tmp_name']){ $dir="uploads/manual/"; if(!is_dir($dir)) mkdir($dir,0777,true); $path=$dir.time()."_".basename($_FILES['photo']['name']); move_uploaded_file($_FILES['photo']['tmp_name'],$path); $photo=$path; }
if(!empty($_POST['photo_base64'])) $photo=$_POST['photo_base64'];

function agg($m){ $m=(int)$m; if($m>=80) return 'D1'; if($m>=65) return 'D2'; if($m>=50) return 'C3'; if($m>=35) return 'P7'; return 'F9'; }
function remark($m){ $m=(int)$m; if($m>=80) return 'Excellent'; if($m>=65) return 'V.Good'; if($m>=50) return 'Good'; if($m>=35) return 'Fair'; return 'Needs effort'; }
function posSuffix($n){ $n=(int)$n; if($n%100>=11 && $n%100<=13) return $n.'th'; return $n.(['th','st','nd','rd','th','th','th','th','th','th'][$n%10]); }
function getBee($avg){ $avg=(int)$avg; if($avg>=80) return '🐝🌟 Excellent! Keep Buzzing!'; if($avg>=65) return '🐝 Very Good Bee!'; if($avg>=50) return '🐝 Good Work!'; return '🐝 Keep Trying!'; }

if($isNursery){ $total=(int)$n_lang1+(int)$n_math+(int)$n_social+(int)$n_health+(int)$n_lang2; $max_total=500; $avg=round($total/5,1); }
elseif($isLower){ $total=(int)$l_eng+(int)$l_mtc+(int)$l_re+(int)$l_lit1+(int)$l_lug+(int)$l_lit2; $max_total=600; $avg=round($total/6,1); }
else { $total=$re!==''? (int)$eng+(int)$mtc+(int)$sci+(int)$sst+(int)$re : (int)$eng+(int)$mtc+(int)$sci+(int)$sst; $max_total=$re!==''?500:400; $avg=$re!==''?round($total/5,1):round($total/4,1); $div=$total>=400?'I':($total>=300?'II':'III'); if($re==='') $div=$total>=280?'I':($total>=200?'II':'III'); }

if($_SERVER['REQUEST_METHOD']=='POST' &&!empty($_POST['pupil'])){
 $saveDir="report_cards/"; if(!is_dir($saveDir)) mkdir($saveDir,0777,true);
 $classDir=$saveDir.preg_replace('/[^A-Za-z0-9]/','', $class). "/"; if(!is_dir($classDir)) mkdir($classDir,0777,true);
 file_put_contents($classDir.preg_replace('/[^A-Za-z0-9]/','_', $pupil). ".json", json_encode($_POST, JSON_PRETTY_PRINT));
}
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title><?=$pupil?></title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial,sans-serif}
body{background:#d9d9d9;display:flex;justify-content:center;padding:12px;gap:18px}
.form-box{width:390px;background:#fff;padding:14px;border:2px solid #0e4d1b;border-radius:10px;height:fit-content;position:sticky;top:12px;max-height:98vh;overflow:auto}
.form-box h3{background:#0e4d1b;color:#fff;text-align:center;padding:9px;border-radius:6px;margin-bottom:10px;font-size:12px}
.form-box label{font-size:10px;font-weight:900;color:#0e4d1b;display:block;margin-top:6px}
.form-box input,.form-box select,.form-box textarea{width:100%;padding:7px;margin:3px 0 6px 0;border:1.5px solid #ccc;border-radius:5px;font-size:12px}
.row{display:grid;grid-template-columns:1fr 1fr;gap:7px}
.row3{display:grid;grid-template-columns:1fr 60px;gap:5px;align-items:end}
.btn{width:100%;padding:9px;border:none;border-radius:6px;font-weight:800;cursor:pointer;margin-top:5px}
.btn-save{background:#0e4d1b;color:#fff}.btn-print{background:#ff7a00;color:#fff}
.btn-back{background:#333;color:#fff;display:block;text-align:center;text-decoration:none;padding:8px;border-radius:6px;margin-bottom:6px;font-size:11px;font-weight:800}
.a4{width:210mm;min-height:287mm;background:#fff;padding:9mm 9mm 13mm 9mm;position:relative;border:2px solid #0e4d1b;flex-shrink:0;display:flex;flex-direction:column}
.a4::before{content:'';position:absolute;top:3mm;left:3mm;right:3mm;bottom:3mm;border:1.5px solid #ff7a00;pointer-events:none}
.watermark{position:absolute;top:46%;left:50%;transform:translate(-50%,-50%);width:500px;opacity:0.10;z-index:0;pointer-events:none}
.watermark img{width:100%}
.content{position:relative;z-index:1;flex:1;display:flex;flex-direction:column}
.top-box{border:2px solid #0e4d1b;text-align:center;padding:7px;font-size:14px;font-weight:900;color:#0e4d1b;background:#f7fdf5}
.header{display:grid;grid-template-columns:145px 1fr 125px;gap:14px;align-items:center;padding:14px 0 12px 0;border-bottom:3px solid #0e4d1b}
.badge-wrap{width:138px;height:138px;border:3px solid #0e4d1b;padding:3px;background:#0e4d1b;border-radius:12px;display:flex;align-items:center;justify-content:center}
.badge-wrap img{width:100%;height:100%;object-fit:cover;border-radius:8px}
.header-center{text-align:center;font-size:11.5px;line-height:1.6;font-weight:600}
.p5{margin-top:8px;font-size:12px;font-weight:900;color:#0e4d1b;border:2px solid #ff7a00;padding:5px 14px;border-radius:22px;background:#fff7ed;display:inline-block}
.photo-wrap{width:122px;height:142px;border:2.5px solid #0e4d1b;border-radius:7px;overflow:hidden;background:#fff}
.photo-wrap img{width:100%;height:100%;object-fit:cover}
.pupil-line{display:flex;justify-content:space-between;font-size:13px;margin:14px 0 10px;padding:10px 12px;background:#f8fdf6;border-left:5px solid #0e4d1b;font-weight:700}
.end-term{text-align:center;font-weight:900;font-size:14px;margin:12px 0 10px;color:#0e4d1b}
table{width:100%;border-collapse:collapse;font-size:12.5px}
th,td{border:1.8px solid #000;padding:7px 4px;text-align:center}
th{background:#0e4d1b;color:#fff;font-size:11px}th.orange{background:#ff7a00}
td:first-child{text-align:left;padding-left:10px;font-weight:800;background:#fafafa;font-size:11.5px}
td.mark{font-weight:900;color:#0e4d1b;background:#f0f9ec;font-size:14px}
.division{display:grid;grid-template-columns:1fr 1fr 1.5fr;border:2px solid #000;border-top:none;font-size:13px;font-weight:900;background:#fff3e0}
.division div{padding:8px;border-right:1.8px solid #000}
.comments{border:2px solid #000;border-top:none;font-size:11.5px}
.comments-row{display:grid;grid-template-columns:135px 1fr 90px 1fr;border-bottom:1.8px solid #000;min-height:65px}
.label{background:#e8f5e9;padding:8px;font-weight:900;border-right:1.8px solid #000;color:#0e4d1b;display:flex;align-items:center;font-size:10px}
.val{padding:8px;border-right:1.8px solid #000;display:flex;align-items:flex-start;justify-content:center;flex-direction:column;gap:4px}
.bee{font-size:11px;font-weight:900;color:#d48800;background:#fff8e1;border:1px dashed #ffb300;padding:3px 8px;border-radius:12px;display:inline-block}
.sig-label{background:#fff7ed;padding:8px;font-weight:900;border-right:1.8px solid #000;color:#ff7a00;display:flex;align-items:center;font-size:10px}
.sig-val{padding:6px 10px;display:flex;align-items:center;justify-content:center}
.sig-img{max-width:120px;max-height:48px;object-fit:contain}
.next-term{margin-top:12px;margin-bottom:22px;padding:10px;background:#0e4d1b;color:#fff;border-radius:6px;text-align:center;font-size:12px;font-weight:900}
.footer-bar{position:absolute;bottom:5.5mm;left:9mm;right:9mm;background:#0e4d1b;height:9px;border-radius:3px}
.footer-motto{position:absolute;bottom:7.5mm;left:50%;transform:translateX(-50%);background:#fff;font-size:9px;font-weight:900;padding:0 12px;color:#0e4d1b;border:1.8px solid #ff7a00;border-radius:12px}
@media print{body{background:#fff;padding:0;margin:0;gap:0}.form-box{display:none!important}.a4{border:none;width:100%;min-height:100vh;padding:8mm 8mm 12mm 8mm}@page{size:A4;margin:0} *{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important}}
</style></head><body>
<div class="form-box">
<a href="dashboard.php" class="btn-back">⬅️ DASHBOARD</a>
<a href="report_manager.php" class="btn-back" style="background:#0e4d1b;">📂 VIEW SAVED</a>
<h3>📝 <?= $isNursery?'NURSERY':'P1-P3 LOWER / P4-P7 UPPER'?> - NO AVERAGE</h3>
<form method="POST" enctype="multipart/form-data">
<label>Pupil Name</label><input type="text" name="pupil" value="<?=$pupil?>" required>
<div class="row"><div><label>Class *</label><select name="class" onchange="this.form.submit()" required><?php foreach(["BABY","MIDDLE","TOP","P.1","P.2","P.3","P.4","P.5","P.6","P.7"] as $c){ $sel=$class==$c?'selected':''; echo "<option $sel>$c</option>"; }?></select></div><div><label>Term</label><input type="text" name="term" value="<?=$term?>"></div></div>
<div class="row"><div><label style="color:#ff7a00;">Position *</label><input type="number" name="position" value="<?=$position?>" required style="border:2px solid #ff7a00;background:#fff7ed;font-weight:900"></div><div><label>Total</label><input type="text" value="<?=$total?> / <?=$max_total?>" disabled style="background:#f0f2f5;font-weight:900"></div></div>

<?php if($isNursery):?>
<div style="background:#fff7ed;border:1.5px dashed #ff7a00;padding:8px;border-radius:6px;margin:8px 0">
<label style="color:#ff7a00;">NURSERY - 5 AREAS + INITIALS</label>
<div class="row3"><input type="number" name="n_lang1" value="<?=$n_lang1?>" placeholder="LANGUAGE DEV'T I"><input type="text" name="n_lang1_init" value="<?=$n_lang1_init?>"></div>
<div class="row3"><input type="number" name="n_math" value="<?=$n_math?>" placeholder="MATHEMATICAL C."><input type="text" name="n_math_init" value="<?=$n_math_init?>"></div>
<div class="row3"><input type="number" name="n_social" value="<?=$n_social?>" placeholder="SOCIAL DEV"><input type="text" name="n_social_init" value="<?=$n_social_init?>"></div>
<div class="row3"><input type="number" name="n_health" value="<?=$n_health?>" placeholder="HEALTH HABBITS"><input type="text" name="n_health_init" value="<?=$n_health_init?>"></div>
<div class="row3"><input type="number" name="n_lang2" value="<?=$n_lang2?>" placeholder="LANGUAGE DEV'T II"><input type="text" name="n_lang2_init" value="<?=$n_lang2_init?>"></div>
</div>
<?php elseif($isLower):?>
<div style="background:#e8f5e9;border:1.5px dashed #0e4d1b;padding:8px;border-radius:6px;margin:8px 0">
<label style="color:#0e4d1b;">LOWER P1-P3 - 6 SUBJECTS + INITIALS</label>
<div class="row3"><input type="number" name="l_eng" value="<?=$l_eng?>" placeholder="ENGLISH"><input type="text" name="l_eng_init" value="<?=$l_eng_init?>"></div>
<div class="row3"><input type="number" name="l_mtc" value="<?=$l_mtc?>" placeholder="MATHEMATICS"><input type="text" name="l_mtc_init" value="<?=$l_mtc_init?>"></div>
<div class="row3"><input type="number" name="l_re" value="<?=$l_re?>" placeholder="RE"><input type="text" name="l_re_init" value="<?=$l_re_init?>"></div>
<div class="row3"><input type="number" name="l_lit1" value="<?=$l_lit1?>" placeholder="LITERACY 1"><input type="text" name="l_lit1_init" value="<?=$l_lit1_init?>"></div>
<div class="row3"><input type="number" name="l_lug" value="<?=$l_lug?>" placeholder="LUGANDA"><input type="text" name="l_lug_init" value="<?=$l_lug_init?>"></div>
<div class="row3"><input type="number" name="l_lit2" value="<?=$l_lit2?>" placeholder="LITERACY 2"><input type="text" name="l_lit2_init" value="<?=$l_lit2_init?>"></div>
</div>
<?php else:?>
<div style="background:#f0f9ec;border:1.5px dashed #0e4d1b;padding:8px;border-radius:6px;margin:8px 0">
<label>UPPER P4-P7 - AGG KEPT, AVG REMOVED</label>
<div class="row3"><input type="number" name="eng" value="<?=$eng?>" placeholder="ENG"><input type="text" name="eng_init" value="<?=$eng_init?>"></div>
<div class="row3"><input type="number" name="mtc" value="<?=$mtc?>" placeholder="MTC"><input type="text" name="mtc_init" value="<?=$mtc_init?>"></div>
<div class="row3"><input type="number" name="sci" value="<?=$sci?>" placeholder="SCI"><input type="text" name="sci_init" value="<?=$sci_init?>"></div>
<div class="row3"><input type="number" name="sst" value="<?=$sst?>" placeholder="SST"><input type="text" name="sst_init" value="<?=$sst_init?>"></div>
<div class="row3"><input type="number" name="re" value="<?=$re?>" placeholder="RE"><input type="text" name="re_init" value="<?=$re_init?>"></div>
</div>
<?php endif;?>

<label>📸 Photo</label><input type="file" name="photo" accept="image/*" onchange="previewPhoto(this)" style="background:#e8f5e9;">
<input type="text" name="teacher_name" value="<?=$teacher_name?>">
<textarea name="t_comment" rows="3"><?=$t_comment?></textarea>
<textarea name="hm_comment" rows="2"><?=$hm_comment?></textarea>
<div class="row"><input type="text" name="promotion" value="<?=$promotion?>"><input type="text" name="next_term" value="<?=$next_term?>"></div>
<input type="hidden" name="photo_base64" id="photo_base64">
<button class="btn btn-save" type="submit">💾 SAVE TO <?=strtoupper($class)?></button>
<button class="btn btn-print" type="button" onclick="window.print()">🖨️ PRINT A4</button>
</form>
</div>

<div class="a4">
<div class="watermark"><img src="<?=$school_badge?>?v=<?=time()?>"></div>
<div class="content">
<div class="top-box"><?=$school_name?></div>
<div class="header">
<div class="badge-wrap"><img src="<?=$school_badge?>?v=<?=time()?>"></div>
<div class="header-center"><?=$school_contacts?><br><div class="p5"><?=$class?> TERMLY REPORT - <?=$term?></div></div>
<div class="photo-wrap"><img id="reportPhoto" src="<?=$photo?>"></div>
</div>
<div class="pupil-line"><div>NAME: <b style="border-bottom:2px solid #000;"><?=strtoupper($pupil)?></b></div><div>TERM: <span style="color:#ff7a00;font-weight:900;"><?=$term?></span></div></div>
<div class="end-term"><?= $isNursery?'LEARNING AREAS PERFORMANCE':'END OF TERM PERFORMANCE'?></div>

<?php if($isNursery):?>
<table>
<tr><th>LEARNING AREA</th><th>MARKS SCORED</th><th>REMARKS</th><th>INIT</th></tr>
<tr><td>LANGUAGE DEV'T I</td><td class="mark"><?=$n_lang1?></td><td><?=remark($n_lang1)?></td><td><?=$n_lang1_init?></td></tr>
<tr><td>MATHEMATICAL C.</td><td class="mark"><?=$n_math?></td><td><?=remark($n_math)?></td><td><?=$n_math_init?></td></tr>
<tr><td>SOCIAL DEVELOPMENT</td><td class="mark"><?=$n_social?></td><td><?=remark($n_social)?></td><td><?=$n_social_init?></td></tr>
<tr><td>HEALTH HABBITS</td><td class="mark"><?=$n_health?></td><td><?=remark($n_health)?></td><td><?=$n_health_init?></td></tr>
<tr><td>LANGUAGE DEV'T II</td><td class="mark"><?=$n_lang2?></td><td><?=remark($n_lang2)?></td><td><?=$n_lang2_init?></td></tr>
<tr style="font-weight:900;"><td style="text-align:right;background:#e8f5e9;">TOTAL</td><td style="background:#ff7a00;color:#fff;"><?=$total?>/<?=$max_total?></td><td colspan="2" style="background:#e8f5e9;">POS: <?=posSuffix($position)?></td></tr>
</table>
<?php elseif($isLower):?>
<table>
<tr><th rowspan="2">SUBJECT</th><th colspan="2">FINAL MARK OBTAINED (%)</th><th rowspan="2">REMARKS</th><th rowspan="2">INIT</th></tr>
<tr><th style="background:#ff7a00;">FULL MARKS</th><th style="background:#ff7a00;">MARK OBT</th></tr>
<tr><td>ENGLISH</td><td>100</td><td class="mark"><?=$l_eng?></td><td><?=remark($l_eng)?></td><td><?=$l_eng_init?></td></tr>
<tr><td>MATHEMATICS</td><td>100</td><td class="mark"><?=$l_mtc?></td><td><?=remark($l_mtc)?></td><td><?=$l_mtc_init?></td></tr>
<tr><td>RE</td><td>100</td><td class="mark"><?=$l_re?></td><td><?=remark($l_re)?></td><td><?=$l_re_init?></td></tr>
<tr><td>LITERACY 1</td><td>100</td><td class="mark"><?=$l_lit1?></td><td><?=remark($l_lit1)?></td><td><?=$l_lit1_init?></td></tr>
<tr><td>LUGANDA</td><td>100</td><td class="mark"><?=$l_lug?></td><td><?=remark($l_lug)?></td><td><?=$l_lug_init?></td></tr>
<tr><td>LITERACY 2</td><td>100</td><td class="mark"><?=$l_lit2?></td><td><?=remark($l_lit2)?></td><td><?=$l_lit2_init?></td></tr>
<tr style="font-weight:900;"><td style="text-align:right;background:#e8f5e9;">TOTAL</td><td style="background:#e8f5e9;"><?=$max_total?></td><td style="background:#ff7a00;color:#fff;"><?=$total?></td><td colspan="2" style="background:#e8f5e9;">POS: <?=posSuffix($position)?></td></tr>
</table>
<?php else:?>
<table>
<tr><th rowspan="2">SUBJECT</th><th colspan="2" class="orange">FINAL MARK</th><th rowspan="2">AGG</th><th rowspan="2">REMARKS</th><th rowspan="2">INIT</th></tr>
<tr><th class="orange">FULL</th><th class="orange">OBT</th></tr>
<tr><td>ENGLISH</td><td>100</td><td class="mark"><?=$eng?></td><td><?=agg($eng)?></td><td><?=remark($eng)?></td><td><?=$eng_init?></td></tr>
<tr><td>MATHEMATICS</td><td>100</td><td class="mark"><?=$mtc?></td><td><?=agg($mtc)?></td><td><?=remark($mtc)?></td><td><?=$mtc_init?></td></tr>
<tr><td>SCIENCE</td><td>100</td><td class="mark"><?=$sci?></td><td><?=agg($sci)?></td><td><?=remark($sci)?></td><td><?=$sci_init?></td></tr>
<tr><td>SST</td><td>100</td><td class="mark"><?=$sst?></td><td><?=agg($sst)?></td><td><?=remark($sst)?></td><td><?=$sst_init?></td></tr>
<?php if($re!==''){?><tr><td>RE</td><td>100</td><td class="mark"><?=$re?></td><td><?=agg($re)?></td><td><?=remark($re)?></td><td><?=$re_init?></td></tr><?php }?>
<tr style="font-weight:900;"><td style="text-align:right;background:#e8f5e9;">TOTAL</td><td style="background:#e8f5e9;"><?=$max_total?></td><td style="background:#ff7a00;color:#fff;"><?=$total?></td><td style="background:#e8f5e9;"><?=agg($total/4)?></td><td colspan="2" style="background:#e8f5e9;">DIV: <?=$div?> | POS: <?=posSuffix($position)?></td></tr>
</table>
<?php endif;?>

<?php if($isNursery || $isLower):?>
<div class="division" style="background:#ff7a00;color:#fff;"><div style="background:#ff7a00;">POS: <?=posSuffix($position)?></div><div style="background:#0e4d1b;color:#fff;">Out of: 35</div><div style="background:#fff;color:#000;">STATUS: <?=$promotion?></div></div>
<?php else:?>
<div class="division"><div>DIV: <?=$div?></div><div>POS: <?=posSuffix($position)?>/35</div><div>STATUS: <?=$promotion?></div></div>
<?php endif;?>

<div class="comments">
<div class="comments-row"><div class="label">CLASS TEACHER COMMENT:</div><div class="val"><span><?=$t_comment?></span><span class="bee"><?=getBee($avg)?></span></div><div class="sig-label">SIGN:</div><div class="sig-val"><?=$teacher_name?></div></div>
<div class="comments-row"><div class="label">HEAD TEACHER COMMENT:</div><div class="val"><span><?=$hm_comment?></span></div><div class="sig-label">SIGN:</div><div class="sig-val"><?php if(file_exists($hm_signature)){?><img class="sig-img" src="<?=$hm_signature?>?v=<?=time()?>"><?php }else{ echo "Mwesigwa J."; }?></div></div>
</div>
<div class="next-term">NEXT TERM BEGINS: <?=strtoupper($next_term)?></div>
</div>
<div class="footer-bar"></div><div class="footer-motto"><?=$school_motto?></div>
</div>
<script>function previewPhoto(i){ if(i.files&&i.files[0]){ let r=new FileReader(); r.onload=function(e){ document.getElementById('reportPhoto').src=e.target.result; document.getElementById('photo_base64').value=e.target.result; }; r.readAsDataURL(i.files[0]); } }</script>
</body></html>