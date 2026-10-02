<?php
require 'functions.php';
header('Location: ' . (isLoggedIn() ? 'dashboard.php' : 'login.php'));
exit;