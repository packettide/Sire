<?php
namespace Packettide\Sire;

use Illuminate\Support\Pluralizer;
use Symfony\Component\Yaml\Yaml;
use Mustache_Engine as Mustache;

class Sire {

	protected $name; 
	protected $names; 
	protected $Name; 
	protected $Names;
	protected $fields = array();
	protected $file;

	// Templates
	protected $migrationTemplate = file_get_contents(__DIR__.'/templates/migration.mustache');

	public function __construct($yamlFileLocation)
	{
		$this->getYaml($yamlFileLocation);
		$this->setupNames();
		$this->mustache = new Mustache();
	}

	public function run() {
		$this->generateMigration();
	}

	private function getYaml($yamlFileLocation)
	{
		$fields = file_get_contents($yamlFileLocation);
        $fields = Yaml::parse($fields);

        foreach ($fields as $key => $value) 
        {
        	// This is a *special field like name
        	if(strpos($key, '_') === 0)
        	{
        		$this->{ltrim($key, '_')} = $value;
        	}
        	else
        	{
        		$this->fields[$key] = $value;
        		$this->fields[$key]['_name'] = $value;
        	}
        }
	}

	private function setupNames() 
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
			$this->names = Pluralizer::plural($this->name);
		}

		if(!isset($this->Names))
		{
			$this->Names = ucwords($this->names);
		}
	}

	public function generateMigration()
	{
		$toTemplate = array(
			"name" => $this->name,
			"tableName" => $this->name,
			"fields" => $this->fields,
			)

		var_dump($this->mustache->render($migrationTemplate, $toTemplate));
	}

}