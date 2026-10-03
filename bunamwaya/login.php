<?php
require_once 'config.php';
if(isset($_SESSION['user_id'])){ header("Location: dashboard.php"); exit; }
$error='';
if($_SERVER['REQUEST_METHOD']=='POST'){
  $u=trim($_POST['username']); $p=trim($_POST['password']);
  $stmt=$conn->prepare("SELECT * FROM users WHERE username=?");
  $stmt->bind_param("s",$u); $stmt->execute(); $r=$stmt->get_result();
  if($r->num_rows==1){
    $user=$r->fetch_assoc();
    if($p==$user['password']){
      $_SESSION['user_id']=$user['user_id']; $_SESSION['full_name']=$user['full_name']; $_SESSION['role']=$user['role'];
      header("Location: dashboard.php"); exit;
    }else $error="Wrong password!";
  }else $error="User not found!";
}
?>
<!DOCTYPE html><html><head><title>Login - Bunamwaya</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
*{font-family:Inter,sans-serif}
body{margin:0;height:100vh;background:linear-gradient(rgba(8,30,12,0.78),rgba(11,77,30,0.88)),url('images/pupils.jpeg') center/cover no-repeat;display:flex;justify-content:center;align-items:center}
.wrap{display:flex;background:#fff;border-radius:28px;overflow:hidden;box-shadow:0 30px 80px rgba(0,0,0,0.45);width:900px;max-width:96%;min-height:520px}
.left{flex:1.1;position:relative;background:url('images/pupils.jpeg') center/cover no-repeat;color:#fff;padding:35px;display:flex;flex-direction:column;justify-content:space-between}
.left::before{content:'';position:absolute;inset:0;background:linear-gradient(180deg,rgba(11,77,30,0.2),rgba(8,40,15,0.92))}
.left *{position:relative;z-index:2}
.logo-box{width:95px;height:95px;background:#fff;border-radius:50%;padding:7px;border:4px solid #ff7a00;overflow:hidden}
.logo-box img{width:100%;height:100%;object-fit:contain;border-radius:50%}
.motto{background:#ff7a00;padding:6px 14px;border-radius:20px;font-size:11px;font-weight:800;display:inline-block;margin-top:10px}
.right{flex:1;padding:45px 38px;display:flex;flex-direction:column;justify-content:center}
.form-control{border-radius:12px;padding:13px 18px;background:#f4f7f3;border:1px solid #dbe8d9}
.btn-login{background:linear-gradient(135deg,#0b4d1e,#ff7a00);border:none;border-radius:12px;padding:13px;font-weight:800;color:#fff;width:100%}
@media(max-width:768px){.left{display:none}}
</style>
</head><body>
<div class="wrap">
<div class="left">
<div><div class="logo-box"><img src="images/logo.jpeg" alt="logo"></div>
<h2 style="font-weight:800;font-size:26px;line-height:1.1">BUNAMWAYA<br>CENTRAL PARENTS'<br>NUR. & PRI. SCH.</h2>
<div class="motto">KNOWLEDGE IS POWER</div></div>
<div><p style="font-size:13px;opacity:.9">Our happy pupils in Orange & Green dancing with joy!</p><small style="opacity:.7">Bunamwaya, Kampala</small></div>
</div>
<div class="right">
<h4 style="font-weight:800;color:#0b4d1e">Welcome Back!</h4><p style="font-size:13px;color:#888;margin-bottom:25px">Login to manage <b style="color:#ff7a00">students, marks, fees</b></p>
<?php if($error) echo "<div class='alert alert-danger py-2 small'>$error</div>"; ?>
<form method="POST">
<label class="small fw-bold">Username</label><input name="username" value=""class="form-control mb-3" required>
<label class="small fw-bold">Password</label><input type="password" name="password" value="" class="form-control mb-4" required>
<button class="btn-login">LOGIN TO SYSTEM →</button>
</form>
<!--form method="POST">
<label class="small fw-bold">Username</label><input name="username" value="admin">class="form-control mb-3" required>
<label class="small fw-bold">Password</label><input type="password" name="password" value="admin123" class="form-control mb-4" required>
<button class="btn-login">LOGIN TO SYSTEM →</button>
</form-->
<!--p class="small text-muted mt-3 text-center">admin / admin123</p>-->
</div>
</div>
</body></html>