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

    /**
     * Module's constructor.
     */
    public function __construct()
    {
        // Module's base configuration
        $this->name = 'hotfix';
        $this->author = 'PrestaShop';
        $this->version = '0.1';
        $this->bootstrap = true;

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
    }

    /**
     * Module installation.
     *
     * @return bool Success of the operation.
     */
    public function install()
    {
        HotfixClassesLoader::loadClasses(array(
            'Settings',
            'Installation',
            'Patches'
        ));
        $installation = new HotfixInstallation();

        $success = parent::install();
        $success = $success && $installation->installTables();
        $success = $success &&  $installation->createFolder($this->settings->get('paths/backup'));
        $success = $success &&  $installation->createFolder($this->settings->get('paths/patches'));

        $settings = new HotfixSettings(
            include(dirname(__FILE__).DIRECTORY_SEPARATOR.'settings'.DIRECTORY_SEPARATOR.'settings.php')
        );

        $patches = new HotfixPatches($settings);
        $patches->refreshPatchesList();

        while ($patches->getTotalPatchesToDo() > 0) {
            $currentPatch = $patches->getFirstPatchToDo();
            $success = $success && $patches->installPatch($currentPatch);
        }

        if ($success) {
            if (_PS_VERSION_ == '1.4.11.0') {
                Tools::redirectAdmin('index.php?tab=AdminModules&configure='.$this->name.'&token='.Tools::getValue('token'));
            } else {
                Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminModules').'&configure='.$this->name);
            }
        }

        return $success;
    }

    /**
     * Return the configuration result of this module.
     *
     * @return string Content to show.
     */
    public function getContent()
    {
        $this->context->smarty->assign(array(
            'isLinux' => strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN',
        ));

        $templateName = 'configure.tpl';
        switch (_PS_VERSION_) {
            case '1.5.6.2':
                $templateName = 'configure_1562.tpl';
                break;
            case '1.4.11.0':
                $templateName = 'configure_14110.tpl';
                break;
        }

        return $this->context->smarty->fetch($this->local_path.'views/templates/admin/'.$templateName);
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
            && $installation->removeFolder($this->settings->get('paths/patches'))
            && parent::uninstall();
    }
}
