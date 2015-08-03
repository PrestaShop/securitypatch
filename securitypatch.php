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
class Securitypatch extends Module
{
    /** @var Array Module's settings. */
    private $settings = array();

    /**
     * Module's constructor.
     */
    public function __construct()
    {
        // Module's base configuration
        $this->name = 'securitypatch';
        $this->author = 'PrestaShop';
        $this->version = '1.0.2';
        $this->bootstrap = true;

        parent::__construct();

        // Module's presentation
        $this->displayName = $this->l('Security Patch');
        $this->description = $this->l('This module improves your shop\'s safety by applying the latest security patches from PrestaShop.');

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
        $success = $success && $installation->createFolder($this->settings->get('paths/backup'));
        $success = $success && $installation->createFolder($this->settings->get('paths/patches'));
        $success = $success && Configuration::updateValue('SECURITYPATCH_EXEC_RESULT', 1);

        if ($success) {

            if ($this->checkExec()) {
                $settings = new HotfixSettings(
                    include(dirname(__FILE__).DIRECTORY_SEPARATOR.'settings'.DIRECTORY_SEPARATOR.'settings.php')
                );

                $patches = new HotfixPatches($settings);
                $patches->refreshPatchesList();

                while ($patches->getTotalPatchesToDo() > 0) {
                    $currentPatch = $patches->getFirstPatchToDo();
                    $success = $success && $patches->installPatch($currentPatch);
                }
            }

            if ($success) {
                if (_PS_VERSION_ == '1.4.11.0') {
                    Tools::redirectAdmin('index.php?tab=AdminModules&configure='.$this->name.'&token='.Tools::getValue('token'));
                } else {
                    Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminModules').'&configure='.$this->name);
                }
            }
        }

        return $success;
    }

    /**
     * Check if the exec function is available.
     *
     * @return bool Availability of the function.
     */
    private function checkExec()
    {
        if ((bool)ini_get('safe_mode') === true) {
            return false;
        }

        $disabledFunctions = explode(',', ini_get('disable_functions'));
        foreach ($disabledFunctions as $function) {
            if (trim($function) == 'exec') {
                return false;
            }
        }

        $result = array();
        $return = 1;
        exec('hash patch', $result, $return);
        if ($return == 1) {
            return false;
        }

        return true;
    }

    /**
     * Return the configuration result of this module.
     *
     * @return string Content to show.
     */
    public function getContent()
    {
        $isLinux = strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN';
        $execAvailable = $this->checkExec();
        $execSuccess = Configuration::get('SECURITYPATCH_EXEC_RESULT') == 0;
        $language = 'en';

        if (_PS_VERSION_ == '1.4.11.0') {
            global $cookie;
            $output = '<h2>'.$this->l('Security Patch').'</h2><fieldset>';
            if ($isLinux) {
                $language = Language::getIsoById($cookie->id_lang);
                if (!$execAvailable || !$execSuccess) {
                    $output .= '<div class="error">
                        <img src="../img/admin/error2.png"> '.$this->l('The security update could not be applied to your shop. The module cannot execute the patch on your server configuration.').'<br>'
                        .'<span style="font-weight: normal">'.$this->l('Please check the details below for each update to see how you can implement the patch on your shop.').'</span>'
                    .'</div>';
                }
                else {
                    $output .= '<div class="conf">
                        <img src="../img/admin/ok2.png" alt=""> '.$this->l('Module successfully installed. Your shop benefits from the latest security update!')
                    .'</div>
                    <p>
                        '.$this->l('The module has applied the following patches to your store:')
                    .'</p>';
                }
            } else {
                $output .= '<div class="error">
                    <img src="../img/admin/error2.png"> '.$this->l('Your shop is hosted on a Windows server. Unfortunately, the module is not compatible with this configuration yet.').'<br>'
                    .'<span style="font-weight: normal">'.$this->l('Please check the details below for each update to see how you can implement the patch on your shop.').'</span>'
                .'</div>';
            }

            $link = $this->settings->get('links/patches/password/'.$language);
            if ($link == '' || $link == null) {
                $link = $this->settings->get('links/patches/password/en');
            }

            $output .= '<p>
                <b>'.$this->l('Password generation update').'</b> - '.$this->l('July 2015').'<br>'
                .$this->l('Improved algorithm for password generation.').' <a href="'.$link.'" style="font-weight:bold;">'.$this->l('Read this article').'</a> '.$this->l('for more details.')
            .'</p></fieldset>';

            return $output;
        }

        $language = Language::getIsoById($this->context->cookie->id_lang);
        $link = $this->settings->get('links/patches/password/'.$language);
        if ($link == '' || $link == null) {
            $link = $this->settings->get('links/patches/password/en');
        }

        $this->context->smarty->assign(array(
            'isLinux' => $isLinux,
            'execAvailable' => $execAvailable,
            'link' => $link,
            'execSuccess' => $execSuccess,
        ));

        $templateName = 'configure.tpl';
        switch (_PS_VERSION_) {
            case '1.5.6.2':
                $templateName = 'configure_1562.tpl';
                break;
        }

        return $this->display(__FILE__, 'views/templates/admin/'.$templateName);
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
            && Configuration::deleteByName('SECURITYPATCH_EXEC_RESULT')
            && parent::uninstall();
    }
}
