<?php

class Gamer{

	private $last_play_row;
	private $last_play_column;
	private $play_number;
	private $pairs_founded;
	
	public function __construct(){
		$last_play_row = null;
		$last_play_column = null;
		$this->play_number = 0;
		$this->pairs_founded = 0;
	}
	// general getter
	public function __get($attribute){
		return $this->$attribute;
	}
	
	// general setter
	public function __set( $attribute, $value ){
		$this->$attribute = $value;
	}

}

?>