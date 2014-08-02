<?php
require_once __DIR__ . '/config.php';

// 特定のユーザ名 $user_name について、データを更新する
function update_user( $db_connecton, $user_name )
{
	// ユーザの ID （データベース的な意味で）を取得
	$user_id = $db_connecton->query( 'select id from users where name = "' . $user_name . '"' )->fetch()[0];

	// Codeforces の API を叩いてデータを取得
	// ユーザが存在しないとき、ステータスコード 400 ( bad request ) がきて file_get_contents が false を返す
	if ( !( $api_result = file_get_contents( 'http://codeforces.com/api/user.rating?handle=' . $user_name ) ) )
	{
		return 1;
	}

	// 取得した JSON を PHP で扱える形にデコード
	$json = json_decode( $api_result, true );

	// レーティング推移に関する部分をループ
	foreach ( $json{ 'result' } as $row )
	{
		// アップデート秒と更新後レート
		$sec = $row{ 'ratingUpdateTimeSeconds' };
		$rating = $row{ 'newRating' };

		// データベースに保存
		$db_connecton->query( 'insert into ratings ( user_id, updated_sec, rating ) values ( ' . $user_id . ', ' . $sec . ', ' . $rating . ' )' );
	}

	return 0;
}

// GET パラメータの処理
// JavaScript 側の簡略のために user_name は犠牲になった
$user_name = array_key_exists( 'user_name', $_GET ) ? $_GET{ 'user_name' } : "";
$mode = $_GET{ 'mode' };

// データベースに接続
$db_connecton = new PDO( 'mysql:host='.$config['mysql']['host'].';dbname='.$config['mysql']['dbname'].';', $config['mysql']['user'], $config['mysql']['pass'] );

// mode パラメータにより処理を振り分け
switch ( $mode )
{
case 'json': // グラフ描画用 JSON データのリクエスト
	// JSON データの外郭は配列
	echo '[';

	// カンマ挿入用フラグ
	//ダサい……
	$first_user = true;

	// データベースからユーザ名を最新レート（＝現在レート）が高い順に取得してループ
	for ( $users = $db_connecton->query( 'select * from users u order by ( select rating from ratings where user_id = u.id and updated_sec = ( select max( updated_sec ) from ratings where user_id = u.id ) ) desc;' ); $user = $users->fetch(); )
	{
		// カンマの挿入
		// ダサい……
		if ( $first_user )
		{
			$first_user = false;
		}
		else
		{
			echo ',';
		}

		// 一件のデータについて処理
		echo '{ ';
		echo '"label": "' . $user[ 'name' ] . '", ';
		echo '"data": [';
		$first_data = true;

		// 着目しているユーザについて、レート更新情報を取得
		for ( $ratings = $db_connecton->query( 'select updated_sec as sec, rating from ratings where user_id = ' . $user{ 'id' } ); $data = $ratings->fetch(); )
		{
			// カンマの挿入（ダサい）
			if ( $first_data )
			{
				$first_data = false;
			}
			else
			{
				echo ',';
			}

			// データを表すタプル
			// Flot の都合で秒は 1000 倍
			echo '[ ' . $data{ 'sec' } * 1000 . ', ' . $data{ 'rating' } . ' ]';
		}
		echo '] }';
	}
	echo ']';
	break;
case 'add': // ユーザ追加リクエスト
	// $user_name を追加して update_user を呼び出し
	if ( !$db_connecton->query( 'insert into users ( name ) values ( "' . $user_name . '" )' ) || update_user( $db_connecton, $user_name ) )
	{
		// 失敗したらデータベースから消す
		$db_connecton->query( 'delete from users where name = "' . $user_name . '"' );
		// 以上終了のステータスコードを表示
		echo 1;
	}
	else
	{
		// 正常終了のステータスコードを表示
		echo 0;
	}
	break;
case 'update': // データ更新リクエスト
	// データベースにある全ユーザを取り出してループ
	for ( $users = $db_connecton->query( 'select name from users' ); $user = $users->fetch(); )
	{
		// データ更新
		update_user( $db_connecton, $user{ 'name' } );
		// 5 [times/sec] のアクセス制限があるので 200 [ms] ウェイト
		usleep( 200 * 1000 );
	}
	echo 0;
	break;
case 'reset': // データ消去リクエスト
	// レーティングを消去してからユーザを消去
	$db_connecton->query( 'delete from ratings' );
	$db_connecton->query( 'delete from users' );
	echo 0;
}
?>
