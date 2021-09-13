<?php

namespace KSPEdu\PHPAndroidCli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends Command
{
	use HelpersTrait, ChangableTrait;

	private $dimensions = [], $flavors = [], $modules = ['app'];
	private $input, $output, $projectName, $pkgName;

	const DS = DIRECTORY_SEPARATOR;
	private $stubsPath = __DIR__ . self::DS . '..' . self::DS . 'stubs' . self::DS;

	protected function configure()
	{
		$this->setName('create')
			->setDescription('Create android studio gradle project skeleton')
			->addArgument('project', InputArgument::REQUIRED, 'Provide the project name')
			->addArgument('pkg', InputArgument::REQUIRED, 'Provide the pkg name')
			->addOption('targetSdk', 'ts', InputOption::VALUE_OPTIONAL, 'Pass the targetSdk.', 31)
			->addOption('minSdk', 'ms', InputOption::VALUE_OPTIONAL, 'Pass the minSdk.', 21)
			->addOption('compileSdk', 'cs', InputOption::VALUE_OPTIONAL, 'Pass the compileSdk.', 31)
			->addOption('buildTools', 'bt', InputOption::VALUE_OPTIONAL, 'Pass the buildVersion.', '31.0.0')
			->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'JAVA or KOTLIN', 'kotlin')
			->addOption('legacy', 'l', InputOption::VALUE_OPTIONAL, 'Using Legacy deps?', false)
			->addOption('modules', 'm', InputOption::VALUE_OPTIONAL, 'Comma-seperated modules [with semi-colon seperated type] (backend:library,client)')
			->addOption('flavors', 'f', InputOption::VALUE_OPTIONAL, 'Comma-seperated flavors followed by dimension with semi-colon (free:type,paid:type,php:backend,firebase:backend)')
			->addOption('force', null)
			->setHelp(
				<<<EOT
The <info>%command.name%</info> command helps you generates new Android-Studio Gradle Project.

By default, the command interacts with the developer to tweak the generation.
Any passed option will be used as a default value for the interaction
(<comment>project</comment> & <comment>pkg</comment> is the only two needed if you follow the conventions):

<info>php %command.full_name% HelloWorld com.example.helloworld</info>

If you want to disable any user interaction, use <comment>--no-interaction</comment>
EOT
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->input = $input;
		$this->output = $output;

		$this->projectName = $input->getArgument('project');
		$this->pkgName = $input->getArgument('pkg');

		$this->generateOptions();
		$this->createProject();

		return 0;
	}

