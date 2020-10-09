<?php


define( 'DB_HOST', 'localhost' );
define( 'DB_USER', 'root' );
define( 'DB_PASS', 'root' );
define( 'DB_NAME', 'MyBBS' );


define( 'PASSWORD', 'password' );


date_default_timezone_set('Asia/Tokyo');


$now_date = null;
$data = null;
$file_handle = null;
$split_data = null;
$message = array();
$message_array = array();
$success_message = null;
$error_message = array();
$clean = array();
$mysqli = new mysqli( DB_HOST, DB_USER, DB_PASS, DB_NAME );



session_start();



// ログアウト
if( !empty($_GET['btn_logout']) ) {
	unset($_SESSION['admin_login']);
}


// ログインが正しく行えているか確認
if( !empty($_POST['btn_submit']) ) {
	if( !empty($_POST['admin_password']) && $_POST['admin_password'] === PASSWORD ) {
		$_SESSION['admin_login'] = true;
	} else {
		$error_message[] = 'ログインに失敗しました。';
	}
	
}


// データベースから取得する処理
if( $mysqli->connect_errno ) {
	$error_message[] = 'データの読み込みに失敗しました。　エラー番号 '.$mysqli->connect_errno.' : '.$mysqli->connect_error;
} else {
	// 現在のページ数を取得
	if( isset($_GET['page']) ) {
		$page = (int)$_GET['page'];
	} else {
		$page = 1;
	}

	// データベースから取得するデータのスタート位置を計算
	if( $page > 1 ) {
		$start = ($page * 20) - 20;
	} else {
		$start = 0;
	}

	// messageテーブルからデータを取得
	$sql = "SELECT * FROM message ORDER BY id DESC LIMIT {$start}, 20";
	$res = $mysqli->query($sql);

	if( $res ) {
		while( $value = $res->fetch_array(MYSQLI_ASSOC) ) {
			$message_array[] = $value;
		}
	}


	// replyテーブル（返信用のテーブル）からデータを取得
	$sql2 = "SELECT * FROM reply";
	$res2 = $mysqli->query($sql2);

	if( $res2 ) {
		while( $value2 = $res2->fetch_array(MYSQLI_ASSOC) ) {
			$message_array2[] = $value2;
		}
	}
	

	// ページ数を決めるためのデータを取得
	$sql_count = "SELECT COUNT(*) FROM message";

	if( $page_num = $mysqli->query($sql_count) ) {
		$row_cnt = $page_num->fetch_row();
		$pagination = ceil($row_cnt[0] / 20);
	}
	
	$mysqli->close();
}



?>



<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>MyBBS（掲示板）管理ページ</title>
		<link rel="stylesheet" type="text/css" href="index.css">
	</head>


	<body>
		<div class="header">
			<h1 class="title">MyBBS（掲示板）管理ページ</h1>
			<?php if( !empty($_SESSION['admin_login']) ): ?>
				<form method="get" action="">
					<input id="btn_logout" type="submit" name="btn_logout" value="ログアウト">
				</form>
			<?php endif; ?>
		</div>


		<div class="error_message">
			<?php if ( !empty($error_message) ): ?>
				<ul class="error_message">
					<?php foreach ( $error_message as $value ): ?>
						<li>・<?php echo $value; ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>


		<section>
			<?php if( !empty($_SESSION['admin_login']) && $_SESSION['admin_login'] == true ): ?>

			<?php if( !empty($message_array) ): ?>
				<?php foreach ($message_array as $value): ?>
					<article id="<?php echo $value['id'] ?>">
						<div class="info">
							<p><?php echo $value['name']; ?></p>
							<time class="post_date"><?php echo date('/Y年m月d日 H:i', strtotime($value['post_date'])); ?></time>
							<p class="edit_delete_btn">
							<!-- admin.phpにログインした直後はpage_idがないので編集できない -->
							<?php if( !empty($_GET['page']) ): ?>
								<a href="edit.php?page_id=<?php echo $_GET['page'] ?>&message_id=<?php echo $value['id']; ?>">編集</a>
								<a href="delete.php?page_id=<?php echo $_GET['page'] ?>&message_id=<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>">削除</a>
							<!-- admin.phpにログインした直後はpage_idに「1」を入れる -->
							<?php else: ?>
								<a href="edit.php?page_id=1&message_id=<?php echo $value['id']; ?>">編集</a>
								<a href="delete.php?page_id=<?php echo $_GET['page'] ?>&message_id=<?php echo $value['id']; ?>"  id="<?php echo $value['id'] ?>">削除</a>
							<?php endif; ?>
							</p>
						</div>
						<p class="content"><?php echo nl2br($value['content']); ?></p>
						<!-- 返信一覧 --> 
						<div class="reply_list_container">
							<?php foreach ($message_array2 as $value2): ?>
								<?php if( $value['id'] == $value2['connect_id'] ): ?>

										<article class="reply_list" id="<?php echo $value2['reply_id'] ?>">
											<div class="info">
												<p><?php echo $value2['reply_name']; ?></p>
												<time class="reply_date"><?php echo date('/Y年m月d日 H:i', strtotime($value2['reply_date'])); ?></time>


												<p class="edit_delete_btn">
												<!-- admin.phpにログインした直後はpage_idがないので編集できない -->
												<?php if( !empty($_GET['page']) ): ?>
													<a href="edit_reply.php?page_id=<?php echo $_GET['page'] ?>&reply_id=<?php echo $value2['reply_id']; ?>">編集</a>
													<a href="delete_reply.php?page_id=<?php echo $_GET['page'] ?>&reply_id=<?php echo $value2['reply_id'] ?>" id="<?php echo $value2['reply_id'] ?>">削除</a>
												<!-- admin.phpにログインした直後はpage_idに「1」を入れる -->
												<?php else: ?>
													<a href="edit_reply.php?page_id=1&reply_id=<?php echo $value2['reply_id']; ?>">編集</a>
													<a href="delete_reply.php?page_id=<?php echo $_GET['page'] ?>&reply_id=<?php echo $value2['reply_id'] ?>" id="<?php echo $value2['reply_id'] ?>">削除</a>
												<?php endif; ?>
												</p>


											</div>
											<p class="reply_content"><?php echo nl2br($value2['reply_content']) ?></p>
										</article>
									
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					</article>
				<?php endforeach; ?>
				
				<div class="pagination_container">
					<!-- 前へボタン -->
					<?php if( $page !== 1 ): ?>
						<a href="?page=<?php echo $page - 1 ?>" class="prev_btn">前へ</a>
					<?php endif; ?>
					<!-- ページ番号 -->
					<?php for ($i=1; $i <= $pagination ; $i++) { ?>
						<?php if( $page == $i ): ?>
						<a href="" class="current_page"><?php echo $i; ?></a>
						<?php else: ?>
						<a href="?page=<?php echo $i ?>" class="pagination"><?php echo $i; ?></a>
						<?php endif; ?>
					<?php } ?>
					<!-- 次へボタン -->
					<?php $max_page = $i - 1; ?>
					<?php if($page < $max_page): ?>
					<a href="?page=<?php echo $page + 1 ?>" class="next_btn">次へ</a>
					<?php endif; ?>
				</div>

			<?php endif; ?>

			<?php else: ?>
				<div class="container">
					<form method="post">
						<div class="form_login">
							<label for="admin_password">ログインパスワード</label>
							<input id="admin_password" type="password" name="admin_password" value="">
						</div>
						<input type="submit" name="btn_submit" value="ログイン" id="btn_submit">
					</form>
				</div>
			<?php endif; ?>
		</section>
	</body>
</html>

