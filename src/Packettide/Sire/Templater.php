<?php namespace Packettide\Sire;


use Mustache_Engine as Mustache;
/**
* 
*/
class Templater
{
	
	function __construct(Mustache $mustache)
	{

	}

	private function with(Name $name)
	{
		$this->name = $name;
	}

	private function augmentArray($array)
	{
		$baseData = array(
			"name" => $this->name,
			"Name" => $this->name->upper(),
			"names" => $this->name->plural(),
			"Names" => $this->name->pluralUpper(),
			"nameLiterate" => $this->name->literate(),
			"NameLiterate" => $this->name->literateUpper(),
			"namesLiterate" => $this->name->pluralLiterate(),
			"NamesLiterate" => $this->name->pluralLiterateUpper(),
			);

		return array_merge($array, $baseData);
	}

	public function template($template, $data, $target)
	{
		$path = dirname($target);
		if (!is_dir($path))
		{
		  mkdir($path);
		}
		$augmentedData = $this->augmentArray($data);
		$rendered = $this->mustache->render($template, $augmentedData);
		file_put_contents($target, $rendered);
	}
}

 ?>