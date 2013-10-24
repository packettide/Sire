<?php
namespace Packettide\Sire;

class Sire {

	protected $name; 
	protected $names; 
	protected $Name; 
	protected $Names;
	protected $fields;

	public function __construct($name, $yamlFileLocation)
	{
		var_dump($name);
		var_dump($yamlFileLocation);
	}

}