<?php
include "parametros.php";

$pdo=new PDO($dns,$userName,$password);

return $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);


?>
