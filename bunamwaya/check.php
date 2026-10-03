<?php
echo "<h2>Your Real Files:</h2>";
$files = scandir(__DIR__);
foreach($files as $f){
 if(strpos($f,'.php')) echo $f."<br>";
}
?>