<?php namespace Packettide\Sire\Generators;

class ControllerGenerator {

	public function __construct()
	{
		$this->controllerTemplate = file_get_contents(__DIR__.'/../templates/controller.mustache');
	}

	/**
	 * Generate Controller file from template
	 * @param  Packettide\Sire\Sire $sire
	 */
	public function run($sire)
	{
		$path = app_path() . '/controllers/';
		$name = $sire->name->pluralUpper().'Controller.php';

		$toTemplate = array();

		$sire->templater->template($this->controllerTemplate, $toTemplate, $path.$name);
	}

}
