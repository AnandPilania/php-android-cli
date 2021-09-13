<?php

namespace KSPEdu\PHPAndroidCli;

trait HelpersTrait
{
    protected function deleteExisting($target)
    {
        if (is_dir($target)) {
            $objs = scandir($target);
            foreach ($objs as $obj) {
                if ($obj != '.' && $obj != '..') {
                    if (is_dir($target . GenerateCommand::DS . $obj)) {
                        $this->deleteExisting($target . GenerateCommand::DS . $obj);
                    } else {
                        unlink($target . GenerateCommand::DS . $obj);
                    }
                }
            }
            rmdir($target);
        }
    }
    protected function copyr($src, $dest)
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

            $this->copyr($src . GenerateCommand::DS . $entry, $dest . GenerateCommand::DS . $entry);
        }

        $dir->close();
        return true;
    }
    protected function str_contains($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle != '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }
        return false;
    }
    protected function _mkdir($dest)
    {
        if (!file_exists($dest) || !is_dir($dest)) {
            //$this->_print_info("$dest\n");
            mkdir($dest);
        }
    }

    protected function _print_info($message)
    {
        $this->output->writeln('<info>' . $message . '</info>');
    }

    protected function isKotlin()
    {
        return strtoupper($this->getInputOption('type')) === 'KOTLIN';
    }

    protected function isLegacy()
    {
        return $this->getInputOption('legacy');
    }

    protected function getInputOption($option)
    {
        return $this->input->getOption($option);
    }

    protected function nameForAsset($moduleName)
    {
        return ucfirst(strtolower($moduleName) === 'app' ? $this->projectName : $moduleName);
    }
}
