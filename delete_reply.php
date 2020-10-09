<?php


define( 'DB_HOST', 'localhost' );
define( 'DB_USER', 'root' );
define( 'DB_PASS', 'root' );
define( 'DB_NAME', 'MyBBS' );


date_default_timezone_set('Asia/Tokyo');


$reply_id = null;
$sql = null;
$res = null;
$message_array = array();
$error_message = array();
$mysqli = new mysqli( DB_HOST, DB_USER, DB_PASS, DB_NAME );



session_start();



if( empty($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true ) {
	header("Location: ./admin.php");
	exit();
}


// 削除したい投稿のデータを取得
if( !empty($_GET['reply_id']) && empty($_POST['reply_id']) ){
	$reply_id = (int)htmlspecialchars($_GET['reply_id'], ENT_QUOTES);


	if( $mysqli->connect_errno ) {
		$error_message[] = 'データベースの接続に失敗しました。　エラー番号 '.$mysqli->connect_errno.' : '.$mysqli->connect_error;
	} else {
		$sql = "SELECT * FROM reply WHERE reply_id = $reply_id";
		$res = $mysqli->query($sql);

		if( $res ) {
			$message_data = $res->fetch_assoc();
		} else {
			header("Location: ./admin.php?page={$_GET['page_id']}");
			exit();
		}

		$mysqli->close();
	}



// データベースから削除
} elseif( !empty($_POST['reply_id']) ) {
	$reply_id = (int)htmlspecialchars($_POST['reply_id'], ENT_QUOTES);


	if( $mysqli->connect_errno ) {
		$error_message[] = 'データベースの接続に失敗しました。　エラー番号 '.$mysqli->connect_errno.' : '.$mysqli->connect_error;
	} else {
		$sql = "DELETE FROM reply WHERE reply_id = $reply_id";
		$res = $mysqli->query($sql);
	}

	$mysqli->close();

	if( $res ) {
		header("Location: ./admin.php?page={$_GET['page_id']}#{$message_id}");
		exit();
	}
}


?>



<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>管理ページ　削除の間</title>
		<link rel="stylesheet" type="text/css" href="index.css">
	</head>


	<body>
		<div class="header">
			<h1 class="title">管理ページ　削除の間</h1>
		</div>


		<p class="text-confirm">以下の投稿を削除します。<br>よろしければ「削除」ボタンを押してください。</p>


		<div class="container">
			<form method="post">
				<div class="form_name">
					<label for="reply_name">投稿者</label>
					<input type="text" id="reply_name" name="reply_name" placeholder="名前を入力してください" value="<?php if( !empty($message_data['reply_name']) ){ echo $message_data['reply_name']; } ?>" disabled>
				</div>
				<div class="form_content">
					<label for="reply_content">本文</label>
					<textarea id="reply_content" name="reply_content" placeholder="投稿したい内容を入力してください" disabled><?php if( !empty($message_data['reply_content']) ){ echo $message_data['reply_content']; } ?></textarea>
				</div>
				<a class="btn_cancel" href="./admin.php?page=<?php echo $_GET['page_id'] ?>#<?php echo $_GET['reply_id'] ?>">キャンセル</a>
				<input type="submit" name="btn_submit" value="削除" id="btn_submit">
				<input type="hidden" name="reply_id" value="<?php echo $message_data['reply_id']; ?>">
			</form>
	    </div>
	</body>
</html>

