<?php namespace Packettide\Sire\Generators;

class ModelGenerator {

	public function __construct()
	{
		$this->modelTemplate = file_get_contents(__DIR__.'/../templates/code/model.mustache');
	}

	/**
	 * Generate Model file from template
	 * @param  Packettide\Sire\Sire $sire
	 */
	public function run($sire)
	{
		$fields = $sire->fields;
		$tempRelationships = $sire->fields;
		$relationships = array();

		foreach ($tempRelationships as $key => $value) {
			if (isset($value['relationshipType']) && isset($value['relatedModel']))
			{
				$temp = array();
				$temp['_name'] = \Str::camel($value['_name']);
				$temp['type'] = $value['relationshipType'];
				$temp['model'] = $value['relatedModel'];
				array_push($relationships, $temp);
			}
		}

		$fields = array_map(function ($el) {
			if (isset($el['fieldTypeOptions'])) {
				$el['breeAttrs'] = array_map(function ($v, $k) {
					if (!($v === true || $v === false))
					{
						$v = "'$v'";
					}
					return sprintf("'%s' => %s", $k, $v);
				}, $el['fieldTypeOptions'], array_keys($el['fieldTypeOptions']));
			}
			$el['breeAttrs'] = (isset($el['breeAttrs']))? $el['breeAttrs'] : array();
			if (isset($el['relatedModel']))
			{
				array_push($el['breeAttrs'], "'related' => '${el['relatedModel']}'");
			}
			$el['breeAttrs'] = implode(", ", $el['breeAttrs']);
			return $el;
		}, $fields);

		$fields = $sire->assocToNumeric($fields);

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
