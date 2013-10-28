<?php 
namespace Packettide\Sire\Generators;

class MigrationGenerator {

	public function __construct()
	{
		$this->migrationTemplate = file_get_contents(__DIR__.'/../templates/migration.mustache');
	}

	public function run($sire)
	{
		$fields = $sire->assocToNumeric($sire->fields);

		$path = app_path() . '/database/migrations/';
		$name = date('Y_m_d_His') . '_create_' . $sire->name->plural() . '_table.php';

		$toTemplate = array(
			"tableName" => $sire->name->plural(),
			"fields" => $fields,
			);

		$sire->templater->template($this->modelTemplate, $toTemplate, $path.$name);
	}

}

 ?>