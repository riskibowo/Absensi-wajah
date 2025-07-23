<?php
require_once '../db_connect.php';
session_destroy();
echo json_encode(['status' => 'success', 'message' => 'Sesi berhasil di-reset.']);
?>