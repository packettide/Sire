Sire
====
## Awesome YAML generation for Laravel

Sire allows you to generate models, views, controller, and migrations based on a YAML description file. This makes for repeatedable, tweakable generation. It generates views that utilize Bootstrap 3.

### Features

* Generate Bree-enhanced models
* Your models can have relationships and validation rules
* Your views will have nice real names, no more `Add new long_named_thing` buttons
* Auto generates excelent forms for creation and editing of models
* Show real names for fields (rather than variable names) and shows properties across relations

### Getting Started

1. Add `"packettide/sire": "@dev"` to the require block of your `composer.json`
2. Do a `composer update`
3. Add `'Packettide\Sire\SireServiceProvider',` to the end of your app config providers array
4. Setup a simple YAML file representing your model:

		_name: important_item

		title:
  		   sqlType: string
  		    bree:
    	        type: Text
    	        label: Title
  		    validation:
   		        - required


The `_name` field tells Sire what the name of the things (views, controller, model, table and routes) you are generating is
* It should be a singular, snake case, name. Sire will turn that into the other formats of the name it needs, including real words.

The `title` field is a parameter that will appear on the resulting model. It has an `sqlType` which tells the migration generator what format the column should be, see [here](http://laravel.com/docs/schema) for more info about the types availible. The `bree` field describes how the generated forms will interact with the parameter. The `type` is the Bree fieldtype that will represent the parameter. The `label` is simply the label. The `validation` field is a list of the rules that the parameter should obey. That small amount of YAML is enough to get you started with Sire.

### Advanced Usage

Generating a relation field is more complicated. This is an example of another model that relates to the previous one:

	_name: thing

	description:
	  sqlType: text
	  bree:
	    type: TextArea
	    label: A Little Information
	  validation:
	    - required
	important_thing:
	  sqlType: integer
	  realField:
	    field: importantThing
	    title: title
	  bree:
	    type: Relate
	    related: ImportantThing
	    select: true
	    title: title
	    label: An Important Thing
	  relationships:
	    name: important_thing
	    type: belongsTo
	    model: ImportantThing
	  validation:
	    - required
	    - integer

This sets up a textarea field and a Bree relate field that allows you to select an important_thing to relate.
