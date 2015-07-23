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

    /**
     * Constructor.
     *
     * @param HotfixSettings $settings Settings object.
     */
    public function __construct($settings)
    {
        $this->settings = $settings;
    }

    /**
     * Refresh the patches list.
     */
    public function refreshPatchesList()
    {
        $prefix = _DB_PREFIX_;

        $patchesListLocation = str_replace('{$base_version}', _PS_VERSION_, $this->settings->get('list/location'));
        $patchesList = json_decode(Tools::file_get_contents($patchesListLocation), true);

        $currentPatchesList = $this->getAllPatchesList();

        foreach ($patchesList as $guid => $currentPatch) {
            $alreadyDefined = false;
            if ($currentPatchesList !== null) {
                foreach ($currentPatchesList as $currentDefinedPatch) {
                    if ($currentDefinedPatch['guid'] == $guid) {
                        $alreadyDefined = true;
                    }
                }
            }
            if (!$alreadyDefined) {
                $guid = pSQL($guid);
                $date = pSQL($currentPatch['date']);
                Db::getInstance()->query("
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
}
