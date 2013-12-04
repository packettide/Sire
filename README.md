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

## Getting Started

1. Add `"packettide/sire": "@dev"` to the require block of your `composer.json`
2. Do a `composer update`
3. Add `'Packettide\Sire\SireServiceProvider',` to the end of your app config providers array
4. Setup a simple YAML file representing your model:

		_name: thing
		_makes:
			- model
			- view
			- controller
			- migration
			- route
		_viewTheme: bs3
		_codeTheme: default

		title:
			sqlType: string
			fieldType: Text
			label: Title
			validation:
				- required

The `_name` field tells Sire what the name of the things (views, controller, model, table and routes) you are generating is

* It should be a singular, snake case, name. Sire will turn that into the other formats of the name it needs, including real words.

In addition to these special paramters there are also the members of the model. The `title` field is a parameter that will appear on the resulting model. 

* It has an `sqlType` which tells the migration generator what format the column should be, see [here](http://laravel.com/docs/schema) for more info about the types availible. 
* The `fieldType` field describes how the generated forms will interact with the parameter. 
* The `label` is simply the label. 
* The `validation` field is a list of the rules that the parameter should obey. That small amount of YAML is enough to get you started with Sire.

## Advanced Usage

#### Field Options

	image:
		label: An Image
		sqlType: string
		fieldType: File
		fieldTypeOptions:
			directory: "/uploads/images"
			
Here we have a file field. File fields in Bree need to know what directory you want the upload to go to. In Sire we specify that as a `fieldTypeOptions`. We set a `fieldTypeOption` named `directory` and give it a value of `"/uploads/images"`. The same works for other options as well.

#### Adding a hasMany
	
	other:
      label: Other Thing
      fieldType: Cell
      relationshipType: hasMany
      relatedModel: Other


We give this field a fieldType of Cell, since it will represent a hasMany by giving you the option to create new instances of the Other model and attaching them to this model. We have to give it:

* `relationshipType` which tells Sire that this is a hasMany relationship
* `relatedModel` with tells Sire what class is going to be related on this relationship

#### Adding a belongsTo

	more:
		label: More Things
		fieldType: Relate
		fieldTypeOptions:
			select: true
			title: title
		relationshipType: belongsTo
		relatedModel: More

We are going to give this field a fieldType of Relate. A belongsTo will mean that the migration for this model will have a `more_id` with the type integer. We this field:

* `relationshipType` which tells Sire that it is a belongsTo
* `relatedModel` which tells Sire to relate it to the more class
* `fieldTypeOptions` telling Sire that we want this to be select relate rather than a radio button and we want to label each option with the title from that model.

