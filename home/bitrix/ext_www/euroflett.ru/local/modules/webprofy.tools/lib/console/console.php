<?
	namespace Webprofy\Tools\Console;

	use Symfony\Component\Console\Application;

	class Console{
		private $commands = array(
			'Webprofy\Tools\Console\Command\CreateModuleCommand',
			'Webprofy\Tools\Console\Command\DeleteModuleCommand',
			'Webprofy\Tools\Console\Command\CreateComponentCommand'
		);

		function run(){
			$app = new Application();
			foreach($this->commands as $command){
				$app->add(new $command());
			}
			$app->run();
		}
	}
?>