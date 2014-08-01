-- drop database cfchecker_db;

-- �f�[�^�x�[�X�쐬
create database cfchecker_db;
use cfchecker_db;

-- ���[�U���e�[�u��
create table users
(
	id integer primary key auto_increment, -- ���j�[�N ID ���l��
	name varchar( 64 ) not null unique -- Cf �ł̃��[�U��
	-- �ӂ[�Ƀ��j�[�N�����[�U����L�[�ł��悩�����C�����邯�ǂĂ�����Ă�̂ł��̂܂�
);

create table ratings
(
	id integer primary key auto_increment,
	user_id integer not null, -- ���[�U ID �i�O���L�[�j
	updated_sec integer not null, -- �A�b�v�f�[�g�b
	rating integer not null, -- �X�V�ヌ�[�g

	unique ( user_id, updated_sec ), -- �d���f�[�^������Ȃ��悤�Ƀ��j�[�N����Ŕ���
	foreign key ( user_id ) references users( id )
);
