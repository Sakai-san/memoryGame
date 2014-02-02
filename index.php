<?php
function loadClass( $className ){
        require "lib/". $className . ".php" ; 
} 
spl_autoload_register( 'loadClass' );
session_start();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
		<script type="text/javascript" src="../chat/jquery-1.9.1.min.js"></script>
		<link rel="stylesheet" href="static/main.css" />	
	</head>


<body>
<h1> memory </h1>

<!--  wrapp rules and game's status -->
<div class="left">

	<!--  info about the rules -->
	<div>
		<p class="regle emphasis"> Règles </p>
		<p>Clique sur les cases et trouve les paires !</p>
	</div>
	
	<!--  info about the game state -->
	<div id="infos" >
				<select name="level">
					<option>very easy</option>
					<option selected="selected">easy</option>
					<option>middle</option>
					<option>advanced</option>
				</select>
			<button id="nouvellePartie"> New game </button>
			<br/>
			<br/>
			<p><span id="etat" class="hidden">Nombre de coups </span></p>
	</div>
</div>

<?php
	$_SESSION['memory']	= new Jeu( "static/images" );
	$_SESSION['joueur']	= new Gamer();
?>
<!-- contains the game -->
<div id="game">
<?php
		$_SESSION['memory']->affiche();
?>
</div>

<script>
var piece_selected_counter;

$( document ).ready ( function(){
	event_play_attach();
//	$( 'td' ).each( function(){ $(this).addClass('photo_background') });
	$( '#nouvellePartie' ).bind( 'click', function(){newGame_handler();});
	piece_selected_counter = 0;;
	
});


function newGame_handler(){
	$.ajax({
		type: 'GET',
		url : "genjson.php",
		data : "action=new&level="+$('#infos option:selected').val(),
		dataType : 'json',
		async: true,
		success: function(returned){
			if( returned.results.new == true ){
				var current_rows = $('#game table tbody tr').length;
				var current_cols = $('#game table tbody tr:first-child td').length;
				var new_rows = returned.results.rows;
				var new_cols = returned.results.cols;				
				var rows_to_add = new_rows - current_rows;
				var cols_to_add = new_cols - current_cols;

				// new colums must been added
				if( cols_to_add > 0 ){
					$('#game table tbody tr').each( function(){
						for( var i=0; i< cols_to_add; i++ ){
							$(this).append( '<td></td>' );
						}
					});
				}
				// columns has to be removed
				else{
					$('#game table tbody tr').each( function(){
						for( var i=0; i< Math.abs(cols_to_add); i++ ){
							$(this).find(':first-child').remove();
						}
					});
				}
				
				// new rows must been added
				if( rows_to_add > 0 ){
					$('#game table tbody').each( function(){
						var concat ='', k=0;
						for( k=0; k < new_cols; k++){
							concat += '<td></td>';}
						if( cols_to_add > 0 ){
							for( k=0; k< rows_to_add; k++ ){
								$(this).append( '<tr>' + concat + '</tr>' );						
							}
						}
					});
				}
				// rows has to be removed
				else{
					for( var i=0; i< Math.abs(rows_to_add); i++ ){
						$('#game table tbody tr:first-child').remove();
					}
				}
				
				$( "#etat" ).empty();
				// renumbering of the cells
				var tr_nb =0, td_nb = 0;
				$( "tr" ).each( function(){
					$(this).find('td').each( function(){
						$(this).attr( 'id', 'cell_'+tr_nb+'_'+td_nb );
						td_nb++;
					});
//					.addClass('photo_background');;
					td_nb = 0;
					tr_nb++;
				});
				
				$( "td" ).each ( function(){
					$(this).empty().removeClass('hover').addClass('hover');
					$(this).off('click').on( 'click', function(){ play_handler( this );
					});
				});
				piece_selected_counter = 0;
			}
		}
	});
}


function event_play_attach(){
	
	$( "td" ).each( function(){
		if( ! $(this).html() ){
			$(this).addClass( 'hover' ).on( 'click', function(){play_handler( this );});
		}
	});
}

function play_handler( cell_args ){
	$( '#wait' ).show();
	piece_selected_counter++;
	if( piece_selected_counter <= 2 ){
		var cell = $(cell_args).attr( 'id' );
		var id   = cell.split( '_' );
		
		// unbind the cell which has been clicked on in all cases
		$( '#cell_'+id[1]+'_'+id[2] ).removeClass('hover').unbind('click');
		
		$.ajax({
			type: 'GET',
			url : "genjson.php",
			data : "action=play&row="+id[1]+"&column="+id[2],
			dataType : 'json',
			async: true,
			success: function(returned){
				
					var image = returned.results.image_name;
					var path  = returned.results.image_dir;
					$( '#cell_'+id[1]+'_'+id[2] ).html( '<img src="'+path + "/" +image +'" />' );					

					if ( returned.results.round == 2 ){
						////////////////////////////////////////////////////////
						// pair match or not
						var move_before = returned.results.move_before.split( ',');
						// false : remove display and reattach event
						if( returned.results.success == false ){
							setTimeout( function( ){
								$( '#cell_'+ move_before[0] +'_' + move_before[1] ).empty();
								$( '#cell_'+id[1]+'_'+id[2] ).empty();
								//$(cell_args).empty();
								event_play_attach();
							}, 2000);
						}
						// correct : here let the display and remove the bind
						else{
							$( '#cell_'+ move_before[0] +'_' + move_before[1] ).unbind('click');
							$( '#cell_'+id[1]+'_'+id[2] ).unbind('click');
							event_play_attach();
						}
						piece_selected_counter=0;

						////////////////////////////////////////////////////////
						
						// game finished
						if( returned.results.terminated == true ){
							$( "#etat" ).empty().text( 'Bravo, tu as réussi en ' + returned.results.attemps + ' coups');
						}
						// game not finished yet
						else{
							$( "#etat" ).removeClass( 'hidden' ).empty().text( 'Nombre de coups '+ returned.results.attemps );
						}
					}
					$( '#wait' ).hide();
			}
		});
	}
	if( piece_selected_counter == 2){
		$( "td" ).each (function(){
			$(this).removeClass( 'hover' ).off('click');
		});
	}
}

		</script>
		<footer>
		<p>realisé par Hiro</p>
		</footer>
	</body>
</html>