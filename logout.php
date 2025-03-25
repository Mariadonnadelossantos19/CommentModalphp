<?php
/*
* logout.php
*
* Simple logout processor
* - Nag-dedelete ng session
* - Nag-redirect sa login page
*
* Konektado sa:
* - comments.php (source ng logout request)
* - login.php (redirect destination)
*/
session_start(); // Nagsisimula ng session para ma-access ito
session_destroy(); // Binubura ang kasalukuyang session at lahat ng data nito
header('Location: login.php'); // Nire-redirect sa login page
exit; // Humihinto ang script 