<?php
$baseFile = "settings_data.json";
$badges = ["badge.png","badge.jpeg","badge.jpg","logo.png"];
$school_badge = "badge.jpeg";
foreach($badges as $b){ if(file_exists($b)){ $school_badge=$b; break; } }

$school_name = "BUNAMWAYA CENTRAL PARENTS' NUR. & PRI. SCH.";

$defaults = [
 'school_name' => $school_name,
 'school_contacts' => "P.O.BOX 9270, Kampala. TEL: +256 772896922 / +256 751431922",
 'school_motto' => '"KNOWLEDGE IS POWER"',
 'school_email' => 'bunamwayacentralparentsschool@gmail.com',
 'at_username' => '',
 'at_api_key' => '',
 'at_sender_id' => 'BUNAMWAYA',
 'sms_enabled' => '0'
];

$settings = $defaults;
if(file_exists($baseFile)){
 $saved = json_decode(file_get_contents($baseFile), true);
 if($saved) $settings = array_merge($defaults, $saved);
}

$message = "";
if($_SERVER['REQUEST_METHOD']=='POST'){
 
 $settings['school_name'] = $_POST['school_name'] ?? $settings['school_name'];
 $settings['school_contacts'] = $_POST['school_contacts'] ?? $settings['school_contacts'];
 $settings['school_motto'] = $_POST['school_motto'] ?? $settings['school_motto'];
 $settings['school_email'] = $_POST['school_email'] ?? $settings['school_email'];
 $settings['at_username'] = trim($_POST['at_username'] ?? '');
 $settings['at_api_key'] = trim($_POST['at_api_key'] ?? '');
 $settings['at_sender_id'] = trim($_POST['at_sender_id'] ?? 'BUNAMWAYA');
 $settings['sms_enabled'] = isset($_POST['sms_enabled']) ? '1' : '0';

 file_put_contents($baseFile, json_encode($settings, JSON_PRETTY_PRINT));
 $message = "Settings saved successfully!";
}
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Settings - Bunamwaya</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial}
body{background:#f0f2f5;padding:20px;display:flex;justify-content:center}
.container{width:100%;max-width:650px;background:#fff;border-radius:12px;border:2px solid #0e4d1b;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.1)}
.header{background:#0e4d1b;color:#fff;padding:16px 20px;display:flex;align-items:center;gap:14px;justify-content:space-between}
.header-left{display:flex;align-items:center;gap:14px}
.header img{width:55px;height:55px;object-fit:contain;background:#fff;border-radius:10px;padding:5px;border:2px solid #ff7a00}
.header h3{font-size:14px;line-height:1.4}
.header a{background:#ff7a00;color:#fff;padding:8px 14px;border-radius:6px;text-decoration:none;font-weight:800;font-size:12px}
.content{padding:20px}
label{font-size:11px;font-weight:900;color:#0e4d1b;display:block;margin-top:14px;margin-bottom:4px}
input[type=text],input[type=email],textarea{width:100%;padding:11px;border:1.5px solid #ccc;border-radius:6px;font-size:13px}
textarea{resize:vertical}
.row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.card{border:1.5px solid #ddd;border-radius:8px;padding:14px;margin-top:16px;background:#f9fdf8}
.card h4{font-size:13px;color:#0e4d1b;margin-bottom:10px;border-bottom:1.5px solid #0e4d1b;padding-bottom:6px}
.checkbox{display:flex;align-items:center;gap:10px;margin-top:10px;background:#fff3e0;padding:10px;border-radius:6px;border:1.5px solid #ff7a00}
.checkbox input{width:18px;height:18px}
.btn{width:100%;padding:13px;border:none;border-radius:8px;font-weight:900;cursor:pointer;margin-top:18px;background:#0e4d1b;color:#fff;font-size:14px}
.alert{padding:12px;background:#d4edda;border:1.5px solid #0e4d1b;border-radius:6px;color:#0e4d1b;font-weight:800;font-size:12px;margin-bottom:14px;text-align:center}
 small{color:#666;font-size:10px}
</style></head><body>
<div class="container">
<div class="header">
<div class="header-left">
<img src="<?=$school_badge?>?v=<?=time()?>" alt="Badge">
<h3><?=$settings['school_name']?><br><small style="color:#ffcc80;">SYSTEM SETTINGS</small></h3>
</div>
<a href="dashboard.php">⬅️ BACK</a>
</div>
<div class="content">
<?php if($message):?><div class="alert">✅ <?=$message?></div><?php endif;?>

<form method="POST">
<div class="card">
<h4>🏫 SCHOOL INFO</h4>
<label>School Name</label><input type="text" name="school_name" value="<?=htmlspecialchars($settings['school_name'])?>">
<label>Contacts</label><input type="text" name="school_contacts" value="<?=htmlspecialchars($settings['school_contacts'])?>">
<label>Motto</label><input type="text" name="school_motto" value="<?=htmlspecialchars($settings['school_motto'])?>">
<label>Email</label><input type="email" name="school_email" value="<?=htmlspecialchars($settings['school_email'])?>">
</div>

<div class="card">
<h4>📱 AFRICA'S TALKING SMS SETTINGS</h4>
<p style="font-size:11px;color:#555;margin-bottom:10px;">Get these from Africa's Talking Dashboard - https://account.africastalking.com</p>

<label>AT Username</label>
<input type="text" name="at_username" value="<?=htmlspecialchars($settings['at_username'] ?? '')?>" placeholder="e.g. sandbox or your username">


<label>API Key</label>
<input type="text" name="at_api_key" value="<?=htmlspecialchars($settings['at_api_key'] ?? '')?>" placeholder="e.g. atsk_xxxxxxxxxxxxxxxxxxxxxxxx">


<label>Sender ID</label>
<input type="text" name="at_sender_id" value="<?=htmlspecialchars($settings['at_sender_id'] ?? 'BUNAMWAYA')?>" placeholder="e.g. BUNAMWAYA">
<small>Must be approved by AT, max 11 chars, e.g. BUNAMWAYA</small>

<div class="checkbox">
<input type="checkbox" name="sms_enabled" value="1" <?= ($settings['sms_enabled']??'0')=='1'?'checked':''?>>
<label style="margin:0;">Enable SMS Sending</label>
</div>
</div>

<button class="btn" type="submit">💾 SAVE SETTINGS</button>

<div style="margin-top:14px;padding:10px;background:#e8f5e9;border-radius:6px;border:1px solid #0e4d1b;font-size:11px">


</div>

</form>
</div>
</div>
</body></html>