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
            CREATE TABLE IF NOT EXISTS `{$prefix}hotfix_patches_installed` (
                `id_patch` VARCHAR(32),
                `installed` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0
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
            $success &= mkdir($folderPath, 0700, true);

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

        var_dump("DROP TABLE IF EXISTS `{$prefix}upgrade_checklist`");

        $success = DB::getInstance()->execute("
            DROP TABLE IF EXISTS `{$prefix}upgrade_checklist`
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

        return rmdir($folderPath);
    }
}
