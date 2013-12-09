<?php namespace Packettide\Sire\Generators;

class ViewGenerator
{

	public function __construct()
	{
		
	}

	public function setupTemplates($sire)
	{
		$this->viewTemplates = array(
			"create.blade.php" => file_get_contents(__DIR__.'/../templates/view/'.$sire->viewTheme.'/create.mustache'),
			"edit.blade.php" => file_get_contents(__DIR__.'/../templates/view/'.$sire->viewTheme.'/edit.mustache'),
			"index.blade.php" => file_get_contents(__DIR__.'/../templates/view/'.$sire->viewTheme.'/index.mustache'),
			"layout.blade.php" => file_get_contents(__DIR__.'/../templates/view/'.$sire->viewTheme.'/layout.mustache'),
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
			return array("heading" => $el['label'], 'hide' => ($el['fieldType'] == 'None'));
		}, $sire->fields);

		$tempFields = $sire->fields;
		$fields = array();

		foreach ($tempFields as $key => $value) {
			$value['realField'] = isset($value['relatedModel']) && isset($value['fieldTypeOptions']);
			$value['realField'] = ($value['realField'])? $value['fieldTypeOptions']['title'] : false;
			$value['hide'] = ($value['fieldType'] == 'None');
			array_push($fields, $value);
		}

		$fields = $sire->assocToNumeric($fields);

		$toTemplate = array(
			"headings" => $sire->assocToNumeric($headings),
			"fields" => $fields,
		);

		foreach ($names as $name) {
			// Make sure view $path exists
			if (!is_dir($path)) mkdir($path);

			$sire->templater->template($this->viewTemplates[$name], $toTemplate, $path.$name);
		}

		// Set $layoutPath for view layouts and make sure it exists
		$layoutPath = app_path() . '/views/layouts/';
		if (!is_dir($layoutPath)) mkdir($layoutPath);

		$sire->templater->template($this->viewTemplates['layout.blade.php'], $toTemplate, $layoutPath .$sire->viewTheme.'.blade.php');
	}

	public function reset($sire)
	{
		$path = app_path() . '/views/'.$sire->name->plural().'/';

		$names = array('create.blade.php', 'edit.blade.php', 'index.blade.php');
		foreach ($names as $name) {
			if (is_file($path.$name))
			{
				unlink($path.$name);
			}
		}
	}

}
