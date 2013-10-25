<?php
namespace Packettide\Sire;

use Symfony\Component\Yaml\Yaml;
use Mustache_Engine as Mustache;

class Sire {

	protected $name;
	protected $fields = array();
	protected $file;

	// Templates
	protected $migrationTemplate;

	public function __construct(Mustache $mustache, Templater $templater)
	{
		$this->mustache = $mustache;
		$this->templater = $templater;
	}

	public function with($yamlFileLocation)
	{
		$this->getYaml($yamlFileLocation);
		$this->setupNames();
		$this->migrationTemplate = file_get_contents(__DIR__.'/templates/migration.mustache');
		$this->modelTemplate = file_get_contents(__DIR__.'/templates/model.mustache');
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
		$this->generateMigration();
		$this->generateModel();
		$this->generateController();
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

	private function assocToNumeric($array) 
	{
		$newArray = array();

		foreach ($array as $value) {
			array_push($newArray, $value);
		}

		return $newArray;
	}

	private function pluckWith($needle, $haystack, $with)
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

	private function getRules()
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

	public function generateMigration()
	{

		$fields = $this->assocToNumeric($this->fields);

		$path = app_path() . '/database/migrations/';
		$name = date('Y_m_d_His') . '_create_' . $this->name->plural() . '_table.php';

		$toTemplate = array(
			"tableName" => $this->name->plural(),
			"fields" => $fields,
			);

		$this->templater->template($this->migrationTemplate, $toTemplate, $path.$name);
	}

	public function generateModel()
	{

		$fields = $this->pluckWith('bree', $this->fields, '_name');
		$relationships = $this->pluckWith('relationships', $this->fields, '_name');

		$relationships = array_map(function ($el) {
			$el['name'] = \Str::camel($el['name']);
			return $el;
		}, $relationships);

		$path = app_path() . '/models/';
		$name = $this->name->upper().'.php';

		$toTemplate = array(
			"rules" => $this->getRules(),
			"relationships" => $relationships,
			"breeFields" => $fields,
			);

		$this->templater->template($this->modelTemplate, $toTemplate, $path.$name);
	}

	public function generateController()
	{
		$path = app_path() . '/controllers/';
		$name = $this->name->pluralUpper().'Controller.php';

		$toTemplate = array();

		$this->templater->template($this->controllerTemplate, $toTemplate, $path.$name);
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