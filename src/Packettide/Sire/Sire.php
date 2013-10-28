<?php
namespace Packettide\Sire;

use Symfony\Component\Yaml\Yaml;
use Packettide\Sire\Generators\MigrationGenerator;
use Packettide\Sire\Generators\ModelGenerator;
use Packettide\Sire\Generators\ControllerGenerator;
use Mustache_Engine as Mustache;

class Sire {

	public $name;
	public $fields = array();
	public $file;

	public function __construct(Mustache $mustache, Templater $templater, 
		MigrationGenerator $miGen, ModelGenerator $mGen, ControllerGenerator $cGen)
	{
		$this->mustache = $mustache;
		$this->templater = $templater;
		$this->migrationGenerator = $miGen;
		$this->modelGenerator = $mGen;
		$this->controllerGenerator = $cGen;
	}

	public function with($yamlFileLocation)
	{
		$this->getYaml($yamlFileLocation);
		$this->setupNames();
		$this->controllerTemplate = file_get_contents(__DIR__.'/templates/controller.mustache');
		$this->viewTemplates = array(
				"create.blade.php" => file_get_contents(__DIR__.'/templates/views/create.mustache'),
				"edit.blade.php" => file_get_contents(__DIR__.'/templates/views/edit.mustache'),
				"index.blade.php" => file_get_contents(__DIR__.'/templates/views/index.mustache'),
				"layout.blade.php" => file_get_contents(__DIR__.'/templates/views/layout.mustache'),
			);
		return $this;
	}

	public function run() {
		$this->migrationGenerator->run($this);
		$this->modelGenerator->run($this);
		$this->controllerGenerator->run($this);
		$this->generateViews();
		$this->updateRoutesFile();
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

	public function generateViews()
	{
		$path = app_path() . '/views/'.$this->name->plural().'/';
		$names = array('create.blade.php', 'edit.blade.php', 'index.blade.php');
		$headings = array_map(function ($el) {
			return array("heading" => $el['bree']['label']);
		}, $this->fields);

		$toTemplate = array(
			"headings" => $this->assocToNumeric($headings),
			"fields" => $this->assocToNumeric($this->fields),
			);

		foreach ($names as $name) {
			if (!is_dir($path)) {
			  mkdir($path);
			}
			$this->templater->template($this->viewTemplates[$name], $toTemplate, $path.$name);
		}

		if (!is_dir(app_path() . '/views/layouts/')) {
		  mkdir(app_path() . '/views/layouts/');
		}
		$this->templater->template($this->viewTemplates['layout.blade.php'], $toTemplate, app_path() . '/views/layouts/layout.blade.php');
	}

	public function updateRoutesFile()
    {

		$data = "\n\nRoute::resource('" . $this->name->plural() . "', '" . ucwords($this->name->pluralUpper()) . "Controller');";

        file_put_contents(app_path() . '/routes.php', $data, FILE_APPEND);
    }

}