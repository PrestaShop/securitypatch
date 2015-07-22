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
 * Settings class.
 */
class HotfixSettings
{
    /** @var array Loaded settings. */
    private $loadedSettings = array();

    /**
     * Constructor.
     *
     * @param array $settings Settings to load in memory.
     */
    public function __construct(array $settings)
    {
        $this->loadedSettings = $settings;
    }

    /**
     * Return the value at the wanted path.
     *
     * @param string $path Value path.
     * @return mixed|null
     */
    public function get($path)
    {
        $explodedPath = explode('/', $path);

        $cursor = &$this->loadedSettings;
        foreach($explodedPath as $currentKey) {
            if (!isset($cursor[$currentKey]))
                return null;

            $cursor = &$cursor[$currentKey];
        }

        return $cursor;
    }
}
