<?php
include 'includes/functions.php';

session_start();
session_unset();
session_destroy();

redirect('home.php', 'Anda telah logout');
?>