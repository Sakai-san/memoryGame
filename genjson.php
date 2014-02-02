<?php
function loadClass( $className ){
	require "lib/". $className . ".php" ;
}
spl_autoload_register( 'loadClass' );
session_start();

$action =  isset( $_REQUEST["action"] ) ? $_REQUEST["action"] : null;

// game is on; the round correspond to the second move of one single pair
if( $action == "play" ){
	$r = (int) $_REQUEST['row'];
	$c = (int) $_REQUEST['column'];
	// increase the number of play
	$play_number = 1+$_SESSION['joueur']->__get('play_number');
	$_SESSION['joueur']->__set('play_number', $play_number);
	// first or second round
	$round =  ($play_number % 2) == 0 ? 2 : 1;

	// if round is 2, further information as below are sent
	// success (boolean)
	// move before $r,$c (string)
	// number of attemps (integer)
	// terminated (boolean)
	$played = array(
					 "results" => array(	
					 					'image_name' => $_SESSION['memory']->getImage($r, $c),
										'image_dir' => $_SESSION['memory']->__get('image_dir'),
										'round' => $round
					 					)
	);
	
	// here is a second move of one single pair
	if( $round == 2  ){
		$last_play_r = $_SESSION['joueur']->__get('last_play_row');
		$last_play_c = $_SESSION['joueur']->__get('last_play_column');
		$success = $_SESSION['memory']->right( $last_play_r, $last_play_c, $r, $c );
		$played['results']['success'] = $success;
		// update the number of pairs founded
		if ( $success ){
			$_SESSION['joueur']->__set( 'pairs_founded', $_SESSION['joueur']->__get( 'pairs_founded' )+1 );
		}
		$played['results']['move_before'] = $last_play_r.",".$last_play_c;
		$played['results']['attemps'] = $_SESSION['joueur']->__get( 'play_number' ) / 2;
		// checks is game finished by comparing the now new value of pairs_founded in the Gamer object
		$played['results']['terminated'] = $_SESSION['joueur']->__get('pairs_founded') == $_SESSION['memory']->__get('pairs_number');
	}
	
	// update the last move played
	$_SESSION['joueur']->__set( 'last_play_row', $r );
	$_SESSION['joueur']->__set( 'last_play_column', $c );
	echo json_encode( $played );
}

// le joueur demande une nouvelle partie
elseif ( $action == "new" ){

	$level = $_REQUEST['level'];
	unset ( $_SESSION['memory'] );
	$_SESSION['memory'] = new Jeu( "static/images", $level );
	unset ( $_SESSION['joueur'] );
	$_SESSION['joueur'] = new Gamer();
	$return =  array( "results"=>  array( 
										"new"=>true,
										"rows" => $_SESSION['memory']->__get('rows'),
										"cols" => $_SESSION['memory']->__get('columns')
										)
					);
	echo json_encode( $return );
}

?>