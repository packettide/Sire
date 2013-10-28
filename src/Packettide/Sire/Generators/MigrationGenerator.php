<?php 
namespace Packettide\Sire\Generators;

use Symfony\Component\Finder\Finder;

class MigrationGenerator {

	public function __construct(Finder $finder)
	{
		$this->finder = $finder;
		$this->migrationTemplate = file_get_contents(__DIR__.'/../templates/migration.mustache');
	}

	public function run($sire)
	{
		$fields = $sire->assocToNumeric($sire->fields);

		$path = app_path() . '/database/migrations/';

		$finder->files()->in($path)->name('*_create_' . $sire->name->plural() . '_table.php');

		if(iterator_count($finder) != 0)
		{
			$name = $finder->current()->getRelativePathname();
		}
		else
		{
			$name = date('Y_m_d_His') . '_create_' . $sire->name->plural() . '_table.php';
		}

		$toTemplate = array(
			"tableName" => $sire->name->plural(),
			"fields" => $fields,
			);

		$sire->templater->template($this->migrationTemplate, $toTemplate, $path.$name);
	}

}

 ?>