	private function createProject()
	{
		if (is_dir($this->projectName)) {
			if (!$this->input->getOption('force')) {
				$this->output->writeln('<fg=red>Project ' . $this->projectName . ' is already exists!</>');
				$this->output->writeln('<info>Use: --force/-ff for forcefully delete an existing project!</info>');
				exit();
			} else {
				$this->deleteExisting($this->projectName);
			}
		}

		$this->_mkdir($this->projectName);
		$this->copyr($this->stubsPath . 'gradle', $this->projectName . self::DS . 'gradle');
		copy($this->stubsPath . 'gradlew', $this->projectName . self::DS . 'gradlew');
		copy($this->stubsPath . 'gradlew.bat', $this->projectName . self::DS . 'gradlew.bat');
		copy($this->stubsPath . '.gitignore', $this->projectName . self::DS . '.gitignore');
		$this->_mkdir($this->projectName . self::DS . 'build');

		file_put_contents($this->projectName . self::DS . 'build.gradle', $this->projectBuildGradle($this->isKotlin()), 0);
		file_put_contents($this->projectName . self::DS . 'gradle.properties', $this->gradleProperties($this->isKotlin()), 0);

		$settingsContent = "include ";

		foreach ($this->modules as $key => $module) {
			$exModule = explode(':', $module);
			if ($exModule > 0) {
				$moduleName = $exModule[0];
				$moduleType = isset($exModule[1]) ? $exModule[1] : 'application';
			}

			$this->_mkdir($this->projectName . self::DS . $moduleName);

			file_put_contents($this->projectName . self::DS . $moduleName . self::DS . '.gitignore', "/build\n\n", 0);

			$fdCount = 0;
			$flavorContent = '';
			$dimensionContent = '';
			if ($count = count($this->flavors)) {
				$flavorContent = "productFlavors {\n\t\t";
				$dimensionContent = 'flavorDimensions ';
				foreach ($this->flavors as $flavor => $dimension) {
					$dimenPushed = $this->str_contains($dimensionContent, "'$dimension'");
					if (!$dimenPushed) {
						$dimensionContent .= "'$dimension'";
					}
					$flavorContent .= $flavor . " {\n\t\t\tdimension '$dimension'\n\t\t}";

					if ($fdCount < ($count - 1)) {
						$fdCount = $fdCount + 1;
						if (!$dimenPushed) {
							$dimensionContent .= ", ";
						}
						$flavorContent .= "\n\t\t";
					}
				}
				$flavorContent .= "\n\t}\n";
			}
			$fdContent = $dimensionContent . "\n\t" . $flavorContent;

			file_put_contents(
				$this->projectName . self::DS . $moduleName . self::DS . 'build.gradle',
				$this->appBuildGradle($this->isKotlin()),
				0
			);
			file_put_contents($this->projectName . self::DS . $moduleName . self::DS . 'proguard-rules.pro', $this->proguardFile(), 0);

			$this->_mkdir($this->projectName . self::DS . $moduleName . self::DS . 'src');
			$this->_mkdir($this->projectName . self::DS . $moduleName . self::DS . 'build');
			$this->_mkdir($this->projectName . self::DS . $moduleName . self::DS . 'libs');

			$this->_mkdir($this->projectName . self::DS . $moduleName . self::DS . 'src/androidTest');
			$this->_mkdir($this->projectName . self::DS . $moduleName . self::DS . 'src/test');
			$this->_mkdir($this->projectName . self::DS . $moduleName . self::DS . 'src/main');

			file_put_contents(
				$this->projectName . self::DS . $moduleName . self::DS . 'src/main/AndroidManifest.xml',
				$moduleType === 'application' ? $this->manifestApplicationFile($this->nameForAsset($moduleName)) : $this->manifestModuleFile($moduleName, $moduleType),
				0
			);

			$this->_mkdir($this->projectName . self::DS . $moduleName . self::DS . 'src/main/java');
			$exPkg = explode('.', $this->pkgName);
			$_dir = $this->projectName . self::DS . $moduleName . self::DS . 'src/main/java';
			$_newDir = '';
			foreach ($exPkg as $key => $pkgDir) {
				$_newDir .= self::DS . $pkgDir;
				$this->_mkdir($_dir . $_newDir);
				if ($key === (count($exPkg) - 1) && $moduleName !== 'app') {
					$this->_mkdir($_dir . $_newDir . self::DS . $moduleName);
				}
			}

			if ($moduleType === 'library') {
				$this->_mkdir($this->projectName . self::DS . $moduleName . self::DS . 'src' . self::DS . 'main' . self::DS . 'res');
				$this->_mkdir($this->projectName . self::DS . $moduleName . self::DS . 'src' . self::DS . 'main' . self::DS . 'res' . self::DS . 'drawable');
				$this->_mkdir($this->projectName . self::DS . $moduleName . self::DS . 'src' . self::DS . 'main' . self::DS . 'res' . self::DS . 'values');
			} else {
				$this->copyr($this->stubsPath . 'res', $this->projectName . self::DS . $moduleName . self::DS . 'src' . self::DS . 'main' . self::DS . 'res');
			}
			file_put_contents($this->projectName . self::DS . $moduleName . self::DS . 'src/main/res/values/strings.xml', $this->stringsFile($this->nameForAsset($moduleName)), 0);
			file_put_contents($this->projectName . self::DS . $moduleName . self::DS . 'src/main/res/values/themes.xml', $this->themesFile($this->nameForAsset($moduleName)), 0);
			file_put_contents($this->projectName . self::DS . $moduleName . self::DS . 'src/main/res/values-night/themes.xml', $this->themesFile($this->nameForAsset($moduleName), true), 0);

			$settingsContent .= "':" . $moduleName . "'";

			if ($key < (count($this->modules) - 1)) {
				$settingsContent .= ",";
			}
		}
		file_put_contents($this->projectName . self::DS . 'settings.gradle', $settingsContent . "\n", 0);
		// file_put_contents($this->projectName . DIRECTORY_SEPARATOR . 'local.properties', "ndk.dir=E\:\\SDK\\ndk-bundle\nsdk.dir=E\:\\SDK\n", 0);

		$this->output->writeln('<info>' . $this->projectName . ' created successfully!</info>');
	}

	private function generateOptions()
	{
		$this->generateFlavors($this->input->getOption('flavors'));
		$this->generateModules($this->input->getOption('modules'));
	}

	private function generateFlavors($flavors = null)
	{
		if ($flavors) {
			$flavors = explode(',', $flavors);
			if (is_array($flavors) && count($flavors)) {
				foreach ($flavors as $flavor) {
					$exFlavor = explode(':', $flavor);
					if (!isset($exFlavor[1])) {
						$this->output->writeln('<fg=red>Flavors must-be followed by specific dimension</>');
						exit();
					}

					$this->flavors[$exFlavor[0]] = $exFlavor[1];
				}
			}
		}
	}
	private function generateModules($modules = null)
	{
		if ($modules) {
			$modules = explode(',', $modules);
			if (is_array($modules) && count($modules)) {
				$_mergedModules = array_merge($this->modules, $modules);
				$this->modules = $_mergedModules;
			}
		}
	}
}
