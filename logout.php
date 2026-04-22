<?php
require_once __DIR__ . '/includes/init.php';
logout_user();
session_start();
set_flash('success', 'You have been logged out successfully.');
redirect('login.php');
