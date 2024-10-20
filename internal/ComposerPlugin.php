<?php

namespace iocui\vqmod;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Script\ScriptEvents;
use Composer\Factory;

class ComposerPlugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var IOInterface
     */
    private $io;

    private static $activated = false;

    private static $editFile = ['vendor/autoload.php'];

    // 激活事件
    public function activate(Composer $composer, IOInterface $io): void
    {
        self::$activated = true;
        $this->composer = $composer;
        $this->io = $io;
    }

    // 停用事件
    public function deactivate(Composer $composer, IOInterface $io): void
    {
        self::$activated = false;
    }

    // 删除事件
    public function uninstall(Composer $composer, IOInterface $io): void
    {
        self::$activated = false;

        $projectPath = \dirname(realpath(Factory::getComposerFile())) . DIRECTORY_SEPARATOR;
        if (file_exists($projectPath . 'runtime/_vqmod.mods')) {
            unlink($projectPath . 'runtime/_vqmod.mods');
        }
        if (file_exists($projectPath . 'runtime/_vqmod.checked')) {
            unlink($projectPath . 'runtime/_vqmod.checked');
        }
        if (is_dir($projectPath . 'runtime/_vqmod.cache/')) {
            array_map('unlink', glob($projectPath . 'runtime/_vqmod.cache/*'));
            rmdir($projectPath . 'runtime/_vqmod.cache/');
        }
        if (is_dir($projectPath . 'runtime/_vqmod.logs/')) {
            array_map('unlink', glob($projectPath . 'runtime/_vqmod.logs/*'));
            rmdir($projectPath . 'runtime/_vqmod.logs/');
        }
    }

    public function updateAutoloadFile(): void
    {
        $ugrsrFile = __DIR__ . '/../source/install/ugrsr.class.php';
        $vendorPath = realpath($this->composer->getConfig()->get('vendor-dir'));

        if (!is_file($autoloadFile = $vendorPath . '/autoload.php') || !is_file($ugrsrFile)) {
            return;
        }        

        $projectPath = \dirname(realpath(Factory::getComposerFile())) . DIRECTORY_SEPARATOR;

        // *Counters
        $writes = 0;
        $changes = 0;

        if (!is_dir($projectPath . 'runtime')) {
            mkdir($projectPath . 'runtime');
        }

        if (file_exists($projectPath . 'runtime/_vqmod.mods')) {
            unlink($projectPath . 'runtime/_vqmod.mods');
        }

        $write_file = 'vendor/autoload.php';
        $autoloadTemplate = __DIR__.'/autoload_vqmod.template';
        $code = file_get_contents($autoloadTemplate);

        // $content = file_get_contents($autoloadFile);
        // file_put_contents(substr_replace($autoloadFile, '_vqmod', -4, 0), $content);
        // $write_file = 'vendor/autoload_vqmod.php';

        require_once $ugrsrFile;

        // *Create new UGRSR class
        $u = new \UGRSR($projectPath);

        // *remove the # before this to enable debugging info
        ### $u->debug = true;

        // *Set file searching to off
        $u->file_search = false;

        $u->addFile($write_file);

        // *Pattern to run required files through vqmod
        $u->addPattern('/require_once([^\;]+)/', $code);

        // *Get number of changes during run
        $result = $u->run();
        if (is_array($result)) {
            $changes += $result['changes'];
            $writes += $result['writes'];
        }

        // *output result to user
        if (!$changes) {
            echo 'VQMod has been installed on your system!' . PHP_EOL;
        } else {
            if ($writes < 1) {
                echo 'VQMod unable to write to one or more files.' . PHP_EOL;
            } else {
                echo 'VQMod install succee!' . PHP_EOL;
            }
        }
    }

    // 获取订阅事件
    public static function getSubscribedEvents(): array
    {
        if (!self::$activated) {
            return [];
        }

        return [
            ScriptEvents::POST_AUTOLOAD_DUMP => 'updateAutoloadFile',
        ];
    }
}