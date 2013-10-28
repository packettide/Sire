<?php 
namespace Packettide\Sire\Generators;

class ModelGenerator {

	public function __construct()
	{
		$this->modelTemplate = file_get_contents(__DIR__.'/../templates/model.mustache');
	}

	public function run($sire)
	{

		$fields = $sire->pluckWith('bree', $sire->fields, '_name');
		$relationships = $sire->pluckWith('relationships', $sire->fields, '_name');

		$relationships = array_map(function ($el) {
			$el['name'] = \Str::camel($el['name']);
			return $el;
		}, $relationships);

		$path = app_path() . '/models/';
		$name = $sire->name->upper().'.php';

		$toTemplate = array(
			"rules" => $sire->getRules(),
			"relationships" => $relationships,
			"breeFields" => $fields,
			);

		$sire->templater->template($this->modelTemplate, $toTemplate, $path.$name);
	}

}


 ?>