<?php
session_start();

//ログインしているかどうか
if (isset($_SESSION["name"])) {
	$errorMessage = "ログアウトしました";
} else {
	$errorMessage = "タイムアウトしました";
}

//セッション変数のクリア
$_SESSION = array();

//セッションクリア
session_destroy();
?>