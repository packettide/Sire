<?php namespace Packettide\Sire;

/**
* 
*/
class Name
{
	
	protected $name;

	function __construct($name)
	{
		$this->name = $name->__toString();

		$this->generateNames();
	}

	private function generateNames()
	{
		if(!isset($this->Name))
		{
			$this->Name = \Str::studly($this->name);
		}

		if(!isset($this->names))
		{
			$this->names = Pluralizer::plural($this->name);
		}

		if(!isset($this->Names))
		{
			$this->Names = \Str::studly($this->names);
		}

		if(!isset($this->nameLiterate)) 
		{
			$this->nameLiterate = str_replace("_", " ", $this->name);
		}

		if(!isset($this->NameLiterate))
		{
			$this->NameLiterate = ucwords($this->nameLiterate);
		}

		if(!isset($this->namesLiterate))
		{
			$this->namesLiterate = Pluralizer::plural($this->nameLiterate);
		}

		if(!isset($this->NamesLiterate))
		{
			$this->NamesLiterate = ucwords($this->namesLiterate);
		}
	}

	public function __toString()
    {
        return $this->name;
    }

    public function plural()
    {
    	return $this->names;
    }

    public function pluralLiterate()
    {
    	return $this->namesLiterate;
    }

    public function pluralUpper()
    {
    	return $this->Names;
    }

    public function pluralLiterateUpper()
    {
    	return $this->NamesLiterate;
    }

    public function singular()
    {
    	return $this->name;
    }

    public function literate()
    {
    	return $this->nameLiterate;
    }

    public function upper()
    {
    	return $this->Name;
    }

    public function literateUpper()
    {
    	return $this->NameLiterate;
    	
    }
}