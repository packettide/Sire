<?php
namespace Packettide\Sire;

use Symfony\Component\Yaml\Yaml;
use Packettide\Sire\Generators\MigrationGenerator;
use Packettide\Sire\Generators\ModelGenerator;
use Packettide\Sire\Generators\ControllerGenerator;
use Packettide\Sire\Generators\ViewGenerator;
use Mustache_Engine as Mustache;

class Sire {

	public $name;
	public $fields = array();
	public $file;

	public function __construct(Mustache $mustache, Templater $templater, 
		MigrationGenerator $miGen, ModelGenerator $mGen, ControllerGenerator $cGen,
		ViewGenerator $vGen)
	{
		$this->mustache = $mustache;
		$this->templater = $templater;
		$this->migrationGenerator = $miGen;
		$this->modelGenerator = $mGen;
		$this->controllerGenerator = $cGen;
		$this->viewGenerator = $vGen;
	}

	public function reset()
	{
		$this->fields = array();
	}

	public function with($yamlFileLocation)
	{
		$this->getYaml($yamlFileLocation);
		$this->setupNames();
		return $this;
	}

	public function cmd($command)
	{
		$this->command = $command;
	}

	public function run() {
		$this->migrationGenerator->run($this);
		$this->modelGenerator->run($this);
		$this->controllerGenerator->run($this);
		$this->viewGenerator->run($this);
		$this->updateRoutesFile();
	}

	private function getYaml($yamlFileLocation)
	{
		$fields = file_get_contents($yamlFileLocation);
        $fields = Yaml::parse($fields);

        $this->fields = array();

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
		else
		{
			$this->name = new Name($this->name);
			$this->templater->with($this->name);
		}
	}

	public function assocToNumeric($array) 
	{
		$newArray = array();

		foreach ($array as $value) {
			array_push($newArray, $value);
		}

		return $newArray;
	}

	public function pluckWith($needle, $haystack, $with)
	{
		$toReturn = array();

		foreach ($haystack as $subHaystack)
		{
			if (isset($subHaystack[$needle])) 
			{
				$subHaystack[$needle][$with] = $subHaystack[$with];
				array_push($toReturn, $subHaystack[$needle]);
			}
		}

		return $toReturn;
	}

	public function getRules()
	{
		$toReturn = array();

		foreach ($this->fields as $key => $value) {
			if (isset($value['validation']))
			{
				$rule = array(
					"_name" => $key,
					"rules" => implode("|", $value['validation'])
					);
				array_push($toReturn, $rule);
			}
		}

		return $toReturn;
	}

	public function updateRoutesFile()
    {

		$data = "\n\nRoute::resource('" . $this->name->plural() . "', '" . ucwords($this->name->pluralUpper()) . "Controller');";

        file_put_contents(app_path() . '/routes.php', $data, FILE_APPEND);
    }

}