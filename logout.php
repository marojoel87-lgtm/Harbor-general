<?php
require_once 'includes/auth.php';
unset($_SESSION['staff_id'], $_SESSION['staff_name'], $_SESSION['staff_role']);
unset($_SESSION['patient_id'], $_SESSION['patient_name']);
header('Location: index.php');
exit;
