<?php namespace Packettide\Sire\Generators;

use Symfony\Component\Finder\Finder;
use Illuminate\Database\Migrations\Migrator;

class MigrationGenerator {

	public function __construct(Finder $finder, Migrator $migrator)
	{
		$this->finder = $finder;
		$this->migrator = $migrator;
		$this->migrationTemplate = file_get_contents(__DIR__.'/../templates/code/migration.mustache');
	}

	/**
	 * Generate Migration file from template
	 * @param  Packettide\Sire\Sire $sire
	 */
	public function run($sire)
	{
		$tempFields = $sire->assocToNumeric($sire->fields);
		$fields = array();

		$path = app_path() . '/database/migrations/';

		foreach ($tempFields as $field)
		{
			$temp = $field;
			if (isset($temp['relationshipType']) && $temp['relationshipType'] === 'belongsTo') 
			{
				$temp['sqlType'] = 'integer';
				$temp['_name'] = $temp['_name'] . '_id';
				array_push($fields, $temp);
			} 
			else if (!isset($temp['relationshipType']))
			{
				array_push($fields, $temp);
			}
		}

		$this->reset($sire);

		$name = date('Y_m_d_His') . '_create_' . $sire->name->plural() . '_table.php';

		$toTemplate = array(
			"tableName" => $sire->name->plural(),
			"fields" => $fields,
		);

		$sire->templater->template($this->migrationTemplate, $toTemplate, $path.$name);
	}

	public function reset($sire)
	{
		$path = app_path() . '/database/migrations/';

		// @max - any reason for $finder to be set as class var here? is state used somewhere else?
		// @TODO - WAT how is this code so messy?
		$this->finder = $this->finder->create();
		$this->finder->files()->in($path)->name('*_create_' . $sire->name->plural() . '_table.php');

		if($this->finder->count() != 0)
		{
			foreach ($this->finder as $file)
			{
				$migrationName = explode(".php", $file->getRelativePathname());

				if ($this->migrator->repositoryExists() && in_array($migrationName[0], $this->migrator->getRepository()->getRan()))
				{
					if ($sire->command->option('sketchy') || $sire->command->confirm("This is will down {$migrationName[0]}. Do you wish to continue? [yes|no]"))
					{
						$mig = (object) array('migration' => $migrationName[0], 'batch' => 1);
						$method = new \ReflectionMethod($this->migrator, 'runDown');
						$method->setAccessible(true);

						$method->invoke($this->migrator, $mig, false);
					}
					else
					{
						throw new \Exception("Please resolve the conflicting migration and try again.", 1);
					}
				}

				if (is_file($path.$file->getRelativePathname()))
				{
					unlink($path.$file->getRelativePathname());
				}
			}
		}
	}

}
