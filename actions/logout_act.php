<?php
session_start();
session_unset();
session_destroy();

// Balikin ke halaman login
header("Location: ../index.php");
exit;
