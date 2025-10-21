<?php
$conn = new mysqli('localhost', 'user', '8C]Cl)r[DA1W3vay', 'test', 3309);
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}
echo "succes";
?>
