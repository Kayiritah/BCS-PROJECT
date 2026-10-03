<?php
$class = $_GET['class']?? ''; $pupilFile = $_GET['pupil']?? '';
$file = "fees_data/$class/$pupilFile.json";
if(!file_exists($file)) die("Fees record not found");
$d = json_decode(file_get_contents($file), true);
$last = end($d['history']);
$badges = ["badge.png","badge.jpeg","badge.jpg","logo.png"]; $school_badge="badge.jpeg"; foreach($badges as $b){ if(file_exists($b)){ $school_badge=$b; break; } }
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Receipt - <?=$d['pupil']?></title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial}
body{background:#d9d9d9;display:flex;justify-content:center;padding:20px}
.receipt{width:85mm;background:#fff;padding:14px 14px;border:2px solid #000;position:relative;overflow:hidden}
.watermark{position:absolute;top:45%;left:50%;transform:translate(-50%,-50%);width:200px;opacity:0.08;pointer-events:none}
.watermark img{width:100%}
.content{position:relative;z-index:1}
.center{text-align:center}.line{border-top:1.5px dashed #000;margin:8px 0}
.badge-wrap{width:65px;height:65px;margin:0 auto 6px auto;border:2px solid #0e4d1b;border-radius:10px;padding:4px;background:#fff}
.badge-wrap img{width:100%;height:100%;object-fit:contain}
h3{font-size:13px;color:#0e4d1b}h4{font-size:11px} p{font-size:10px;margin:3px 0}
.row{display:flex;justify-content:space-between;font-size:11px;margin:4px 0}
.bold{font-weight:900}.stamp{border:3px solid #0e4d1b;color:#0e4d1b;padding:6px 10px;border-radius:8px;font-weight:900;transform:rotate(-15deg);display:inline-block;margin-top:10px;font-size:11px}
.btn{position:fixed;top:15px;right:15px;background:#0e4d1b;color:#fff;padding:12px 18px;border:none;border-radius:8px;font-weight:900;cursor:pointer}
.btn2{position:fixed;top:15px;left:15px;background:#333;color:#fff;padding:12px 18px;border-radius:8px;text-decoration:none;font-weight:900}
@media print{.btn,.btn2{display:none} body{background:#fff;padding:0}.receipt{border:1.5px solid #000;width:100%} @page{size:85mm auto;margin:2mm} *{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important}}
</style></head><body>
<a href="fees.php?class=<?=$class?>" class="btn2">⬅️ BACK</a>
<button class="btn" onclick="window.print()">🖨️ PRINT</button>
<div class="receipt">
<div class="watermark"><img src="<?=$school_badge?>"></div>
<div class="content">
<div class="center">
<div class="badge-wrap"><img src="<?=$school_badge?>?v=<?=time()?>" alt="Badge"></div>
<h3>BUNAMWAYA CENTRAL PARENTS' SCHOOL</h3>
<p>P.O BOX 9270, Kampala<br>TEL: 0772896922 / 0751431922<br><b style="color:#ff7a00;">"KNOWLEDGE IS POWER"</b></p>
<h4 style="margin-top:8px;background:#0e4d1b;color:#fff;padding:5px;border-radius:4px;">FEES RECEIPT</h4>
</div>
<div class="line"></div>
<div class="row"><span>Receipt No:</span><span class="bold"><?=$last['receipt']?></span></div>
<div class="row"><span>Date:</span><span><?=$last['date']?></span></div>
<div class="row"><span>Pupil:</span><span class="bold"><?=strtoupper($d['pupil'])?></span></div>
<div class="row"><span>Class:</span><span class="bold"><?=$d['class']?></span></div>
<div class="row"><span>Term:</span><span><?=$last['term']?></span></div>
<div class="line"></div>
<div class="row"><span>Total Fee:</span><span>UGX <?=number_format($d['total_fee'])?></span></div>
<div class="row"><span>Paid Before:</span><span>UGX <?=number_format($d['total_paid'] - $last['amount'])?></span></div>
<div class="row"><span>This Payment:</span><span class="bold" style="font-size:13px;color:#0e4d1b">UGX <?=number_format($last['amount'])?></span></div>
<div class="row"><span>Method:</span><span><?=$last['method']?></span></div>
<div class="line"></div>
<div class="row"><span>Total Paid:</span><span class="bold">UGX <?=number_format($d['total_paid'])?></span></div>
<div class="row"><span>Balance:</span><span class="bold" style="color:<?=$d['balance']==0?'green':'red'?>">UGX <?=number_format($d['balance'])?></span></div>
<div class="line"></div>
<p>Received By: <b><?=$last['received_by']?></b></p>
<div class="center"><div class="stamp"><?= $d['balance']==0? 'FULLY PAID ✓' : 'PARTLY PAID'?></div></div>
<div class="line"></div>
<p class="center" style="font-size:8.5px">Thank you! This is a system generated receipt<br>Keep it safe | Knowledge is Power</p>
</div>
</div>
</div>
</body></html>