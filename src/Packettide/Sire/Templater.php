<?php namespace Packettide\Sire;

use Mustache_Engine as Mustache;

/**
* Setup and render Mustache templates
*/

class Templater
{

	function __construct(Mustache $mustache)
	{
		$this->mustache = $mustache;
	}

	/**
	 * Set name to use with Templater
	 * @param  Packettide\Sire\Name   $name
	 */
	public function with(Name $name)
	{
		$this->name = $name;
	}

	/**
	 * Add Name data to a given array
	 * @param  array $array
	 * @return array
	 */
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

	/**
	 * Process a template
	 * @param  string $template Mustache template
	 * @param  array $data
	 * @param  string $target
	 */
	public function template($template, $data, $target)
	{
		$path = dirname($target);

		// Make sure target directory exists
		if (!is_dir($path)) mkdir($path);

		$augmentedData = $this->augmentArray($data);
		$rendered = $this->mustache->render($template, $augmentedData);

		// Save data to $target location
		file_put_contents($target, $rendered);
	}
}