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
	protected $migrationTemplate;

	public function __construct($yamlFileLocation)
	{
		$this->getYaml($yamlFileLocation);
		$this->setupNames();
		$this->migrationTemplate = file_get_contents(__DIR__.'/templates/migration.mustache');
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
        		$this->fields[$key]['_name'] = $key;
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


	private function assocToNumeric() 
	{
		$fields = array();

		foreach ($this->fields as $value) {
			array_push($fields, $value);
		}

		return $fields;
	}

	public function generateMigration()
	{

		$fields = assocToNumeric($this->fields);

		$path = app_path() . '/database/migrations';
		$name = date('Y_m_d_His').'_create_' . $this->models . '_table';

		$toTemplate = array(
			"name" => $this->name,
			"tableName" => $this->name,
			"fields" => $fields,
			);

		file_put_contents($path.$name, $this->mustache->render($this->migrationTemplate, $toTemplate));
	}

}