<?php
require_once dirname(__DIR__).'/config.php';
session_destroy();
redirect(SITE_URL.'/user/login.php');
