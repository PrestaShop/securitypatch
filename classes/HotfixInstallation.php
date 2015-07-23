<?php
/**
 * 2007-2015 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2015 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

/**
 * Install class.
 */
class HotfixInstallation
{
    /**
     * Install the HotFix module's tables.
     *
     * @return bool Success of the operation.
     */
    public function installTables()
    {
        $prefix = _DB_PREFIX_;
        $engine = _MYSQL_ENGINE_;

        $success = DB::getInstance()->execute("
            CREATE TABLE `{$prefix}hotfix_patche` (
                `id_hotfix_patche` INT NOT NULL AUTO_INCREMENT,
                `guid` VARCHAR(32) NULL,
                `date` DATE NULL,
                `status` TINYINT NULL,
                PRIMARY KEY (`id_hotfix_patche`)
            ) ENGINE={$engine} DEFAULT CHARSET=utf8
        ");

        return $success;
    }

    /**
     * Create a folder.
     *
     * Steps :
     * 1. Create the folder
     * 2. Copy the index.php file from this file's folder.
     *
     * @param string $path Path relative to the module's folder.
     * @return bool Success of the operation.
     */
    public function createFolder($path)
    {
        $folderPath = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$path;

        $success = true;

        if (!is_dir($folderPath)) {
            $success &= mkdir($folderPath, 0777, true);

            if ($success) {
                $success &= copy(dirname(__FILE__).DIRECTORY_SEPARATOR.'index.php', $folderPath.DIRECTORY_SEPARATOR.'index.php');
            }
        }

        return $success;
    }

    /**
     * Remove the HotFix module's tables.
     *
     * @return bool Success of the operation.
     */
    public function removeTables()
    {
        $prefix = _DB_PREFIX_;

        $success = DB::getInstance()->execute("
            DROP TABLE IF EXISTS `{$prefix}hotfix_patche`
        ");

        return $success;
    }

    /**
     * Remove a folder.
     *
     * @param string $path Path relative to the module's folder.
     * @return bool Success of the operation.
     */
    public function removeFolder($path)
    {
        $folderPath = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$path;

        if (is_dir($folderPath)) {
            return $this->removeFolderRecursively($folderPath);
        }

        return true;
    }

    /**
     * Register all needed hooks to the module.
     *
     * @param Module $module Module to anchor.
     * @param array $hooks Hooks list.
     * @return bool Success of the operation.
     */
    public function registerHooks($module, $hooks)
    {
        $success = true;

        foreach ($hooks as $hook) {
            $success &= $module->registerHook($hook);
        }

        return $success;
    }

    /**
     * Create a tab for the module.
     *
     * @param string $name Tab name.
     * @param string $className Target class
     * @param string $parentName Parent tab name.
     * @param Module $module Target module.
     * @return bool Success of the operation.
     */
    public function installTab($name, $className, $parentName, $module)
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $className;
        $tab->name = array();
        $tab->id_parent = (int)Tab::getIdFromClassName($parentName);
        $tab->module = $module->name;

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $name;
        }

        return $tab->add();
    }

    /**
     * Remove a tab for the module.
     *
     * @param string $className Class name of the tab.
     * @return bool Success of the operation.
     */
    public function uninstallTab($className)
    {
        $idTab = (int)Tab::getIdFromClassName($className);

        if ($idTab) {
            $tab = new Tab($idTab);
            return $tab->delete();
        }

        return false;
    }

    /**
     * Remove a folder and all this datas.
     *
     * @param string $folder Folder to remove.
     * @return bool Succcess of the operation.
     */
    private function removeFolderRecursively($folder) {
        if (is_dir($folder)) {
            $objects = scandir($folder);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (filetype($folder.DIRECTORY_SEPARATOR.$object) == 'dir') {
                        $this->removeFolderRecursively($folder.DIRECTORY_SEPARATOR.$object);
                    } else {
                        unlink($folder.DIRECTORY_SEPARATOR.$object);
                    }
                }
            }
            reset($objects);
            return rmdir($folder);
        }
    }
}
