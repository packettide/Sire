<?php 
namespace Packettide\Sire\Generators;

use Symfony\Component\Finder\Finder;
use Illuminate\Database\Migrations\Migrator;

class MigrationGenerator {

	public function __construct(Finder $finder, Migrator $migrator)
	{
		$this->finder = $finder;
		$this->migrator = $migrator;
		$this->migrationTemplate = file_get_contents(__DIR__.'/../templates/migration.mustache');
	}

	public function run($sire)
	{
		$fields = $sire->assocToNumeric($sire->fields);

		$path = app_path() . '/database/migrations/';

		$this->finder = $this->finder->create();

		$this->finder->files()->in($path)->name('*_create_' . $sire->name->plural() . '_table.php');

		if($this->finder->count() != 0)
		{
			foreach ($this->finder as $file) {
				$migrationName = explode(".php", $file->getRelativePathname());
				if ($this->migrator->repositoryExists() && in_array($migrationName[0], $this->migrator->getRepository()->getRan()))
				{
					$mig = (object) array('migration' => $migrationName[0], 'batch' => 1);
					$method = new \ReflectionMethod($this->migrator, 'runDown');
					$method->setAccessible(true);

					$method->invoke($this->migrator, $mig, false);
				}					
				unlink($path.$file->getRelativePathname());
			}
		}
	
		$name = date('Y_m_d_His') . '_create_' . $sire->name->plural() . '_table.php';

		$toTemplate = array(
			"tableName" => $sire->name->plural(),
			"fields" => $fields,
			);

		$sire->templater->template($this->migrationTemplate, $toTemplate, $path.$name);
	}

}

 ?>