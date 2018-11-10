<?php
session_start(); //セッションの開始
setcookie("jeff", "", time() + 604800); //クッキーの有効期限を7日間に設定

$dsn = "hoge" ;
$db[user] = "hoge" ;
$db[pass] = "hoge" ;

//ログインしているかどうか
if (!isset($_SESSION["name"])) {
	header("Location: login.php");
	exit;
}

// エラーメッセージ、成功メッセージの初期化
$errorMessage = "";
$successMessage = "";

//半角英数字をそれぞれ1種類以上含む8文字以上100文字以下の正規表現
$pattern = '/\A(?=.*?[a-z])(?=.*?\d)[a-z\d]{8,100}+\z/i';

//セッションからユーザー名を取る
$username = $_SESSION["name"];

try {
	$pdo = new PDO($dsn, $db[user], $db[pass]);
	$sql = "SELECT * FROM users WHERE name = '$username'";
	$row = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);

	$email = $row['email'];
	$password = $row['password'];

	//ユーザー名・メールアドレス編集機構
	if (!empty($_POST["edit02"])) { //ユーザー名・メールアドレスの送信があったら
		if (empty($_POST["newusername"])) {
			$errorMessage = "新しいユーザー名を記入してください";
			//submitには連想配列が入る=配列を入れればクリックしたのと同じ
			//!empty()で確かめる場合、配列になにか入れないと空とみなされる
			$_POST["edit01"] = array(1);
		} elseif (empty($_POST["newemail"])) {
			$errorMessage = "新しいメールアドレスを記入してください";
			$_POST["edit01"] = array(1);
		} elseif (empty($_POST["password"])) {
			$errorMessage = "現在のパスワードを記入してください";
			$_POST["edit01"] = array(1);
		}
		
		if (!empty($_POST["newusername"]) and !empty($_POST["newemail"]) and !empty($_POST["password"])) {
			$newname = $_POST["newusername"];
			$newemail = $_POST["newemail"];
			$hashpass = hash("sha256", $_POST["password"]);
			
			if ($password == $hashpass) {
				//ユーザーネームの重複を確認
				$stmt = "SELECT * FROM users WHERE name = '$newname'";
				$count = (int)$pdo->query($stmt)->fetchColumn();
				
				if ($count > 0 and $username !== $newname) { //重複かつ違うユーザー名を指定した場合
					$errorMessage = 'そのユーザー名は既に使用されています';
					$_POST["edit01"] = array(1);
				} else {
					// 編集させる
					$sql = "update users set name='$newname', email='$newemail' where name='$username'";
					$result = $pdo->query($sql);

					//現在のセッションIDを新しく生成したものと置き換える。セキュリティ上重要
					session_regenerate_id(true);

					$_SESSION["name"] = $newname;
					$username = $_SESSION["name"];
					$email = $newemail;
					$successMessage = "正しく変更されました！";
				}
			} else {
				$errorMessage = "パスワードが違います";
				$_POST["edit01"] = array(1);
			}
		}
	}

	//パスワード編集機構
	if (!empty($_POST["passedit02"])) { //パスワードの送信があったら
		if (empty($_POST["password"])) {
			$errorMessage = "現在のパスワードを記入してください";
			$_POST["passedit01"] = array(1);
		} elseif (empty($_POST["newpassword"])) {
			$errorMessage = "新しいパスワードを記入してください";
			$_POST["passedit01"] = array(1);
		} elseif (!preg_match($pattern, $_POST["newpassword"])) { //パスワードがパターンに一致しない場合
			$errorMessage = "パスワードは8文字以上で、半角英字と半角数字をそれぞれ最低1つ含む必要があります";
			$_POST["passedit01"] = array(1);
		}
		
		if (!empty($_POST["password"]) and !empty($_POST["newpassword"]) and preg_match($pattern, $_POST["newpassword"])) {
			$newpass = $_POST["newpassword"];
			$hashpass = hash("sha256", $_POST["password"]);
			
			if ($password == $hashpass) {
				// 編集させる
				$new_hashpass = hash("sha256", $newpass);
				$sql = "update users set password='$new_hashpass' where name='$username'";
				$result = $pdo->query($sql);

			    //ページを遷移しない場合の処理
			    //現在のセッションIDを新しく生成したものと置き換える。セキュリティ上重要
				session_regenerate_id(true);
				
				$_SESSION["name"] = $username;
				$successMessage = "正しく変更されました！";
				

			   /**ページを遷移する場合の処理(強制ログアウト)
				*$successMessage = "正しく変更されました！";
				*$_SESSION = array();
				*session_destroy();
				*header("Location: login.php");
				*exit;
				*/
			} else {
				$errorMessage = "パスワードが違います";
				$_POST["passedit01"] = array(1);
			}
		}
	}

} catch (PDOException $e) {
	$errorMessage = 'データベースエラー';
	// $e->getMessage() でエラー内容を参照可能
	// echo $e->getMessage();
} //try終了



