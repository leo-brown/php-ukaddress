<?php
/**
  * Interprets and works with UK Addresses
  *
  * @author Leo Brown
  */
Class UKAddress{

	var $sub_building;
	var $building_number;
	var $building_name;
	var $street;
	var $post_code;
	var $post_town;
	var $country;

	private $address_string;

	/**
	  * Load formatted string in for decoding like
	  * 123 Barry House, 4 Fake Street, Brighton, BN1 3RA, United Kingdom
	  * @author Leo Brown
	  */
	function loadString($str){
		$this->address_string = $str;
		$this->sub_building = null;
		$this->building_number = null;
		$this->street = null;
		$this->post_code = null;
		$this->post_town = null;
		$this->country = null;
	}

	/**
	  * Get current Address as an array of parts
	  *
	  * @author Leo Brown
	  */
	function get(){

		// split address
		$ap = explode(',', $this->address_string);
		foreach($ap as &$a){
			$a = trim($a);
		}

		// lets try to tick some off
		foreach($ap as &$a){

			// country
			if(in_array($a, array('United Kingdom','Great Britain'))){
				$this->country = $a;
				$a = null;
				continue;
			}

			// postcode
			if(preg_match('/^[A-Z]{1,2}\d[A-Z\d]? ?\d[A-Z]{2}$/', strtoupper($a))){
				$this->post_code = strtoupper($a);
				$a = null;
				continue;
			}

			// Flat X - clear
			if(preg_match('/^Flat [0-9A-Za-z]{1,6}$/', $a)){
				$this->sub_building = $a;
				$a = null;
				continue;
			}

			// 148a Dyke Road (the (a) is inside an optional group)
			// 13 Barry Court
			if(preg_match('/^[0-9]([0-9A-Z-az]{1,4})? .*$/', $a)){
				$street_and_no = explode(' ', $a);
				$the_number = array_shift($street_and_no);
				$the_name = implode(' ', $street_and_no);

				if($this->isStreetName($the_name)){
					$this->building_number = $the_number;
					$this->street = $the_name;
				}
				else{
					$this->sub_building = $the_number;
					$this->building_name = $the_name;
				}

				$a = null;
				continue;
			}

			// Dyke Road 148a
			// Dave Mansions 16
			if(preg_match('/[A-Za-z]. ([0-9][A-Za-z0-9]{1,4})?$/', $a)){
				$street_and_no = explode(' ', $a);

				$the_number = array_pop($street_and_no);
				$the_name = implode(' ', $street_and_no);

				if($this->isStreetName($the_name)){
					$this->building_number = $the_number;
					$this->street = $the_name;
				}
				else{
					$this->sub_building = $the_number;
					$this->building_name = $the_name;
				}

				$a = null;
				continue;
			}

			// 813
			if(is_numeric($a)){
				$this->building_number = $a;
				$a = null;
				continue;
			}

			if($this->isStreetName($a)){
				$this->street = $a;
				$a = null;
				continue;
			}

			if($this->isBuildingName($a)){
				$this->building_name = $a;
				$a = null;
				continue;
			}

		}

		// clean parts
		$ap = array_filter($ap);

		// we need a city!
		$this->post_town = array_pop($ap);

		if($ap){
			print "dont know what to do with \n";
			print_r($ap);
			print "\n";
		}

		return $this->asArray();
	}

	/**
	  * Return address as associative array
	  * @author Leo Brown
	  */
	function asArray(){
		return array(
			'building_number' => $this->building_number,
			'sub_building' => $this->sub_building,
			'building_name' => $this->building_name,
			'street' => $this->street,
			'post_code' => str_replace(' ', '', $this->post_code),
			'post_town' => $this->post_town,
			'country' => $this->country,
		);
	}

	function isStreetName($name){
		if(stristr($name, ' avenue')) return true;
		if(stristr($name, ' street')) return true;
		if(stristr($name, ' road')) return true;
		if(stristr($name, ' mews')) return true;
		if(stristr($name, ' drive')) return true;
		if(stristr($name, ' grove')) return true;
		if(stristr($name, ' gardens')) return true;
		if(stristr($name, ' crescent')) return true;
		if(stristr($name, ' close')) return true;
		if(stristr($name, ' way')) return true;
		if(stristr($name, ' vale')) return true;
		if(stristr($name, ' row')) return true;
		if(stristr($name, ' rise')) return true;
		if(stristr($name, ' mead')) return true;
		if(stristr($name, ' wharf')) return true;
		return false;
	}

	function isBuildingName($name){
		if(stristr($name, ' court')) return true;
		if(stristr($name, ' mansions')) return true;
		if(stristr($name, ' apartments')) return true;
		if(stristr($name, ' buildings')) return true;
		if(stristr($name, ' house')) return true;
		if(stristr($name, ' point')) return true;
		if(stristr($name, ' heights')) return true;
		if(stristr($name, ' studios')) return true;
		if(stristr($name, ' lodge')) return true;
		return false;
	}

}	
