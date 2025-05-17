<?php
require_once '../src/auth/Auth.php';
logout();
header("Location: login.php");
exit;