?>

<!doctype html>
<html>
    <head>
		<meta charset="UTF-8">
        <title>プロフィール</title>
    </head>
    <body>
        <h1>プロフィール</h1>
        <form id="loginForm" name="loginForm" action="" method="POST">
            <fieldset>
                <legend>プロフィールと編集</legend>
				パスワードはハッシュ化されていますが、あまり安全ではありません。重要な文字列は入力しないでください。
				<br>
				パスワードは8文字以上で、半角英字と半角数字をそれぞれ最低1つ含む必要があります。
				<br>
				メールアドレスは認証がないので、適当で大丈夫です。
				<br>
                <label for="username">ユーザー名</label>: <?php echo htmlspecialchars($username, ENT_QUOTES); ?>
				<br>
                <label for="emaile">メールアドレス</label>: <?php echo htmlspecialchars($email, ENT_QUOTES); ?>
				<br>
                <label for="password">パスワード</label>: ****** (セキュリティのため表示されません)
				<br>
                <input type="submit" id="edit01" name="edit01" value="ユーザー名・メールアドレスを編集する">
                <input type="submit" id="passedit01" name="passedit01" value="パスワードを編集する">
				<br>
                <div><font color="red"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES); ?></font></div>
                <div><font color="blue"><?php echo htmlspecialchars($successMessage, ENT_QUOTES); ?></font></div>
				<?php if (!empty($_POST['edit01'])) { ?>
					<label for="newusername">新しいユーザーネーム</label> <input type="text" size="30" id="newusername" name="newusername" placeholder="新しいユーザー名を入力" value="<?php echo htmlspecialchars($username, ENT_QUOTES); ?>">
					<br>
					<label for="newemail">新しいメールアドレス</label> <input type="text" size="30" id="newemail" name="newemail" placeholder="新しいメールアドレスを入力" value="<?php echo htmlspecialchars($email, ENT_QUOTES); ?>">
					<br>
					<label for="password">現在のパスワード</label><input type="password" size="30" id="password" name="password" value="" placeholder="現在のパスワードを入力">
					<br>
					<input type="submit" id="edit02" name="edit02" value="送信する">
					<input type="submit" id="cancel" name="cancel" value="キャンセル">
				<?php } if (!empty($_POST['passedit01'])) { ?>
					<label for="password">現在のパスワード</label><input type="password" size="30" id="password" name="password" value="" placeholder="現在のパスワードを入力">
					<br>
					<label for="newpassword">新しいのパスワード</label><input type="password" size="30" id="newpassword" name="newpassword" value="" placeholder="新しいパスワードを入力">
					<br>
					<input type="submit" id="passedit02" name="passedit02" value="送信する">
					<input type="submit" id="cancel" name="cancel" value="キャンセル">
				<?php } ?>
            </fieldset>
        </form>
        <br>
        <form action="index.php">
            <input type="submit" value="トップページへ戻る">
        </form>
    </body>
</html>