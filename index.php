<?php


define( 'DB_HOST', 'localhost' );
define( 'DB_USER', 'root' );
define( 'DB_PASS', 'root' );
define( 'DB_NAME', 'MyBBS' );


date_default_timezone_set('Asia/Tokyo');


$now_date = null;
$data = null;
$file_handle = null;
$split_data = null;
$message = array();
$message_array = array();
$message_array2 = array();
$success_message = null;
$error_message = array();
$clean = array();
$mysqli = new mysqli( DB_HOST, DB_USER, DB_PASS, DB_NAME );



session_start();



// データベースへの書き込み処理
if( !empty($_POST['btn_submit']) ) {
	if( empty($_POST['name']) ) {
		$error_message[] = '投稿者名を入力してください。';
	} else {
		$clean['name'] = htmlspecialchars( $_POST['name'], ENT_QUOTES );
		$clean['name'] = preg_replace( '/\\r\\n|\\n|\\r/', '', $clean['name']);
	}


	if( empty($_POST['content']) ) {
		$error_message[] = '本文を入力してください。';
	} else {
		$clean['content'] = htmlspecialchars( $_POST['content'], ENT_QUOTES );
	}


	if ( empty($error_message) ) {
		if( $mysqli->connect_errno ) {
			$error_message[] = '書き込みに失敗しました。 エラー番号 '.$mysqli->connect_errno.' : '.$mysqli->connect_error;
		} else {
			$mysqli->set_charset('utf8');
			$now_date = date("Y-m-d H:i:s");
			$sql = "INSERT INTO message (name, content, post_date) VALUES ( '$clean[name]', '$clean[content]', '$now_date')";
			$res = $mysqli->query($sql);


			if( $res ) {
				$_SESSION['success_message'] = 'メッセージを投稿しました。';
			} else {
				$error_message[] = '書き込みに失敗しました。';
			}

			$mysqli->close();
		}

		header('Location: ./');
		exit();
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
		<title>MyBBS</title>
		<link rel="stylesheet" type="text/css" href="index.css">
		<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
	</head>


	<body>
		<div class="header">
			<h1 class="title">MyBBS（掲示板）</h1>
		</div>


		<div class="success_message">
			<!-- 投稿ボタンが押されていない。かつ投稿成功メッセージがあるか確認する -->
			<?php if( empty($_POST['btn_submit']) && !empty($_SESSION['success_message']) ): ?>
				<p class="success_message">
					<?php echo $_SESSION['success_message']; ?>
				</p>
				<!-- 前回の投稿成功メッセージを再表示させないため -->
				<?php unset($_SESSION['success_message']); ?>
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


		<div class="container">
			<form method="post">
				<div class="form_name">
					<label for="name">投稿者</label>
					<input type="text" id="name" name="name" placeholder="名前を入力してください">
				</div>
				<div class="form_content">
					<label for="content">本文</label>
					<textarea id="content" name="content" placeholder="投稿したい内容を入力してください"></textarea>
				</div>
				<input type="submit" name="btn_submit" value="投稿" id="btn_submit">
			</form>
	    </div>


		<hr>


		<section class="post_list">
			<?php if( !empty($message_array) ): ?>
				<?php foreach ($message_array as $value): ?>
					<article id="<?php echo $value['id'] ?>">
						<!-- 投稿内容 -->
						<div class="info">
							<p><?php echo $value['name']; ?></p>
							<time class="post_date"><?php echo date('/Y年m月d日 H:i', strtotime($value['post_date'])); ?></time>
						</div>
						<p class="content"><?php echo nl2br($value['content']) ?></p>
						<!-- 初めてサイトにアクセスしたときはpage_idがないので返信できない -->
						<?php if( !empty($_GET['page']) ): ?>
							<a href="reply.php?page_id=<?php echo $_GET['page'] ?>&message_id=<?php echo $value['id']; ?>" class="reply"><span class="fa fa-reply"></span> 返信する</a>
						<!-- 初めてサイトにアクセスしたときはpage_idに「1」を入れる -->
						<?php else: ?>
							<a href="reply.php?page_id=1&message_id=<?php echo $value['id']; ?>" class="reply"><span class="fa fa-reply"></span> 返信する</a>
						<?php endif; ?>
						<div class="clear"></div>


						<!-- 返信内容 -->
						<div class="reply_list_container">
							<?php foreach ($message_array2 as $value2): ?>
								<!-- 投稿内容に対して返信があるか確認 -->
								<?php if( $value['id'] == $value2['connect_id'] ): ?>
										<article class="reply_list">
											<div class="info">
												<p><?php echo $value2['reply_name']; ?></p>
												<time class="reply_date"><?php echo date('/Y年m月d日 H:i', strtotime($value2['reply_date'])); ?></time>
											</div>
											<p class="reply_content"><?php echo nl2br($value2['reply_content']) ?></p>
										</article>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					</article>
				<?php endforeach; ?>
			<?php endif; ?>


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
		</section>
	</body>
</html>