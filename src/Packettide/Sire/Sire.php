<?php
namespace Packettide\Sire;

use Illuminate\Support\Pluralizer;
use Symfony\Component\Yaml\Yaml;

class Sire {

	protected $name; 
	protected $names; 
	protected $Name; 
	protected $Names;
	protected $fields = array();

	public function __construct($yamlFileLocation)
	{
		$this->getYaml($yamlFileLocation);
		$this->setupNames();
		var_dump($this);
	}

	private getYaml($yamlFileLocation)
	{
		$fields = file_get_contents($yamlFileLocation);
        $fields = Yaml::parse($fields);

        foreach ($fields as $key => $value) 
        {
        	// This is a *special field like name
        	if(strpos($key, '_') === 0)
        	{
        		$this->${ltrim($key, '_')} = $value;
        	}
        	else
        	{
        		$this->fields[$key] = $value;
        	}
        }
	}

	private setupNames() 
	{
		if(!isset($this->name)) 
		{
			throw new \InvalidArgumentException('At minimum you must specify _name');
		}

		if(!isset($this->Name))
		{
			$this->Name = ucwords($this->name);
		}

		if(!isset($this->names))
		{
			$this->Name = Pluralizer::plural($this->name);
		}

		if(!isset($this->Names))
		{
			$this->Name = ucwords($this->name);
		}
	}

}