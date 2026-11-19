<?php
// Security: Prevent direct access
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' + (Get-Date -UFormat %a, %d %b %Y %H:%M:%S) + ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
die();
