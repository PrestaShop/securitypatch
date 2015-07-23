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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * HotFix module main class.
 */
class HotFix extends Module
{
    /** @var Array Module's settings. */
    private $settings = array();

    /** @var int Hotfixes count. */
    private $totalPatches;

    /**
     * Module's constructor.
     */
    public function __construct()
    {
        // Module's base configuration
        $this->name = 'hotfix';
        $this->author = 'PrestaShop';
        $this->version = '0.1';

        parent::__construct();

        // Module's presentation
        $this->displayName = $this->l('HotFix');
        $this->description = $this->l('Security & important updates patcher.');

        // Require the Hotfix classes loader and the main classes
        require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'HotfixClassesLoader.php';
        HotfixClassesLoader::loadClasses(array(
            'Settings',
            'Patches'
        ));

        // Load the settings.
        $this->settings = new HotfixSettings(
            include(dirname(__FILE__).DIRECTORY_SEPARATOR.'settings'.DIRECTORY_SEPARATOR.'settings.php')
        );

        // If active, init the total of patches to install.
        if ($this->isActive()) {
            $patches = new HotfixPatches($this->settings);
            $patches->refreshPatchesList();
            $this->totalPatches = $patches->getTotalPatchesToDo();
        }
    }

    /**
     * Module installation.
     *
     * @return bool Success of the operation.
     */
    public function install()
    {
        HotfixClassesLoader::loadClass('Installation');
        $installation = new HotfixInstallation();

        return parent::install()
            && $installation->installTables()
            && $installation->createFolder($this->settings->get('paths/backup'))
            && $installation->createFolder($this->settings->get('paths/patches'))
            && $installation->installTab('Hotfix', 'AdminHotfix', 'AdminAdmin', $this)
            && $installation->registerHooks($this, array(
                'displayBackOfficeFooter',
                'displayBackOfficeHeader',
            ));
    }

    /**
     * Module uninstallation.
     *
     * @return null
     */
    public function uninstall()
    {
        HotfixClassesLoader::loadClass('Installation');
        $installation = new HotfixInstallation();

        return $installation->removeTables()
            && $installation->removeFolder($this->settings->get('paths/backup'))
            && $installation->removeFolder($this->settings->get('paths/patches'))
            && $installation->uninstallTab('AdminHotfix')
            && parent::uninstall();
    }

    /**
     * Add the needed files for this module to the header.
     *
     * @return null
     */
    public function hookDisplayBackOfficeHeader()
    {
        if (!$this->isActive()) {
            return;
        }

        if ($this->totalPatches > 0) {
            $this->context->controller->addCSS($this->_path.'views/css/hotfix-header.css', 'all');
        }
    }

    /**
     * Method called by the hook "displayBackOfficeFooter".
     *
     * Add the template showing the need to hotfix a bug.
     *
     * @return null|Smarty_Internal_Template
     */
    public function hookDisplayBackOfficeFooter()
    {
        if (!$this->isActive()) {
            return null;
        }

        if ($this->totalPatches > 0) {
            $this->context->smarty->assign(array(
                'count' => $this->totalPatches,
                'link' => $this->context->link->getAdminLink('AdminHotfix'),
            ));

            return $this->display(__FILE__, 'header.tpl');
        }

        return null;
    }

    /**
     * Return the active status of the module.
     *
     * @return bool Active status.
     */
    private final function isActive()
    {
        return Module::isEnabled($this->name)
            && $this->active
            && Tools::getValue('uninstall') != $this->name;
    }
}
