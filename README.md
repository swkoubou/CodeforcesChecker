CodeforcesChecker
=================

　Codeforcesにおける複数人のレーティング推移をグラフで確認できます。

Development
-----------
#### 開発フローについて
　github-flow推奨。

#### 各フォルダについて
　ルート直下のそれぞれのフォルダは下記のような役割となっています。  

- `develop`: 開発者用ディレクトリです。初期化用sqlや開発メモ等を格納します。
- `public`: Webで公開するディレクトリです。実サーバではここにシンボリックリンクを張ります。PHP, HTML/CSS, JavaScript で開発します。APIはこの中の`api`ディレクトリの中です。

#### 初期設定

1. `develop/db_init.sql` をMySQLで実行し、データベースを構築します。
2. `develop/config.json` をこのプロジェクトのルートにコピーし、パスワード等、必要に応じて編集します。
 
