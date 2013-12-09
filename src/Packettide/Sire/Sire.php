<?php namespace Packettide\Sire;

use Symfony\Component\Yaml\Yaml;
use Packettide\Sire\Generators\MigrationGenerator;
use Packettide\Sire\Generators\ModelGenerator;
use Packettide\Sire\Generators\ControllerGenerator;
use Packettide\Sire\Generators\ViewGenerator;
use Packettide\Sire\Generators\SeedGenerator;
use Mustache_Engine as Mustache;

class Sire {

	/**
	 * Resource name
	 * @var string
	 */
	public $name;

	/**
	 * Resource members, aka what should this resource generate?
	 * @var array
	 */
	public $makes;

	/**
	 * View theme that should be used
	 * @var  String
	 */
	public $viewTheme;

	/**
	 * What can we generate, and the name of the functions that 
	 * we should call instead if any.
	 * @var  array
	 */
	private $makeable = array(
		'migration' => null,
		'model' => null,
		'view' => null,
		'controller' => null,
		'seed' => null,
		'route' => array('updateRoutesFile'),
		);

	/**
	 * Resource fields
	 * @var array
	 */
	public $fields = array();
	public $file;


	public function __construct(Mustache $mustache, Templater $templater,
		MigrationGenerator $miGen, ModelGenerator $mGen, ControllerGenerator $cGen,
		ViewGenerator $vGen, SeedGenerator $sGen)
	{
		$this->mustache = $mustache;
		$this->templater = $templater;
		$this->migrationGenerator = $miGen;
		$this->modelGenerator = $mGen;
		$this->controllerGenerator = $cGen;
		$this->viewGenerator = $vGen;
		$this->seedGenerator = $sGen;
	}

	/**
	 * Reset all of the Sire fields
	 */
	public function reset()
	{
		$this->fields = array();
	}

	/**
	 * Use a YAML file for field information setup
	 * @param  string $yamlFileLocation
	 * @return Packettide\Sire\Sire
	 */
	public function with($yamlFileLocation)
	{
		$this->getYaml($yamlFileLocation);
		$this->setupNames();
		$this->viewGenerator->setupTemplates($this);
		return $this;
	}

	public function remove()
	{
		$this->controllerGenerator->reset($this);
		$this->migrationGenerator->reset($this);
		$this->modelGenerator->reset($this);
		$this->seedGenerator->reset($this);
		$this->viewGenerator->reset($this);
	}

	public function cmd($command)
	{
		$this->command = $command;
	}

	/**
	 * Run all of the generators and update routes
	 */
	public function run() {
		foreach ($this->makeable as $toMake => $methods) 
		{
			if($methods && in_array($toMake, $this->makes)) 
			{
				foreach ($methods as $method) 
				{
					$this->{$method}();
				}
			} 
			else if (in_array($toMake, $this->makes))
			{
				$this->{$toMake.'Generator'}->run($this);
			}
		}
	}

	/**
	 * Parse the field information from a given YAML file
	 * @param  string $yamlFileLocation
	 */
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

	/**
	 * Generate proper names for this resource
	 */
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

	/**
	 * Transform an associative array into a numeric one
	 * @param  array $array
	 * @return array
	 */
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

	/**
	 * Structure validation rules for a given field
	 * @return array
	 */
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

	/**
	 * Add an entry for this resource to the routes file
	 */
	public function updateRoutesFile()
	{

		$path = app_path() . '/routes.php';

        $content = file_get_contents($path);

		$data = "Route::resource('" . $this->name->plural() . "', '" . ucwords($this->name->pluralUpper()) . "Controller');";
        
        if ( ! strpos($content, $data))
        {
			file_put_contents($path, "\n\n".$data, FILE_APPEND);
        }

	}

}