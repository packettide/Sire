<?php namespace Packettide\Sire\Generators;

class ViewGenerator
{

	public function __construct()
	{
		$this->viewTemplates = array(
			"create.blade.php" => file_get_contents(__DIR__.'/../templates/views/create.mustache'),
			"edit.blade.php" => file_get_contents(__DIR__.'/../templates/views/edit.mustache'),
			"index.blade.php" => file_get_contents(__DIR__.'/../templates/views/index.mustache'),
			"layout.blade.php" => file_get_contents(__DIR__.'/../templates/views/layout.mustache'),
		);
	}

	/**
	 * Generate View files from template
	 * @param  Packettide\Sire\Sire $sire
	 */
	public function run($sire)
	{
		$path = app_path() . '/views/'.$sire->name->plural().'/';

		$names = array('create.blade.php', 'edit.blade.php', 'index.blade.php');
		$headings = array_map(function ($el) {
			return array("heading" => $el['bree']['label']);
		}, $sire->fields);

		$toTemplate = array(
			"headings" => $sire->assocToNumeric($headings),
			"fields" => $sire->assocToNumeric($sire->fields),
		);

		foreach ($names as $name) {
			// Make sure view $path exists
			if (!is_dir($path)) mkdir($path);

			$sire->templater->template($this->viewTemplates[$name], $toTemplate, $path.$name);
		}

		// Set $layoutPath for view layouts and make sure it exists
		$layoutPath = app_path() . '/views/layouts/';
		if (!is_dir($layoutPath)) mkdir($layoutPath);

		$sire->templater->template($this->viewTemplates['layout.blade.php'], $toTemplate, $layoutPath .'layout.blade.php');
	}

}
