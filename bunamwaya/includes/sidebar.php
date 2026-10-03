<?php $cur = basename($_SERVER['PHP_SELF']); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@500;700&display=swap');
.sidebar{width:270px;background:#1e5a2f;height:100vh;position:fixed;left:0;top:0;z-index:9999;overflow-y:auto!important;padding:20px 12px 120px 12px;font-family:'Poppins',sans-serif;}
.sidebar::-webkit-scrollbar{width:6px;} .sidebar::-webkit-scrollbar-thumb{background:#4caf50;border-radius:10px;}
.brand{text-align:center;color:#fff;margin-bottom:20px;border-bottom:1px solid rgba(255,255,255,0.15);padding-bottom:15px;}
.brand .logo{width:60px;height:60px;background:#fff;color:#1e5a2f;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;margin:0 auto 10px;font-size:22px;}
.brand h3{font-size:14px;margin:0;line-height:1.3;} .brand small{color:#a8d5b5;font-size:11px;}
.menu-title{color:#8fd19e;font-size:11px;font-weight:700;padding:18px 10px 6px;text-transform:uppercase;letter-spacing:1px;}
.sidebar ul{list-style:none;padding:0;margin:0;}
.menu-link{display:flex;align-items:center;justify-content:space-between;padding:11px 14px;color:#e0f2e4;text-decoration:none;border-radius:8px;font-size:14px;cursor:pointer;margin-bottom:3px;transition:0.2s;}
.menu-link:hover{background:rgba(255,255,255,0.1);color:#fff;} .menu-link.active{background:#ff7a00;color:#fff;font-weight:700;}
.menu-link .left{display:flex;align-items:center;} .menu-link .left i{width:22px;margin-right:8px;text-align:center;}
.arrow{font-size:11px;transition:0.3s;} .has-sub.open .arrow{transform:rotate(180deg);}
.submenu{display:none;padding-left:12px;margin:4px 0;} .has-sub.open .submenu{display:block;}
.submenu a{display:flex;align-items:center;padding:10px 14px;margin-bottom:4px;background:rgba(0,0,0,0.18);color:#c8e6c9;text-decoration:none;border-radius:7px;font-size:13px;}
.submenu a:hover,.submenu a.active{background:#ff7a00!important;color:#fff!important;}
.main-content{margin-left:270px;}
@media(max-width:768px){.sidebar{position:relative;width:100%;height:auto;}.main-content{margin-left:0;}}
</style>

<div class="sidebar">
  <div class="brand">
    <div class="logo">BU</div>
    <h3>BUNAMWAYA CENTRAL<br>PARENTS SCHOOL</h3>
    <small>P.O BOX 9270 Kampala</small>
  </div>

  <ul>
    <li class="menu-title">MAIN</li>
    <li><a class="menu-link <?=$cur=='dashboard.php'?'active':''?>" href="dashboard.php"><span class="left"><i class="fa-solid fa-house"></i> Dashboard</span></a></li>

    <li class="menu-title">PUPILS</li>
    <li class="has-sub open">
      <div class="menu-link" onclick="this.parentElement.classList.toggle('open')"><span class="left"><i class="fa-solid fa-users"></i> Pupils</span> <i class="fa-solid fa-chevron-down arrow"></i></div>
      <div class="submenu">
        <a class="<?=$cur=='students.php'?'active':''?>" href="students.php"><i class="fa-solid fa-user-plus" style="margin-right:8px"></i> Admission</a>
        <a class="<?=$cur=='students_records.php'?'active':''?>" href="students_records.php"><i class="fa-solid fa-list" style="margin-right:8px"></i> Master Records by Class</a>
        <a class="<?=$cur=='students_list.php'?'active':''?>" href="students_list.php"><i class="fa-solid fa-id-card" style="margin-right:8px"></i> Students List + Photo</a>
      </div>
    </li>

    <li class="menu-title">ACADEMICS (P.4-P.7)</li>
    <li class="has-sub open">
      <div class="menu-link" onclick="this.parentElement.classList.toggle('open')"><span class="left"><i class="fa-solid fa-book-open"></i> Academics</span> <i class="fa-solid fa-chevron-down arrow"></i></div>
      <div class="submenu">
        <a class="<?=$cur=='marks.php'?'active':''?>" href="marks.php">Enter CA 1-6</a>
        <a class="<?=$cur=='ca_report.php'?'active':''?>" href="ca_report.php">Class CA Report + Ranking</a>
        <a class="<?=$cur=='pupil_report.php'?'active':''?>" href="pupil_report.php">Pupil Report Card</a>
        <a class="<?=$cur=='report_manager.php'?'active':''?>" href="report_manager.php">Report Manager [NEW]</a>
      </div>
    </li>

    <li class="menu-title">ATTENDANCE</li>
    <li class="has-sub open">
      <div class="menu-link" onclick="this.parentElement.classList.toggle('open')"><span class="left"><i class="fa-solid fa-calendar-check"></i> Attendance</span> <i class="fa-solid fa-chevron-down arrow"></i></div>
      <div class="submenu">
        <a class="<?=$cur=='attendance.php'?'active':''?>" href="attendance.php">Student Attendance</a>
        <a class="<?=$cur=='attendance_report.php'?'active':''?>" href="attendance_report.php">Attendance Summary</a>
      </div>
    </li>

    <li class="menu-title">STAFF & FINANCE</li>
    <li class="has-sub open">
      <div class="menu-link" onclick="this.parentElement.classList.toggle('open')"><span class="left"><i class="fa-solid fa-user-tie"></i> Staff & Finance</span> <i class="fa-solid fa-chevron-down arrow"></i></div>
      <div class="submenu">
        <a class="<?=$cur=='staff.php'?'active':''?>" href="staff.php">Staff Management</a>
        <a class="<?=$cur=='fees.php'?'active':''?>" href="fees.php">Fees Collection</a>
        <a class="<?=$cur=='users.php'?'active':''?>" href="users.php">Users</a>
        <a class="<?=$cur=='settings.php'?'active':''?>" href="settings.php">Settings</a>
      </div>
    </li>

    <li style="margin-top:20px"><a class="menu-link" href="logout.php" style="color:#ffb3b3"><span class="left"><i class="fa-solid fa-right-from-bracket"></i> Logout</span></a></li>
  </ul>
  <div style="height:80px;"></div>
</div>

<script>
document.querySelectorAll('.has-sub').forEach(function(el){
  if(el.querySelector('.active')) el.classList.add('open');
});
</script>