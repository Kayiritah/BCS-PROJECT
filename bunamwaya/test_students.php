<?php
include 'config.php';
echo "DB Connected: YES<br>";
echo "Students count: ";
$c = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc();
echo $c['total']."<br><hr>";

$q = $conn->query("SELECT student_id, first_name, last_name, sex, class_id, photo FROM students LIMIT 5");
while($r=$q->fetch_assoc()){
 echo $r['student_id']." - ".$r['first_name']." ".$r['last_name']." - Class ID: ".$r['class_id']." - Sex: ".$r['sex']."<br>";
 echo "<img src='".$r['photo']."' style='width:60px;height:60px;object-fit:cover;border-radius:50%;' onerror=\"this.style.display='none'\"> <hr>";
}
?>