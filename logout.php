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
session_start();
session_destroy();
header('Location: login.php');
exit; 