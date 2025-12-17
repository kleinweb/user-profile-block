<?php
/**
 * Plugin Name: User Profile Block
 * Plugin URI: https://github.com/kleinweb/user-profile-block
 * Description: A WordPress plugin from Klein College of Media and Communication.
 * Version: 1.0.0
 * Requires at least: 6.4
 * Requires PHP: 8.3
 * Author: Klein College of Media and Communication
 * Author URI: https://github.com/kleinweb
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: user-profile-block
 * Domain Path: /languages
 */

declare(strict_types=1);

namespace Kleinweb\UserProfile;

use Kleinweb\UserProfile\Container\ServiceContainer;

if (!defined('ABSPATH')) {
    exit;
}

const VERSION = '1.0.0';
const PLUGIN_FILE = __FILE__;
const PLUGIN_DIR = __DIR__;

require_once __DIR__ . '/vendor/autoload.php';

function plugin(): ServiceContainer
{
    static $container = null;

    if ($container === null) {
        $container = new ServiceContainer();
    }

    return $container;
}

function activate(): void
{
    plugin()->activate();
}

function deactivate(): void
{
    plugin()->deactivate();
}

register_activation_hook(__FILE__, __NAMESPACE__ . '\\activate');
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\\deactivate');

add_action('plugins_loaded', static function (): void {
    plugin()->boot();
});
