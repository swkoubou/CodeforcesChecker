// グラフ背景の色分けデータ
// Codeforces 本家から拝借
markings = [
	{ color: '#f33', lineWidth: 1, yaxis: { from: 2600 } },
	{ color: '#f77', lineWidth: 1, yaxis: { from: 2200, to: 2599 } },
	{ color: '#ffbb55', lineWidth: 1, yaxis: { from: 2050, to: 2199 } },
	{ color: '#ffcc88', lineWidth: 1, yaxis: { from: 1900, to: 2049 } },
	{ color: '#f8f', lineWidth: 1, yaxis: { from: 1700, to: 1899 } },
	{ color: '#aaf', lineWidth: 1, yaxis: { from: 1500, to: 1699 } },
	{ color: '#7f7', lineWidth: 1, yaxis: { from: 1350, to: 1499 } },
	{ color: '#afa', lineWidth: 1, yaxis: { from: 1200, to: 1349 } },
	{ color: '#ccc', lineWidth: 1, yaxis: { from: 0, to: 1199 } },
];

// グラフ描画の関数
function render()
{
	// API を非同期で叩いてデータを取得
	$.getJSON( "./db_manipulate.php", { mode: "json" }, function( data ){

		// グラフの上下境界を求める
		// 生ループダサい……
		var min = 3000, max = 0;
		for ( var i in data ){
			for ( var j in data[i][ 'data' ] ){
				var rating = data[i][ 'data' ][j][1];
				min = Math.min( min, rating );
				max = Math.max( max, rating );
			}
		}
		margin = 50;

		// グラフ描画のためのオプション
		// ほぼ本家から拝借
		var options = {
			lines: { show: true },
			points: { show: true },
			xaxis: { mode: "time" },
			yaxis: { min: min - margin, max: max + margin, ticks: [1200, 1350, 1500, 1700, 1900, 2050, 2200, 2600] },
			grid: { hoverable: true, markings: markings },
			legend: { position: "nw" }
		};
	
		// グラフ描画
		$.plot( $( "#graph" ), data, options );
	} );
}

// ユーザ追加ボタンの onClick イベントハンドラ
function add_user()
{
	// 押されたボタン
	var button = $( "#button-add" );

	// 結果表示をクリア
	$( ".ajax-result" ).empty();

	// 処理の間ローディング画像を表示しておく
	$( "#add-user-result" ).html( '<img src="./img/loading.gif">' );

	// テキストボックスの内容取得
	var user_name = $( "#user-name" ).val();
	if ( user_name == "" ) // 何も入力ｓれていなければ入力を促す
	{
		$( "#add-user-result" ).html( '<p class="alert alert-warning">コンテスタント名を入力してください。</p>' );
		return;
	}

	// 追加処理に先立ってボタンを DISABLED にしてイベントハンドラを外す
	button.addClass( "disabled" );
	button.removeAttr( "onClick" );

	// API を非同期で叩いてデータベースを操作させる
	$.get( "./db_manipulate.php", { user_name: user_name, mode: "add" }, function( result ){
		// この時点で処理が完了しているのでボタンを押せるようにする
		button.removeClass( "disabled" );
		button.attr( { onClick: "add_user()" } );

		if ( result == "0" ){ // 処理成功なのでその旨を表示
			$( "#add-user-result" ).html( '<p class="alert alert-success">コンテスタント ' + user_name + " を登録しました。</p>" );
		} else { // 処理に失敗しているのでその旨を表示
			$( "#add-user-result" ).html( '<p class="alert alert-danger">コンテスタント追加に失敗しました。</p>' );
		}
		// ちなみに、ローディング画像が表示されている div の中身を差し替えているのでローディング画像も消える

		// テキストボックスを空にする
		$( "#user-name" ).val( "" );

		// グラフを再描画
		render();
	} );
};

// ユーザ名入力用テキストボックスの onKeyDown イベントハンドラ
function add_user_enter( event )
{
	if ( event.keyCode == 13 ) // キーコード 13 は Enter キー
	{
		// ユーザ追加ボタンのイベントハンドラを直接叩く
		add_user();
	}
}

// データ更新ボタンのイベントハンドラ
// add_user() とほぼ同じ
function update()
{
	var button = $( "#button-update" );

	$( ".ajax-result" ).empty();

	button.addClass( "disabled" );
	button.removeAttr( "onClick" );

	$( "#update-result" ).html( '<img src="./img/loading.gif">' );
	$.get( "./db_manipulate.php", { mode: "update" }, function( result ){
		button.removeClass( "disabled" );
		button.attr( { onClick: "update()" } );

		if ( result == "0" ){
			$( "#update-result" ).html( '<p class="alert alert-success">データを更新しました。</p>' );
		} else {
			$( "#update-result" ).html( '<p class="alert alert-danger">データ更新に失敗しました。</p>' );
		}
		render();
	} );
}

// データ消去ボタン（モーダルにある方）のイベントハンドラ
// これも add_user とやってること同じ
function reset()
{
	$( ".ajax-result" ).empty();

	$.get( "./db_manipulate.php", { mode: "reset" }, function( result ){
		if ( result == '0' ){
			$( "#reset-result" ).html( '<p class="alert alert-success">データの削除に成功しました。</p>' );
		} else {
			$( "#reset-result" ).html( '<p class="alert alert-danger">データの削除に失敗しました。</p>' );
		}
		render();
	}
	);
}
