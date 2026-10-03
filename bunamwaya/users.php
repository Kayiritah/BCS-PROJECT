<?php
session_start();
$baseDir = "users_data/"; if(!is_dir($baseDir)) mkdir($baseDir,0777,true);
$file = $baseDir."users.json";
$badges = ["badge.png","badge.jpeg","badge.jpg","logo.png"]; $school_badge="badge.jpeg"; foreach($badges as $b){ if(file_exists($b)){ $school_badge=$b; break; } }
$school_name = "BUNAMWAYA CENTRAL PARENTS' NUR. & PRI. SCH.";

$roles = ["Admin","Head Teacher","Bursar","Teacher","Secretary"];
if(!file_exists($file)){
 $default = [
  ['id'=>1,'name'=>'Admin','username'=>'admin','password'=>password_hash('admin123', PASSWORD_DEFAULT),'role'=>'Admin','phone'=>'0772896922','email'=>'bunamwayacentralparentsschool@gmail.com','date'=>date('Y-m-d H:i:s')],
 ];
 file_put_contents($file, json_encode($default, JSON_PRETTY_PRINT));
}
$users = json_decode(file_get_contents($file), true);

$message = ""; $error = "";
// ADD / EDIT
if($_SERVER['REQUEST_METHOD']=='POST'){
 $id = $_POST['id']?? ''; $name = trim($_POST['name']); $username = trim($_POST['username']); $role = $_POST['role'];
 $phone = trim($_POST['phone']); $email = trim($_POST['email']); $password = $_POST['password'];

 // Check duplicate username
 foreach($users as $u){ if($u['username']==$username && $u['id']!=$id){ $error="Username '$username' already exists!"; goto end_post; } }

 if($id){ // EDIT
  foreach($users as &$u){ if($u['id']==$id){
   $u['name']=$name; $u['username']=$username; $u['role']=$role; $u['phone']=$phone; $u['email']=$email;
   if($password) $u['password']=password_hash($password, PASSWORD_DEFAULT);
   $message="User $name updated!";
   break;
  }}
 } else { // ADD
  $new = ['id'=>time(),'name'=>$name,'username'=>$username,'password'=>password_hash($password, PASSWORD_DEFAULT),'role'=>$role,'phone'=>$phone,'email'=>$email,'date'=>date('Y-m-d H:i:s')];
  $users[]=$new;
  $message="User $name added! Username: $username";
 }
 file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));
 end_post:
}

// DELETE
if(isset($_GET['delete'])){
 $delId = $_GET['delete'];
 if(count($users)<=1){ $error="Cannot delete last user!"; }
 else {
  $users = array_filter($users, fn($u)=> $u['id']!=$delId);
  file_put_contents($file, json_encode(array_values($users), JSON_PRETTY_PRINT));
  $message="User deleted!";
  $users = json_decode(file_get_contents($file), true);
 }
}

