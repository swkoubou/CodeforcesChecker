<?php

// ����̃��[�U�� $user_name �ɂ��āA�f�[�^���X�V����
function update_user( $db_connecton, $user_name )
{
	// ���[�U�� ID �i�f�[�^�x�[�X�I�ȈӖ��Łj���擾
	$user_id = $db_connecton->query( 'select id from users where name = "' . $user_name . '"' )->fetch()[0];

	// Codeforces �� API ��@���ăf�[�^���擾
	// ���[�U�����݂��Ȃ��Ƃ��A�X�e�[�^�X�R�[�h 400 ( bad request ) ������ file_get_contents �� false ��Ԃ�
	if ( !( $api_result = file_get_contents( 'http://codeforces.com/api/user.rating?handle=' . $user_name ) ) )
	{
		return 1;
	}

	// �擾���� JSON �� PHP �ň�����`�Ƀf�R�[�h
	$json = json_decode( $api_result, true );

	// ���[�e�B���O���ڂɊւ��镔�������[�v
	foreach ( $json{ 'result' } as $row )
	{
		// �A�b�v�f�[�g�b�ƍX�V�ヌ�[�g
		$sec = $row{ 'ratingUpdateTimeSeconds' };
		$rating = $row{ 'newRating' };

		// �f�[�^�x�[�X�ɕۑ�
		$db_connecton->query( 'insert into ratings ( user_id, updated_sec, rating ) values ( ' . $user_id . ', ' . $sec . ', ' . $rating . ' )' );
	}

	return 0;
}

// GET �p�����[�^�̏���
// JavaScript ���̊ȗ��̂��߂� user_name �͋]���ɂȂ���
$user_name = array_key_exists( 'user_name', $_GET ) ? $_GET{ 'user_name' } : "";
$mode = $_GET{ 'mode' };

// �f�[�^�x�[�X�ɐڑ�
$db_connecton = new PDO( 'mysql:host=localhost;dbname=cfchecker_db;', 'root', '' );

// mode �p�����[�^�ɂ�菈����U�蕪��
switch ( $mode )
{
case 'json': // �O���t�`��p JSON �f�[�^�̃��N�G�X�g
	// JSON �f�[�^�̊O�s�͔z��
	echo '[';

	// �J���}�}���p�t���O
	//�_�T���c�c
	$first_user = true;

	// �f�[�^�x�[�X���烆�[�U�����ŐV���[�g�i�����݃��[�g�j���������Ɏ擾���ă��[�v
	for ( $users = $db_connecton->query( 'select * from users u order by ( select rating from ratings where user_id = u.id and updated_sec = ( select max( updated_sec ) from ratings where user_id = u.id ) ) desc;' ); $user = $users->fetch(); )
	{
		// �J���}�̑}��
		// �_�T���c�c
		if ( $first_user )
		{
			$first_user = false;
		}
		else
		{
			echo ',';
		}

		// �ꌏ�̃f�[�^�ɂ��ď���
		echo '{ ';
		echo '"label": "' . $user[ 'name' ] . '", ';
		echo '"data": [';
		$first_data = true;

		// ���ڂ��Ă��郆�[�U�ɂ��āA���[�g�X�V�����擾
		for ( $ratings = $db_connecton->query( 'select updated_sec as sec, rating from ratings where user_id = ' . $user{ 'id' } ); $data = $ratings->fetch(); )
		{
			// �J���}�̑}���i�_�T���j
			if ( $first_data )
			{
				$first_data = false;
			}
			else
			{
				echo ',';
			}

			// �f�[�^��\���^�v��
			// Flot �̓s���ŕb�� 1000 �{
			echo '[ ' . $data{ 'sec' } * 1000 . ', ' . $data{ 'rating' } . ' ]';
		}
		echo '] }';
	}
	echo ']';
	break;
case 'add': // ���[�U�ǉ����N�G�X�g
	// $user_name ��ǉ����� update_user ���Ăяo��
	if ( !$db_connecton->query( 'insert into users ( name ) values ( "' . $user_name . '" )' ) || update_user( $db_connecton, $user_name ) )
	{
		// ���s������f�[�^�x�[�X�������
		$db_connecton->query( 'delete from users where name = "' . $user_name . '"' );
		// �ȏ�I���̃X�e�[�^�X�R�[�h��\��
		echo 1;
	}
	else
	{
		// ����I���̃X�e�[�^�X�R�[�h��\��
		echo 0;
	}
	break;
case 'update': // �f�[�^�X�V���N�G�X�g
	// �f�[�^�x�[�X�ɂ���S���[�U�����o���ă��[�v
	for ( $users = $db_connecton->query( 'select name from users' ); $user = $users->fetch(); )
	{
		// �f�[�^�X�V
		update_user( $db_connecton, $user{ 'name' } );
		// 5 [times/sec] �̃A�N�Z�X����������̂� 200 [ms] �E�F�C�g
		usleep( 200 * 1000 );
	}
	echo 0;
	break;
case 'reset': // �f�[�^�������N�G�X�g
	// ���[�e�B���O���������Ă��烆�[�U������
	$db_connecton->query( 'delete from ratings' );
	$db_connecton->query( 'delete from users' );
	echo 0;
}
?>
