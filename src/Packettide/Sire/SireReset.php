<?php namespace Packettide\Sire;


use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Finder\Finder;

class SireReset extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'sire:reset';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Undo a sire generation';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(Sire $sire)
	{
		parent::__construct();
		$this->sire = $sire;
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$this->sire->cmd($this);

		// If given a directory loop through all YAML files within that directory
		if (is_dir($this->argument('yaml')))
		{
			$finder = new Finder();
			$finder->files()->in($this->argument('yaml'))->name("*.yaml")->name("*.yml");
			foreach ($finder as $yaml) {
				$this->sire->reset();
				$this->sire->with($yaml)->remove();
			}
		}
		else
		{
			$this->sire->with($this->argument('yaml'))->remove();
		}
		$this->call('optimize');
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('yaml', InputArgument::REQUIRED, 'The location of the yaml file describing your models.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('sketchy', 's', InputOption::VALUE_NONE, 'Dont warn about downing migrations'),
		);
	}

}