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
        // ToDo Codeing
    }

    public function updateAutoloadFile(): void
    {
        $ugrsrFile = __DIR__.'/../source/install/ugrsr.class.php';
        $vendorPath = realpath($this->composer->getConfig()->get('vendor-dir'));

        if (!is_file($autoloadFile = $vendorPath.'/autoload.php') || !is_file($ugrsrFile)) {
            return;
        }

        $projectPath = \dirname(realpath(Factory::getComposerFile())) . DIRECTORY_SEPARATOR;
      
        require($ugrsrFile);

        // *Counters
        $writes = 0;
        $changes = 0;
        $write_files = [];
        $write_errors = [];
        foreach (self::$editFile as $file) {
            if (!is_file($projectPath . $file) || !is_writeable($projectPath . $file)) {
                $write_errors[] = sprintf('File "%s" not writeable', $file);
            } else {
                $write_files[] = $file;
            }
        }

        if (!empty($write_errors)) {
            throw new \InvalidArgumentException(implode(PHP_EOL, $write_errors));
        }

        if (!is_dir($projectPath . 'runtime')) {
            mkdir($projectPath . 'runtime');
        }

        // *Create new UGRSR class
        $u = new \UGRSR($projectPath);

        // *remove the # before this to enable debugging info
        #$u->debug = true;

        // *Set file searching to off
        $u->file_search = false;

        foreach ($write_files as $write_file) {
            $u->clearPatterns();
            $u->resetFileList();

            $u->addFile($write_file);

            // Pattern to run required files through vqmod
            $u->addPattern('/require_once\s+__DIR__\s*\.\s*\'([^\']+)\'\;/', '// VirtualQMOD
if(is_file(__DIR__ . \'/iocui/vqmod/init.php\')){
    // VQMODDED Startup https://gitee.com/iocui/vqmod.git
    require_once(__DIR__ . \'/iocui/vqmod/init.php\');
    require_once(\VQModInit::load(__DIR__ . \'$1\'));
}else{
    // original require
    require_once(__DIR__ . \'$1\');
}');

            // *Get number of changes during run
            $result = (array)$u->run();
            if ($result) {
                $changes += $result['changes'];
                $writes += $result['writes'];
            }
        }

        if (file_exists($projectPath . 'runtime/_vqmod.mods')) {
            unlink($projectPath . 'runtime/_vqmod.mods');
        }

        if(is_dir($projectPath . 'runtime/_vqmod.cache/')){
            array_map('unlink', glob($projectPath . 'runtime/_vqmod.cache/*'));
        }

        // *output result to user
        if (!$changes){
            echo 'VQMod has been installed on your system!' . PHP_EOL;
        }else{
            if ($writes < 1){
                echo 'VQMod unable to write to one or more files.' . PHP_EOL;
            }else{
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