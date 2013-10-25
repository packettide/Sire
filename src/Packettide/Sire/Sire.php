<?php
namespace Packettide\Sire;

use Illuminate\Support\Pluralizer;
use Symfony\Component\Yaml\Yaml;
use Mustache_Engine as Mustache;

class Sire {

	protected $name;
	protected $fields = array();
	protected $file;

	// Templates
	protected $migrationTemplate;

	public function __construct($yamlFileLocation)
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
		$this->mustache = new Mustache();
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
		}
	}

	private function augmentArray($array)
	{
		$baseData = array(
			"name" => $this->name,
			"Name" => $this->name->upper,
			"names" => $this->name->plural,
			"Names" => $this->name->pluralUpper,
			"nameLiterate" => $this->name->literate,
			"NameLiterate" => $this->name->literateUpper,
			"namesLiterate" => $this->name->pluralLiterate,
			"NamesLiterate" => $this->name->pluralLiterateUpper,
			);

		return array_merge($array, $baseData);
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
		$name = date('Y_m_d_His') . '_create_' . $this->names . '_table.php';

		$toTemplate = array(
			"tableName" => $this->name->plural,
			"fields" => $fields,
			);

		file_put_contents($path.$name, $this->mustache->render($this->migrationTemplate, $this->augmentArray($toTemplate)));
	}

	public function generateModel()
	{

		$fields = $this->pluckWith('bree', $this->fields, '_name');
		$relationships = $this->pluckWith('relationships', $this->fields, '_name');

		$path = app_path() . '/models/';
		$name = $this->Name.'.php';

		$toTemplate = array(
			"rules" => $this->getRules(),
			"relationships" => $relationships,
			"breeFields" => $fields,
			);

		file_put_contents($path.$name, $this->mustache->render($this->modelTemplate, $this->augmentArray($toTemplate)));
	}

	public function generateController()
	{
		$path = app_path() . '/controllers/';
		$name = $this->Names.'Controller.php';

		$toTemplate = array(
			);

		file_put_contents($path.$name, $this->mustache->render($this->controllerTemplate, $this->augmentArray($toTemplate)));
	}

	public function generateViews()
	{
		$path = app_path() . '/views/'.$this->names.'/';
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
			file_put_contents($path.$name, $this->mustache->render($this->viewTemplates[$name], $toTemplate));
		}
		if (!is_dir(app_path() . '/views/layouts/')) {
		  mkdir(app_path() . '/views/layouts/');
		}
		file_put_contents(app_path() . '/views/layouts/layout.blade.php', $this->mustache->render($this->viewTemplates['layout.blade.php'], $this->augmentArray($toTemplate)));
	}

	public function updateRoutesFile($name)
    {

		$data = "\n\nRoute::resource('" . $this->name->plural . "', '" . ucwords($this->name->pluralUpper) . "Controller');";

        file_put_contents(app_path() . '/routes.php', $data, FILE_APPEND);
    }

}