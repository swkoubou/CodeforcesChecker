-- drop database cfchecker_db;

-- データベース作成
create database cfchecker_db;
use cfchecker_db;

-- ユーザ情報テーブル
create table users
(
	id integer primary key auto_increment, -- ユニーク ID 数値で
	name varchar( 64 ) not null unique -- Cf でのユーザ名
	-- ふつーにユニークだユーザ名主キーでもよかった気がするけどておくれてるのでこのまま
);

create table ratings
(
	id integer primary key auto_increment,
	user_id integer not null, -- ユーザ ID （外部キー）
	updated_sec integer not null, -- アップデート秒
	rating integer not null, -- 更新後レート

	unique ( user_id, updated_sec ), -- 重複データが入らないようにユニーク制約で縛る
	foreign key ( user_id ) references users( id )
);
