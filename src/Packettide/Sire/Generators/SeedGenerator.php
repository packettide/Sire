<?php namespace Packettide\Sire\Generators;

class SeedGenerator {

	public function __construct()
	{
		$this->controllerTemplate = file_get_contents(__DIR__.'/../templates/code/seed.mustache');
	}

	/**
	 * Generate Controller file from template
	 * @param  Packettide\Sire\Sire $sire
	 */
	public function run($sire)
	{
		$path = app_path() . '/database/seeds/';
		$name = $sire->name->pluralUpper().'TableSeeder.php';

		$seeds = array();

		foreach ($sire->seeds as $seed) {
			$curSeed = array();
			foreach ($seed as $key => $value) {
				array_push($curSeed, array('_name' => $key, '_value' => $value));
			}
			array_push($seeds, array('fields' => $curSeed));
		}

		$toTemplate = array(
			'seeds' => $seeds,
		);

		$this->updateBaseSeeder($sire);

		$sire->templater->template($this->controllerTemplate, $toTemplate, $path.$name);
	}

	// Based on code from the Jeffery Way project
	// https://github.com/JeffreyWay/Laravel-4-Generators/blob/master/src/Way/Generators/Generators/SeedGenerator.php
	public function updateBaseSeeder($sire)
	{
		$databaseSeederPath = app_path() . '/database/seeds/DatabaseSeeder.php';

		$className = $sire->name->pluralUpper().'TableSeeder';

        $content = file_get_contents($databaseSeederPath);

        if ( ! strpos($content, "\$this->call('{$className}');"))
        {
            $content = preg_replace("/(run\(\).+?)}/us", "$1\t\$this->call('{$className}');\n\t}", $content);
            return file_put_contents($databaseSeederPath, $content);
        }
	}

	public function reset($sire)
	{
		$path = app_path() . '/database/seeds/';
		$name = $sire->name->pluralUpper().'TableSeeder.php';
		if (is_file($path.$name))
		{
			unlink($path.$name);
		}
	}

}
