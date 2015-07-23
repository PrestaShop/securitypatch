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
 * Main controller of the Hotfix
 */
class AdminHotfixController extends ModuleAdminController
{
    /** @var HotfixSettings Settings. */
    private $settings;

    /** @var HotfixPatches Patches. */
    private $patches;

    /**
     * Constructor.
     *
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->bootstrap = true;
        $this->display = 'view';
        $this->meta_title = $this->l('Hotfix');

        parent::__construct();

        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }

        HotfixClassesLoader::loadClasses(array(
            'Settings',
            'Patches'
        ));

        $this->settings = new HotfixSettings(
            include(implode(DIRECTORY_SEPARATOR, array(
                dirname(__FILE__),
                '..',
                '..',
                'settings',
                'settings.php',
            )))
        );
        $this->patches = new HotfixPatches($this->settings);
    }

    /**
     * Render the main view.
     *
     * @return string Content to display.
     */
    public function renderView()
    {
        $patchesList = $this->patches->getAllPatchesList();

        $this->context->smarty->assign(array(
            'patches' => array_reverse($patchesList),
            'module_path' => $this->context->link->getAdminLink('AdminHotfix'),
        ));

        return parent::renderView();
    }

    /**
     * Proceed to the installation of the first patch.
     *
     * @return null
     */
    public function ajaxProcessInstallPatch()
    {
        $success = true;
        $patchDetails = $this->patches->getFirstPatchToDo();

        if ($success &= ($patchDetails !== null)) {
            $success &= $this->patches->installPatch($patchDetails);
        }

        if (!$success) {
            die(json_encode(array('success' => false)));
        }

        $result = array();
        $result['hotfix_id'] = 1;
        $result['success'] = true;

        die(json_encode($result));
    }

    /**
     * Set the medias needed by the views.
     *
     * @return null
     */
    public function setMedia()
    {
        $this->addCSS(array(_MODULE_DIR_.$this->module->name.'/views/css/hotfix-panel.css'));

        parent::setMedia();
    }
}
