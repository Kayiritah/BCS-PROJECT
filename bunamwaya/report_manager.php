<?php
$baseDir = "report_cards/"; if(!is_dir($baseDir)) mkdir($baseDir,0777,true);
$classes = []; foreach(scandir($baseDir) as $d){ if($d!='.' && $d!='..' && is_dir($baseDir.$d)) $classes[]=$d; }
$selectedClass = $_GET['class']?? ($classes[0]?? ''); $reports=[];
if($selectedClass && is_dir($baseDir.$selectedClass)){ foreach(glob($baseDir.$selectedClass."/*.json") as $f){ $data=json_decode(file_get_contents($f),true); if($data) $reports[]=$data; } }
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Report Manager</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial} body{background:#f0f2f5;padding:20px}
.header{background:#0e4d1b;color:#fff;padding:16px 20px;border-radius:10px;display:flex;justify-content:space-between;align-items:center}
.header a{background:#ff7a00;color:#fff;padding:9px 16px;border-radius:6px;text-decoration:none;font-weight:800}
.class-tabs{display:flex;gap:10px;margin:20px 0;flex-wrap:wrap}
.tab{padding:10px 18px;background:#fff;border:2px solid #0e4d1b;border-radius:20px;font-weight:800;text-decoration:none;color:#0e4d1b}
.tab.active,.tab:hover{background:#0e4d1b;color:#fff}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px}
.card{background:#fff;border-radius:10px;padding:14px;border:1.5px solid #ddd;box-shadow:0 2px 6px rgba(0,0,0,0.08)}
.card-top{display:flex;gap:12px;align-items:center}
.card-top img{width:60px;height:70px;border-radius:6px;object-fit:cover;border:2px solid #0e4d1b}
.badge{padding:3px 8px;border-radius:10px;font-size:11px;font-weight:800}.badge-total{background:#ff7a00;color:#fff}.badge-div{background:#0e4d1b;color:#fff}
.actions{margin-top:10px;display:flex;gap:8px}.actions a{flex:1;text-align:center;padding:7px;border-radius:5px;text-decoration:none;font-size:12px;font-weight:800}
.view{background:#e8f5e9;color:#0e4d1b;border:1.5px solid #0e4d1b}.print{background:#0e4d1b;color:#fff}
.empty{text-align:center;padding:60px;background:#fff;border-radius:10px;color:#777}
</style></head><body>
<div class="header"><div><h2>📂 REPORT MANAGER </h2><small></small></div><div><a href="manual_report.php">➕ NEW REPORT</a> <a href="dashboard.php" style="background:#333;margin-left:8px;">⬅️ BACK</a></div></div>
<div class="class-tabs">
<a href="report_manager.php" class="tab <?= $selectedClass==''?'active':''?>">ALL (<?=count($classes)?>)</a>
<?php foreach($classes as $c):?><a href="?class=<?=$c?>" class="tab <?= $selectedClass==$c?'active':''?>"><?=$c?> (<?=count(glob($baseDir.$c."/*.json"))?>)</a><?php endforeach;?>
</div>
<?php if(empty($reports) && $selectedClass==''):?>
<?php if(empty($classes)):?><div class="empty"><h3>No reports yet</h3><p>Create in Manual Report - auto saves by class</p><br><a href="manual_report.php" style="background:#0e4d1b;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;">Create Report</a></div>
<?php else:?>
<div class="grid"><?php foreach($classes as $c): $count=count(glob($baseDir.$c."/*.json"));?><div class="card" style="text-align:center;background:#0e4d1b;color:#fff"><h4 style="color:#fff;font-size:18px"><?=$c?></h4><p style="color:#fff;opacity:0.8"><?=$count?> Reports</p><div class="actions"><a href="?class=<?=$c?>" class="view" style="background:#ff7a00;color:#fff;border:none;">OPEN CLASS</a></div></div><?php endforeach;?></div>
<?php endif;?>
<?php elseif(empty($reports)):?><div class="empty"><h3>No reports in <?=$selectedClass?></h3></div>
<?php else:?>
<d
iv style="margin-bottom:12px;font-weight:800;color:#0e4d1b;">📚 <?=$selectedClass?> - <?=count($reports)?> Reports</div>
<div class="grid"><?php foreach($reports as $r):?><div class="card"><div class="card-top"><img src="<?=$r['photo']?>"><div><h4 style="font-size:13px;color:#0e4d1b"><?=strtoupper($r['pupil'])?></h4><p style="font-size:11px;">Class: <?=$r['class']?> | Term: <?=$r['term']?></p><p><span class="badge badge-total">Total: <?=$r['total']?></span> <span class="badge badge-div">Div <?=$r['div']?></span> <span style="font-size:11px;">Avg <?=$r['avg']?>%</span></p></div></div><div class="actions"><a href="print_single.php?class=<?=$selectedClass?>&pupil=<?=preg_replace('/[^A-Za-z0-9]/','_', $r['pupil'])?>" class="print">🖨️ PRINT</a><a href="report_manager.php?class=<?=$selectedClass?>&delete=<?=preg_replace('/[^A-Za-z0-9]/','_', $r['pupil'])?>" class="view" style="color:red;">🗑️ DELETE</a></div></div><?php endforeach;?></div>
<?php endif;?>
</body></html>
<?php
if(isset($_GET['delete']) && $selectedClass){ $delFile = $baseDir.$selectedClass."/".$_GET['delete'].".json"; if(file_exists($delFile)) unlink($delFile); echo "<script>window.location='report_manager.php?class=$selectedClass';</script>"; }
?>