<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends Command
{
	private $dimensions = [], $flavors = [], $modules = ['app'];
	private $input, $output, $projectName, $pkgName;

	protected function configure()
	{
		$this->setName('create')
			->setDescription('Create android studio gradle project skeleton')
			->addArgument('project', InputArgument::REQUIRED, 'Provide the project name')
			->addArgument('pkg', InputArgument::REQUIRED, 'Provide the pkg name')
			->addOption('targetSdk', 'ts', InputOption::VALUE_OPTIONAL, 'Pass the targetSdk.', 29)
			->addOption('minSdk', 'ms', InputOption::VALUE_OPTIONAL, 'Pass the minSdk.', 16)
			->addOption('compileSdk', 'cs', InputOption::VALUE_OPTIONAL, 'Pass the compileSdk.', 29)
			->addOption('buildTools', 'bt', InputOption::VALUE_OPTIONAL, 'Pass the buildVersion.', '29.0.1')
			->addOption('androidX', 'x', InputOption::VALUE_OPTIONAL, 'Is androidX?', true)
			->addOption('jetifier', 'j', InputOption::VALUE_OPTIONAL, 'Jetifier enabled for AndroidX?', true)
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
		$this->copyr(__DIR__ . '/stubs/gradle', $this->projectName . '\gradle');
		copy(__DIR__ . '/stubs/gradlew',$this->projectName.'\gradlew');
		copy(__DIR__ . '/stubs/gradlew.bat',$this->projectName.'\gradlew.bat');
		$this->_mkdir($this->projectName . '\build');

		file_put_contents($this->projectName . '\build.gradle', "buildscript {\r\n\trepositories {\r\n\t\tgoogle()\r\n\t\tjcenter()\r\n\t}\r\n\tdependencies {\r\n\t\tclasspath 'com.android.tools.build:gradle:3.4.2'\r\n\t}\r\n}\r\n\nallprojects {\r\n\trepositories {\r\n\t\tgoogle()\r\n\t\tjcenter()\r\n\t}\r\n}\r\n\ntask clean(type: Delete) {\r\n\tdelete rootProject.buildDir\r\n}\r\n", 0);
		file_put_contents($this->projectName . '\.gitignore', "*.iml\r\n.gradle\r\n/local.properties\r\n/.idea/caches\r\n/.idea/libraries\r\n/.idea/modules.xml\r\n/.idea/workspace.xml\r\n/.idea/navEditor.xml\r\n/.idea/assetWizardSettings.xml\r\n.DS_Store\r\n/build\r\n/captures\r\n.externalNativeBuild\r\n/backup\r\n/priv", 0);
		file_put_contents($this->projectName . '\gradle.properties', "org.gradle.jvmargs=-Xmx1536m\r\nandroid.useAndroidX=" . ($this->input->getOption('androidX') ? "true" : "false") . "\r\nandroid.enableJetifier=" . ($this->input->getOption('jetifier') ? "true" : "false") . "\r\n", 0);

		$settingsContent = "include ";

		foreach ($this->modules as $key => $module) {
			$exModule = explode(':', $module);
			if ($exModule > 0) {
				$moduleName = $exModule[0];
				$moduleType = isset($exModule[1]) ? $exModule[1] : 'application';
			}

			$this->_mkdir($this->projectName . '\\' . $moduleName);

			file_put_contents($this->projectName . '\\' . $moduleName . '\.gitignore', "/build\n\n", 0);

			$isAndroidX = $this->input->getOption('androidX');

			$fdCount = 0;
			$flavorContent = '';
			$dimensionContent = '';
			if ($count = count($this->flavors)) {
				$flavorContent = "productFlavors {\r\n\t\t";
				$dimensionContent = 'flavorDimensions ';
				foreach ($this->flavors as $flavor => $dimension) {
					$dimenPushed = $this->str_contains($dimensionContent, "'$dimension'");
					if (!$dimenPushed) {
						$dimensionContent .= "'$dimension'";
					}
					$flavorContent .= $flavor . " {\r\n\t\t\tdimension '$dimension'\r\n\t\t}";

					if ($fdCount < ($count - 1)) {
						$fdCount = $fdCount + 1;
						if (!$dimenPushed) {
							$dimensionContent .= ", ";
						}
						$flavorContent .= "\r\n\t\t";
					}
				}
				$flavorContent .= "\r\n\t}\r\n";
			}
			$fdContent = $dimensionContent . "\r\n\t" . $flavorContent;

			file_put_contents(
				$this->projectName . '\\' . $moduleName . '\build.gradle',
				"apply plugin: 'com.android." . $moduleType . "'\r\n\n" .
					"android {\r\n\t" .
					"compileSdkVersion " . $this->input->getOption('compileSdk') . "\r\n\t" .
					"buildToolsVersion '" . $this->input->getOption('buildTools') . "'\r\n\t" .
					"defaultConfig {\r\n\t\t" . ($moduleType === 'library' ? '' : "applicationId '" . ($this->pkgName . ($moduleName === 'app' ? '' : '.' . $moduleName)) . "'\r\n\t\t") .
					"minSdkVersion " . $this->input->getOption('minSdk') . "\r\n\t\t" .
					"targetSdkVersion " . $this->input->getOption('targetSdk') . "\r\n\t\t" .
					"versionCode 1\r\n\t\t" .
					"versionName '1.0'\r\n\t\t" .
					"testInstrumentationRunner '" . ($isAndroidX ? 'androidx.test.runner.AndroidJUnitRunner' : '') . "'\r\n\t" .
					"}\r\n\n\t" .
					"buildTypes {\r\n\t\t" .
					"debug {\r\n\t\t\tdebuggable true\r\n\t\t}\r\n\t\t" .
					"release {\r\n\t\t\tdebuggable false\r\n\t\t\tminifyEnabled true\r\n\t\t\tshrinkResources true\r\n\t\t\tzipAlignEnabled true\r\n\t\t\tproguardFiles getDefaultProguardFile('proguard-android-optimize.txt'), 'proguard-rules.pro'\r\n\t\t}\r\n\t" .
					"}\r\n\n\t" .
					$fdContent .
					"}\r\n\n" .
					"dependencies {\r\n\t" .
					"implementation fileTree(dir: 'libs', include: ['*.jar'])\r\n\t" .
					"implementation '" . ($isAndroidX ? 'androidx.appcompat:appcompat:1.0.0' : '') . "'\r\n\t" .
					"testImplementation 'junit:junit:4.12'\r\n\t" .
					"androidTestImplementation '" . ($isAndroidX ? 'androidx.test:runner:1.2.0' : '') . "'\r\n\t" .
					"androidTestImplementation '" . ($isAndroidX ? 'androidx.test.espresso:espresso-core:3.2.0' : '') . "'\r\n" .
					"}",
				0
			);
			file_put_contents($this->projectName . '\\' . $moduleName . '\proguard-rules.pro', "\r\n", 0);

			$this->_mkdir($this->projectName . '\\' . $moduleName . '\src');
			$this->_mkdir($this->projectName . '\\' . $moduleName . '\build');
			$this->_mkdir($this->projectName . '\\' . $moduleName . '\libs');

			$this->_mkdir($this->projectName . '\\' . $moduleName . '\src\androidTest');
			$this->_mkdir($this->projectName . '\\' . $moduleName . '\src\test');
			$this->_mkdir($this->projectName . '\\' . $moduleName . '\src\main');
			file_put_contents(
				$this->projectName . '\\' . $moduleName . '\src\main\AndroidManifest.xml',
				'<manifest xmlns:android="http://schemas.android.com/apk/res/android" ' . "\r\n\t" . 'package="' . $this->pkgName . ('app' === $moduleName ? '' : '.' . $moduleName) . '">' . "\r\n\n\t" . ($moduleType === 'library' ? '' : '<application' . "\r\n\t\t" .
						'android:allowBackup="true"' . "\r\n\t\t" .
						'android:icon="@mipmap/ic_launcher"' . "\r\n\t\t" .
						'android:label="@string/app_name"' . "\r\n\t\t" .
						'android:roundIcon="@mipmap/ic_launcher_round"' . "\r\n\t\t" .
						'android:supportsRtl="true"' . "\r\n\t\t" .
						'android:theme="@style/AppTheme">' . "\r\n\t\t\t" .
						"\r\n\t</application>")
					. "\r\n</manifest>\r\n",
				0
			);

			$this->_mkdir($this->projectName . '\\' . $moduleName . '\src\main\java');
			$exPkg = explode('.', $this->pkgName);
			$_dir = $this->projectName . '\\' . $moduleName . '\src\main\java';
			$_newDir = '';
			foreach ($exPkg as $key => $pkgDir) {
				$_newDir .= '\\' . $pkgDir;
				$this->_mkdir($_dir . $_newDir);
				if ($key === (count($exPkg) - 1) && $moduleName !== 'app') {
					$this->_mkdir($_dir . $_newDir . '\\' . $moduleName);
				}
			}

			if ($moduleType === 'library') {
				$this->_mkdir($this->projectName . '\\' . $moduleName . '\src\main\res');
				$this->_mkdir($this->projectName . '\\' . $moduleName . '\src\main\res\drawable');
				$this->_mkdir($this->projectName . '\\' . $moduleName . '\src\main\res\layout');
				$this->_mkdir($this->projectName . '\\' . $moduleName . '\src\main\res\values');
			} else {
				$this->copyr(__DIR__ . '/stubs/res', $this->projectName . '\\' . $moduleName . '\src\main\res');
			}
			file_put_contents($this->projectName . '\\' . $moduleName . '\src\main\res\values\strings.xml', "<resources>\r\n\t" . '<string name="app_name">' . ucfirst($moduleName) . "</string>\r\n</resources>\r\n", 0);

			$settingsContent .= "':" . $moduleName . "'";

			if ($key < (count($this->modules) - 1)) {
				$settingsContent .= ",";
			}
		}
		file_put_contents($this->projectName . '\settings.gradle', $settingsContent . "\r\n", 0);
		// file_put_contents($this->projectName . '\local.properties', "ndk.dir=E\:\\SDK\\ndk-bundle\r\nsdk.dir=E\:\\SDK\r\n", 0);

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
						$this->output('<fg=red>Flavors must-be followed by specific dimension</>');
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
	private function deleteExisting($target)
	{
		if (is_dir($target)) {
			$objs = scandir($target);
			foreach ($objs as $obj) {
				if ($obj != '.' && $obj != '..') {
					if (is_dir($target . '/' . $obj)) {
						$this->deleteExisting($target . '/' . $obj);
					} else {
						unlink($target . '/' . $obj);
					}
				}
			}
			rmdir($target);
		}
	}
	private function copyr($src, $dest)
	{
		if (is_link($src)) {
			return symlink(readlink($src), $dest);
		}

		if (is_file($src)) {
			return copy($src, $dest);
		}

		$this->_mkdir($dest);

		$dir = dir($src);
		while (false !== $entry = $dir->read()) {
			if ($entry == '.' || $entry == '..') {
				continue;
			}

			$this->copyr("$src/$entry", "$dest/$entry");
		}

		$dir->close();
		return true;
	}
	private function str_contains($haystack, $needles)
	{
		foreach ((array) $needles as $needle) {
			if ($needle != '' && mb_strpos($haystack, $needle) !== false) {
				return true;
			}
		}
		return false;
	}
	private function _mkdir($dest)
	{
		if (!file_exists($dest) || !is_dir($dest)) {
			mkdir($dest);
		}
	}
}
