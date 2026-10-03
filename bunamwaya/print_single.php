<?php
$class = $_GET['class']?? ''; $pupilFile = $_GET['pupil']?? ''; $file = "report_cards/$class/$pupilFile.json";
if(!file_exists($file)) die("<h2 style='text-align:center;margin-top:50px'>Report not found<br><a href='report_manager.php'>Back</a></h2>");
$data = json_decode(file_get_contents($file), true);
$school_name = "BUNAMWAYA CENTRAL PARENTS' NUR. & PRI. SCH.";
$school_contacts= "P.O.BOX 9270, Kampala.<br>TEL: +256 772896922 / +256 751431922<br>EMAIL: bunamwayacentralparentsschool@gmail.com";
$school_motto = '"KNOWLEDGE IS POWER"';
$badges = ["badge.png","badge.jpeg","badge.jpg"]; $school_badge="badge.jpeg"; foreach($badges as $b){ if(file_exists($b)){ $school_badge=$b; break; } }
$sigs = ["hm_signature.png","hm_signature_transparent.png"]; $hm_signature="hm_signature.png"; foreach($sigs as $s){ if(file_exists($s)){ $hm_signature=$s; break; } }
$pupil=$data['pupil']; $class=$data['class']; $term=$data['term']; $eng=$data['eng']; $mtc=$data['mtc']; $sci=$data['sci']; $sst=$data['sst']; $re=$data['re']??''; $t_comment=$data['t_comment']; $hm_comment=$data['hm_comment']; $teacher_name=$data['teacher']; $promotion=$data['promotion']; $next_term=$data['next_term']; $photo=$data['photo']; $total=$data['total']; $avg=$data['avg']; $div=$data['div'];
function agg($m){ if($m==='') return ''; $m=(int)$m; if($m>=80) return 'D1'; if($m>=65) return 'D2'; if($m>=50) return 'C3'; if($m>=35) return 'P7'; return 'F9'; }
function remark($m){ if($m==='') return ''; $m=(int)$m; if($m>=80) return 'Excellent'; if($m>=65) return 'Very good'; if($m>=50) return 'Good'; return 'Needs effort'; }
$max_total=$re!==''?500:400;
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Print - <?=$pupil?></title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial,sans-serif}
body{background:#fff;display:flex;justify-content:center;padding:0}
.a4{width:210mm;height:287mm;background:#fff;padding:9mm 9mm 13mm 9mm;position:relative;border:2px solid #0e4d1b;display:flex;flex-direction:column}
.a4::before{content:'';position:absolute;top:3mm;left:3mm;right:3mm;bottom:3mm;border:1.5px solid #ff7a00;pointer-events:none}
.watermark{position:absolute;top:46%;left:50%;transform:translate(-50%,-50%);width:500px;opacity:0.13;z-index:0;pointer-events:none}
.watermark img{width:100%}
.content{position:relative;z-index:1;flex:1;display:flex;flex-direction:column}
.top-box{border:2px solid #0e4d1b;text-align:center;padding:7px;font-size:14px;font-weight:900;color:#0e4d1b;background:#f7fdf5}
.header{display:grid;grid-template-columns:145px 1fr 125px;gap:14px;align-items:center;padding:14px 0 12px 0;border-bottom:3px solid #0e4d1b}
.badge-wrap{width:138px;height:138px;border:3px solid #0e4d1b;padding:6px;background:#fff;border-radius:12px;display:flex;align-items:center;justify-content:center}
.badge-wrap img{width:100%;height:100%;object-fit:contain}
.header-center{text-align:center;font-size:12px;line-height:1.7;font-weight:600}
.p5{margin-top:8px;font-size:13.5px;font-weight:900;color:#0e4d1b;border:2px solid #ff7a00;padding:6px 18px;border-radius:22px;background:#fff7ed;display:inline-block}
.photo-wrap{width:122px;height:142px;border:2.5px solid #0e4d1b;border-radius:7px;overflow:hidden;background:#fff}
.photo-wrap img{width:100%;height:100%;object-fit:cover}
.pupil-line{display:flex;justify-content:space-between;font-size:13.5px;margin:14px 0 10px;padding:10px 12px;background:#f8fdf6;border-left:5px solid #0e4d1b;font-weight:700}
.end-term{text-align:center;font-weight:900;font-size:14px;margin:14px 0 10px;color:#0e4d1b}
table{width:100%;border-collapse:collapse;font-size:13.5px}
th,td{border:1.8px solid #000;padding:8px 5px;text-align:center}
th{background:#0e4d1b;color:#fff;font-size:12px}th.orange{background:#ff7a00}
td:first-child{text-align:left;padding-left:12px;font-weight:800;background:#fafafa}
td.mark{font-weight:900;color:#0e4d1b;background:#f0f9ec;font-size:14px}
.division{display:grid;grid-template-columns:100px 1fr 1.5fr;border:2px solid #000;border-top:none;font-size:13px;font-weight:900;background:#fff3e0}
.division div{padding:9px;border-right:1.8px solid #000}
.comments{border:2px solid #000;border-top:none;font-size:12.5px}
.comments-row{display:grid;grid-template-columns:145px 1fr 110px 1fr;border-bottom:1.8px solid #000;min-height:72px}
.label{background:#e8f5e9;padding:9px;font-weight:900;border-right:1.8px solid #000;color:#0e4d1b;display:flex;align-items:center;font-size:12px}
.val{padding:9px;border-right:1.8px solid #000;display:flex;align-items:center}
.sig-label{background:#fff7ed;padding:9px;font-weight:900;border-right:1.8px solid #000;color:#ff7a00;display:flex;align-items:center}
.sig-val{padding:6px 10px;display:flex;align-items:center;justify-content:center}
.sig-img{max-width:130px;max-height:50px;object-fit:contain;filter:contrast(1.3)}
.next-term{margin-top:16px;margin-bottom:28px;padding:11px;background:#0e4d1b;color:#fff;border-radius:6px;text-align:center;font-size:13px;font-weight:900}
.footer-bar{position:absolute;bottom:5.5mm;left:9mm;right:9mm;background:#0e4d1b;height:9px;border-radius:3px}
.footer-motto{position:absolute;bottom:7.5mm;left:50%;transform:translateX(-50%);background:#fff;font-size:9.5px;font-weight:900;padding:0 14px;color:#0e4d1b;border:1.8px solid #ff7a00;border-radius:12px}
.btn{position:fixed;top:15px;right:15px;background:#ff7a00;color:#fff;border:none;padding:12px 18px;font-weight:900;border-radius:8px;cursor:pointer;z-index:99}
.btn2{position:fixed;top:15px;left:15px;background:#333;color:#fff;padding:12px 18px;font-weight:900;border-radius:8px;z-index:99;text-decoration:none}
@media print{.btn,.btn2{display:none!important}.a4{border:none;width:100%;height:100vh}@page{size:A4;margin:0mm} *{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important}}
</style></head><body>
<a href="report_manager.php?class=<?=$_GET['class']?>" class="btn2">⬅️ BACK</a>
<button class="btn" onclick="window.print()">🖨️ PRINT ONE PAGE</button>
<div class="a4">
<div class="watermark"><img src="<?=$school_badge?>?v=<?=time()?>"></div>
<div class="content">
<div class="top-box"><?=$school_name?></div>
<div class="header"><div class="badge-wrap"><img src="<?=$school_badge?>?v=<?=time()?>"></div><div class="header-center"><?=$school_contacts?><br><div class="p5"><?=$class?> TERMLY REPORT - <?=$term?></div></div><div class="photo-wrap"><img src="<?=$photo?>"></div></div>
<div class="pupil-line"><div>NAME: <b style="border-bottom:2px solid #000;"><?=strtoupper($pupil)?></b></div><div>TERM: <span style="color:#ff7a00;font-weight:900;"><?=$term?></span></div></div>
<div class="end-term">END OF TERM PERFORMANCE</div>
<table>
<tr><th rowspan="2">SUBJECT</th><th colspan="2" class="orange">FINAL MARK</th><th rowspan="2">AGG</th><th rowspan="2">REMARKS</th><th rowspan="2">INIT</th></tr>
<tr><th class="orange">FULL</th><th class="orange">OBT</th></tr>
<tr><td>ENGLISH</td><td>100</td><td class="mark"><?=$eng?></td><td><?=agg($eng)?></td><td><?=remark($eng)?></td><td>NR</td></tr>
<tr><td>MATHEMATICS</td><td>100</td><td class="mark"><?=$mtc?></td><td><?=agg($mtc)?></td><td><?=remark($mtc)?></td><td>CS</td></tr>
<tr><td>SCIENCE</td><td>100</td><td class="mark"><?=$sci?></td><td><?=agg($sci)?></td><td><?=remark($sci)?></td><td>PW</td></tr>
<tr><td>SST</td><td>100</td><td class="mark"><?=$sst?></td><td><?=agg($sst)?></td><td><?=remark($sst)?></td><td>AJ</td></tr>
<?php if($re!==''){?><tr><td>RE</td><td>100</td><td class="mark"><?=$re?></td><td><?=agg($re)?></td><td><?=remark($re)?></td><td>MK</td></tr><?php }?>
<tr style="font-weight:900;"><td style="text-align:right;background:#e8f5e9;">TOTAL</td><td style="background:#e8f5e9;"><?=$max_total?></td><td style="background:#ff7a00;color:#fff;font-size:16px;"><?=$total?></td><td style="background:#e8f5e9;"><?=agg($total/4)?></td><td style="background:#e8f5e9;">Avg: <?=$avg?>%</td><td style="background:#e8f5e9;"></td></tr>
</table>
<div class="division"><div>DIV: <?=$div?></div><div>PUPILS: 35</div><div>STATUS: <?=$promotion?></div></div>
<div class="comments">
<div class="comments-row"><div class="label">CLASS TEACHER COMMENT:</div><div class="val"><?=$t_comment?></div><div class="sig-label">SIGNATURE:</div><div class="sig-val"><?=$teacher_name?></div></div>
<div class="comments-row"><div class="label">HEAD TEACHER COMMENT:</div><div class="val"><?=$hm_comment?></div><div class="sig-label">SIGNATURE:</div><div class="sig-val"><?php if(file_exists($hm_signature)){?><img class="sig-img" src="<?=$hm_signature?>?v=<?=time()?>"><?php }else{ echo "Mwesigwa J."; }?></div></div>
</div>
<div class="next-term">NEXT TERM BEGINS: <?=strtoupper($next_term)?></div>
</div>
<div class="footer-bar"></div><div class="footer-motto"><?=$school_motto?></div>
</div>
</body></html>