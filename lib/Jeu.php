<?php

class Jeu{

	private	$rows;
	private $columns;
	private $grid;
	private $lastPlayer;
	private $currentGamer;
	private $pairs_number;
	
	private $images;
	private $image_dir;
	
	public function __construct($image_dir, $level="easy"){
		$this->initialize_level($level);
		$this->initialize_images();
		$this->initialize();
		$this->swap_cells();
		$this->image_dir = $image_dir;
	}
	
	public function __destruct(){
		//unset( $this->images );
		//unset( $this->grid );
	}
	
	
	public function initialize_level($level){
		switch( $level ){
			case  "very easy" :{ 
				$this->rows=4;
				$this->columns = 3;
				break;
			}
			case "middle" :{
				$this->rows=6;
				$this->columns = 6;
				break;
			}
			case "advanced" :{
				$this->rows=6;
				$this->columns = 10;
				break;
			}
			// default is the easy one
			default :{
				$this->rows=4;
				$this->columns = 4;				
			}
		}
		$this->pairs_number = ($this->rows * $this->columns )/ 2;
	}
	
	public function initialize_images(){
		$this->images = array();
		$image_total = $this->pairs_number;
		
		if ($handle = opendir( 'static/images' ) ) {
			
			while ( ($entry = readdir($handle)) !== false ) {
				
				if ($entry != "." && $entry != ".." ) {
					$this->images[] = $entry;
				}
			}
			$rand_keys = array_rand( $this->images, $image_total );
			$temp = array();
			foreach( $rand_keys as $key ){
				$temp[] = $this->images[$key];
			}
			unset( $this->images );
			$this->images = $temp;
			shuffle( $this->images );
			closedir($handle);
		}
	}
	
	public function initialize(){
		// first dimension
		$this->grid = array();
		
		// second dimension
		for( $i=0 ; $i < $this->rows; $i++ ){
			$this->grid[] = array();
		}
		
		$total_number = $this->pairs_number;
		
		$range1 = range( 0, $total_number-1 );
		shuffle ($range1 );
		$range2 = range( 0, $total_number-1 );
		shuffle ($range2 );
		$merged = array_merge( $range1, $range2 );
		shuffle($merged);
		
		for ( $i= 0; $i < $this->rows; $i++){
			for ( $j= 0; $j < $this->columns; $j++){
				shuffle($merged);
				$this->setCell( $i, $j, array_shift($merged) );
			}
		}
	}

	public function swap_cells(){
		
		for ( $i= 0; $i < $this->rows; $i++){
			for ( $j= 0; $j < $this->columns; $j++){
				$rand_r_1 = mt_rand(0, $this->rows-1);
				$rand_c_1 = mt_rand(0, $this->columns-1);
				$rand_value = $this->getCell( $rand_r_1, $rand_c_1 );
				$current_value = $this->getCell( $i, $j );
				$this->setCell( $i, $j, $rand_value );
				$this->setCell( $rand_r_1, $rand_c_1, $current_value );
			}
		}
	}
	
	public function getImage( $i, $j ){
		return $this->images[$this->getCell($i, $j)];
	}
	
	public function getCell( $i, $j ){
		return $this->grid[$i][$j];
	}
	
	public function setCell( $i, $j, $value ){
		$this->grid[$i][$j] = $value;
	}
	
	// general getter
	public function __get($attribute){
		return $this->$attribute;
	}
	// general setter
	public function __set( $attribute, $value ){
		$this->$attribute = $value;
	}
		
	public function right( $piece1_r, $piece1_c, $piece2_r, $piece2_c ){
		return $this->getCell($piece1_r, $piece1_c) == $this->getCell($piece2_r, $piece2_c);
	}		
		
	public function affiche(){
		echo( '<table>');
			echo ( '<tbody>' );
			$counter = 0;
			for( $i = 0; $i < $this->rows; $i++){
				if( $counter % $this->columns == 0){
					echo( "<tr>" );
				}
				for( $j = 0; $j < $this->columns; $j++){
					echo ('<td id="'. 'cell_'.$i.'_'.$j.'"></td>');
	//				echo ('<td id="'. 'cell_'.$i.'_'.$j.'" ><img src="'.$this->image_dir."/".$this->getImage($i, $j).'" /></td>');
					$counter++;
				}
				echo( "</tr>" );
			}
			echo ( '</tbody>' );
		echo( '</table>' );
	}

	
	
}
?>