// EDIT LOAD
$editUser = null;
if(isset($_GET['edit'])){ foreach($users as $u){ if($u['id']==$_GET['edit']){ $editUser=$u; break; } } }
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Users - <?=$school_name?></title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial}
body{background:#f0f2f5;padding:15px;display:flex;gap:18px;justify-content:center}
.form-box{width:380px;background:#fff;padding:18px;border-radius:12px;border:2px solid #0e4d1b;height:fit-content;position:sticky;top:15px;box-shadow:0 4px 12px rgba(0,0,0,0.1)}
.school-head{display:flex;align-items:center;gap:12px;margin-bottom:14px;padding-bottom:12px;border-bottom:2px solid #0e4d1b}
.school-head img{width:60px;height:60px;object-fit:contain;border:2px solid #0e4d1b;border-radius:10px;padding:4px;background:#fff}
.school-head h4{font-size:12px;color:#0e4d1b;line-height:1.4}
.form-box h3{background:#0e4d1b;color:#fff;text-align:center;padding:12px;border-radius:8px;margin-bottom:14px;font-size:13px}
.form-box label{font-size:11px;font-weight:900;color:#0e4d1b;display:block;margin-top:8px}
.form-box input,.form-box select{width:100%;padding:10px;margin:5px 0 10px 0;border:1.5px solid #ccc;border-radius:6px;font-size:13px}
.row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.btn{width:100%;padding:12px;border:none;border-radius:8px;font-weight:900;cursor:pointer;margin-top:8px}
.btn-save{background:#0e4d1b;color:#fff}.btn-cancel{background:#666;color:#fff;display:block;text-align:center;text-decoration:none;padding:10px;border-radius:8px;margin-top:8px;font-size:12px}
.btn-back{background:#333;color:#fff;display:block;text-align:center;text-decoration:none;padding:10px;border-radius:8px;margin-bottom:8px;font-size:12px}
.alert{padding:10px;border-radius:6px;font-size:12px;font-weight:800;margin-bottom:12px}
.alert-success{background:#d4edda;border:1.5px solid #0e4d1b;color:#0e4d1b}
.alert-error{background:#f8d7da;border:1.5px solid #c00;color:#c00}
.main{flex:1;max-width:850px;background:#fff;border-radius:12px;padding:18px;border:2px solid #0e4d1b}
table{width:100%;border-collapse:collapse;font-size:12px;margin-top:10px}
th,td{border:1.5px solid #ddd;padding:9px;text-align:left}
th{background:#0e4d1b;color:#fff;font-size:11px}
tr:nth-child(even){background:#f8fdf6}
.role{padding:3px 8px;border-radius:10px;font-size:10px;font-weight:800;color:#fff;display:inline-block}
.role-Admin{background:#000}.role-Head{background:#0e4d1b}.role-Bursar{background:#ff7a00}.role-Teacher{background:#1976d2}.role-Secretary{background:#7b1fa2}
.action a{padding:5px 8px;border-radius:4px;text-decoration:none;font-size:11px;font-weight:800;margin-right:4px}
.edit{background:#e8f5e9;color:#0e4d1b;border:1px solid #0e4d1b}.del{background:#fde8e8;color:#c00;border:1px solid #c00}
.search{width:100%;padding:10px;border:2px solid #0e4d1b;border-radius:8px;margin-bottom:12px;font-size:13px}
</style></head><body>

<div class="form-box">
<div class="school-head">
<img src="<?=$school_badge?>?v=<?=time()?>" alt="Badge">
<div><h4><?=$school_name?><br><small style="color:#ff7a00;">"KNOWLEDGE IS POWER"</small><br><small>USER MANAGEMENT</small></h4></div>
</div>
<a href="dashboard.php" class="btn-back">⬅️ DASHBOARD</a>
<a href="fees.php" class="btn-back" style="background:#ff7a00;">💰 FEES</a>
<a href="report_manager.php" class="btn-back" style="background:#0e4d1b;">📂 REPORTS</a>

<h3><?= $editUser? '✏️ EDIT USER' : '➕ ADD NEW USER'?></h3>
<?php if($message):?><div class="alert alert-success"><?=$message?></div><?php endif;?>
<?php if($error):?><div class="alert alert-error"><?=$error?></div><?php endif;?>

<form method="POST">
<input type="hidden" name="id" value="<?=$editUser['id']??''?>">
<label>Full Name</label><input type="text" name="name" value="<?=$editUser['name']??''?>" required placeholder="e.g. Mwesigwa John">
<label>Username (for login)</label><input type="text" name="username" value="<?=$editUser['username']??''?>" required placeholder="e.g. hm_john">
<div class="row">
<div><label>Role</label><select name="role" required>
<?php foreach($roles as $r):?><option value="<?=$r?>" <?= ($editUser['role']??'')==$r?'selected':''?>><?=$r?></option><?php endforeach;?>
</select></div>
<div><label>Phone</label><input type="text" name="phone" value="<?=$editUser['phone']??''?>" placeholder="07..."></div>
</div>
<label>Email</label><input type="email" name="email" value="<?=$editUser['email']??''?>" placeholder="email@...">
<label>Password <?= $editUser? '(leave blank to keep old)' : ''?></label><input type="password" name="password" <?= $editUser? '' : 'required'?> placeholder="Min 6 chars">
<button class="btn btn-save" type="submit"><?= $editUser? '💾 UPDATE USER' : '💾 ADD USER'?></button>
<?php if($editUser):?><a href="users.php" class="btn-cancel">❌ CANCEL EDIT</a><?php endif;?>
</form>

<div style="background:#fff7ed;padding:10px;border-radius:6px;margin-top:12px;border:1.5px solid #ff7a00;font-size:11px">
<b>Roles:</b><br>
• <b>Admin</b> - Full access<br>
• <b>Head Teacher</b> - Reports & comments<br>
• <b>Bursar</b> - Fees only<br>
• <b>Teacher</b> - Enter marks<br>
• <b>Secretary</b> - Pupils & reports<br>
Default login: admin / admin123
</div>
</div>

<div class="main">
<h3 style="color:#0e4d1b;margin-bottom:12px;">👥 SYSTEM USERS (<?=count($users)?>)</h3>
<input type="text" id="searchInput" class="search" placeholder="🔍 Search name, username, role..." onkeyup="searchTable()">
<table id="usersTable">
<tr><th>#</th><th>Name / Username</th><th>Role</th><th>Contact</th><th>Date Added</th><th>Action</th></tr>
<?php $i=1; foreach($users as $u):?>
<tr>
<td><?=$i++?></td>
<td><b><?=$u['name']?></b><br><small style="color:#0e4d1b;">@<?=$u['username']?></small></td>
<td><span class="role role-<?=explode(' ',$u['role'])[0]?>"><?=$u['role']?></span></td>
<td><small><?=$u['phone']?><br><?=$u['email']?></small></td>
<td><small><?=$u['date']?></small></td>
<td class="action">
<a href="?edit=<?=$u['id']?>" class="edit">✏️ EDIT</a>
<a href="?delete=<?=$u['id']?>" class="del" onclick="return confirm('Delete <?=$u['name']?>?')">🗑️ DEL</a>
</td>
</tr>
<?php endforeach;?>
</table>
<div style="margin-top:14px;padding:12px;background:#f8fdf6;border:1.5px solid #0e4d1b;border-radius:8px;font-size:11px">
<b>Security:</b> Passwords are hashed. File saved at <code>users_data/users.json</code><br>
Change default admin password after first login!
</div>
</div>

<script>
function searchTable(){ let input=document.getElementById('searchInput').value.toLowerCase(); document.querySelectorAll('#usersTable tr').forEach((r,i)=>{ if(i==0) return; r.style.display=r.innerText.toLowerCase().includes(input)?'':'none'; }); }
</script>
</body></html>