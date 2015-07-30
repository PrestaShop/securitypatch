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
 * Patches management class.
 */
class HotfixPatches
{
    /** @var HotfixSettings Settings object. */
    private $settings;

    /** @var string Patch folder. */
    private $patchFolder;

    /**
     * Constructor.
     *
     * @param HotfixSettings $settings Settings object.
     */
    public function __construct($settings)
    {
        $this->settings = $settings;

        $this->patchFolder = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$this->settings->get('paths/patches');
    }

    /**
     * Refresh the patches list.
     */
    public function refreshPatchesList()
    {
        $prefix = _DB_PREFIX_;

        $allPatchesList = include dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'list.php';
        $patchesList = isset($allPatchesList[_PS_VERSION_]) ? $allPatchesList[_PS_VERSION_] : null;

        if ($patchesList) {
            $currentPatchesList = $this->getAllPatchesList();

            if ($patchesList !== null) {
                foreach ($patchesList as $currentPatch) {
                    $alreadyDefined = false;
                    if ($currentPatchesList !== null) {
                        foreach ($currentPatchesList as $currentDefinedPatch) {
                            if ($currentDefinedPatch['guid'] == $currentPatch) {
                                $alreadyDefined = true;
                            }
                        }
                    }
                    if (!$alreadyDefined) {
                        $guid = pSQL($currentPatch);
                        $date = date('Y-m-d');
                        Db::getInstance()->execute("
                            INSERT INTO `{$prefix}hotfix_patche` (
                                `guid`,
                                `date`,
                                `status`
                            ) VALUES (
                                '{$guid}',
                                '{$date}',
                                '0'
                            );
                        ");
                    }
                }
            }
        }
    }

    /**
     * Return all the patches list recorded.
     *
     * @return array Patches list.
     */
    public function getAllPatchesList()
    {
        $prefix = _DB_PREFIX_;

        return Db::getInstance()->executeS("
            SELECT
                `id_hotfix_patche`,
                `guid`,
                `date`,
                `status`
            FROM
                `{$prefix}hotfix_patche`
        ");
    }

    /**
     * Return the patches list to do.
     *
     * @return array Patches list.
     */
    public function getPatchesToDo()
    {
        $prefix = _DB_PREFIX_;

        return Db::getInstance()->executeS("
            SELECT
                `id_hotfix_patche`,
                `guid`,
                `date`,
                `status`
            FROM
                `{$prefix}hotfix_patche`
            WHERE
                `status` = 0
        ");
    }

    /**
     * Return total of patches to do.
     *
     * @return int Total.
     */
    public function getTotalPatchesToDo()
    {
        return count($this->getPatchesToDo());
    }

    /**
     * Return the first patch to do.
     *
     * @return array Patch details.
     */
    public function getFirstPatchToDo()
    {
        $patchesToDo = $this->getPatchesToDo();

        return $patchesToDo[0];
    }

    /**
     * Install the wanted patch.
     *
     * @param array $patchDetails Patch details (SQL row).
     * @return bool Success of the operation
     */
    public function installPatch($patchDetails)
    {
        $patchZip = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$patchDetails['guid'].'.zip';

        return Tools::ZipExtract($patchZip, $this->patchFolder)
            && $this->backupFilesForPatch($patchDetails['guid'])
            && $this->preparePatchForShop()
            && $this->executePatch($patchDetails['guid']);
    }

    /**
     * Backup the files for a unzipped patch.
     *
     * @param string $guid GUID of the patch.
     * @return bool Success of the operation.
     */
    final private function backupFilesForPatch($guid)
    {
        $filesList = json_decode(Tools::file_get_contents($this->patchFolder.DIRECTORY_SEPARATOR.'files_list.json'), true);

        HotfixClassesLoader::loadClass('Backup');
        $backup = new HotfixBackup($this->settings);
        return $backup->backupFilesForPatch($guid, $filesList);
    }

    /**
     * Modify a patch file for adding the admin folder name.
     *
     * @return bool Success of the operation.
     */
    final private function preparePatchForShop()
    {
        $filePath = $this->patchFolder.DIRECTORY_SEPARATOR.'diff.patch';

        $content = Tools::file_get_contents($filePath);
        if (!$content) {
            return false;
        }

        return file_put_contents(
            $filePath,
            preg_replace('/^([\\*-]{3}\\s[\\w]*\\/)admin(.*)/m', '${1}'.array_pop(explode(DIRECTORY_SEPARATOR, _PS_ADMIN_DIR_)).'${2}', $content)
        );
    }

    final private function executePatch($guid)
    {
        $filePath = $this->patchFolder.DIRECTORY_SEPARATOR.'diff.patch';
        $prefix = _DB_PREFIX_;
        $pGuid = pSQL($guid);

        Db::getInstance()->execute("
            UPDATE
                `{$prefix}hotfix_patche`
            SET
                `status` = 1
            WHERE
                `guid` = '$pGuid';
        ");

        $result = array();
        $return = 1;
        exec('patch -p1 -d '.realpath(dirname(_PS_ADMIN_DIR_)).' < '.realpath($filePath), $result, $return);
        Configuration::updateValue('SECURITYPATCH_EXEC_RESULT', $return);

        return true;
    }
}
