<?php
session_start();
session_destroy();
header('Location: ../accueille/accueilleinterface.php');
exit();
?>