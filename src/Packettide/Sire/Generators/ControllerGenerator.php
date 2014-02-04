<?php namespace Packettide\Sire\Generators;

class ControllerGenerator {

	private $templates = array();

	private $safe = array();

	public function __construct()
	{
	}

	public function setupTemplates($sire)
	{
		$this->templates[$sire->name->upper().'sController.php'] = file_get_contents(__DIR__.'/../templates/code/'.$sire->codeTheme.'/controller.mustache');

		$this->safe[$sire->name->upper().'sController.php'] = true;

		if(is_file(__DIR__.'/../templates/code/'.$sire->codeTheme.'/scaffolds/controller.mustache') && !is_file(app_path() . '/controllers/ScaffoldController.php'))
			$this->templates['ScaffoldController.php'] = file_get_contents(__DIR__.'/../templates/code/'.$sire->codeTheme.'/scaffolds/controller.mustache');
	}

	/**
	 * Generate Controller file from template
	 * @param  Packettide\Sire\Sire $sire
	 */
	public function run($sire)
	{
		$path = app_path() . '/controllers/';

		$toTemplate = array();

		foreach ($this->templates as $name => $template) {
			if (!$this->safe[$name] || !is_file($path.$name))
				$sire->templater->template($template, $toTemplate, $path.$name);
		}

	}

	public function reset($sire)
	{
		$path = app_path() . '/controllers/';
		$name = $sire->name->pluralUpper().'Controller.php';

		if (is_file($path.$name))
		{
			unlink($path.$name);
		}
	}

}
