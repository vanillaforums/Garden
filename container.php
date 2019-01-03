<?php

use Garden\Container\Container;
use Garden\Container\Reference;
use Vanilla\Addon;
use Vanilla\InjectableInterface;
use Vanilla\Contracts;
use Vanilla\Utility\ContainerUtils;

if (!defined('APPLICATION')) exit();
/**
 * Bootstrap.
 *
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0-only
 * @package Core
 * @since 2.0
 */

// Guard against broken cache files.
if (!class_exists('Gdn')) {
    // Throwing an exception here would result in a white screen for the user.
    // This error usually indicates the .ini files in /cache are out of date and should be deleted.
    exit("Class Gdn not found.");
}

// Set up the dependency injection container.
$dic = new Container();
Gdn::setContainer($dic);

$dic->setInstance('Garden\Container\Container', $dic)
    ->rule('Interop\Container\ContainerInterface')
    ->setAliasOf('Garden\Container\Container')

    ->rule(InjectableInterface::class)
    ->addCall('setDependencies')

    ->rule(DateTimeInterface::class)
    ->setAliasOf(DateTimeImmutable::class)
    ->setConstructorArgs([null, null])

    // Cache
    ->rule('Gdn_Cache')
    ->setShared(true)
    ->setFactory(['Gdn_Cache', 'initialize'])
    ->addAlias('Cache')

    // Configuration
    ->rule('Gdn_Configuration')
    ->setShared(true)
    ->addAlias('Config')
    ->addAlias(Contracts\ConfigurationInterface::class)

    // AddonManager
    ->rule(Vanilla\AddonManager::class)
    ->setShared(true)
    ->setConstructorArgs([
        [
            Addon::TYPE_ADDON => ['/applications', '/plugins'],
            Addon::TYPE_THEME => '/themes',
            Addon::TYPE_LOCALE => '/locales'
        ],
        PATH_CACHE
    ])
    ->addAlias('AddonManager')
    ->addAlias(Contracts\AddonProviderInterface::class)
    ->addCall('registerAutoloader')

    // ApplicationManager
    ->rule('Gdn_ApplicationManager')
    ->setShared(true)
    ->addAlias('ApplicationManager')

    ->rule(Garden\Web\Cookie::class)
    ->setShared(true)
    ->addAlias('Cookie')

    // PluginManager
    ->rule('Gdn_PluginManager')
    ->setShared(true)
    ->addAlias('PluginManager')

    ->rule(SsoUtils::class)
    ->setShared(true)


    // Logger
    ->rule(\Vanilla\Logger::class)
    ->setShared(true)
    ->addAlias(\Psr\Log\LoggerInterface::class)

    ->rule(\Psr\Log\LoggerAwareInterface::class)
    ->addCall('setLogger')

    // EventManager
    ->rule(\Garden\EventManager::class)
    ->setShared(true)

    // Locale
    ->rule('Gdn_Locale')
    ->setShared(true)
    ->setConstructorArgs([new Reference(['Gdn_Configuration', 'Garden.Locale'])])
    ->addAlias('Locale')

    // Request
    ->rule('Gdn_Request')
    ->setShared(true)
    ->addCall('fromEnvironment')
    ->addAlias('Request')
    ->addAlias(\Garden\Web\RequestInterface::class)

    // Database.
    ->rule('Gdn_Database')
    ->setShared(true)
    ->setConstructorArgs([new Reference(['Gdn_Configuration', 'Database'])])
    ->addAlias('Database')

    ->rule('Gdn_SQLDriver')
    ->setClass('Gdn_MySQLDriver')
    ->setShared(true)
    ->addAlias('Gdn_MySQLDriver')
    ->addAlias('MySQLDriver')
    ->addAlias(Gdn::AliasSqlDriver)

    ->rule('Identity')
    ->setClass('Gdn_CookieIdentity')
    ->setShared(true)

    ->rule('Gdn_Session')
    ->setShared(true)
    ->addAlias('Session')

    ->rule(Gdn::AliasAuthenticator)
    ->setClass('Gdn_Auth')
    ->setShared(true)

    ->rule('Gdn_Router')
    ->addAlias(Gdn::AliasRouter)
    ->setShared(true)

    ->rule('Gdn_Dispatcher')
    ->setShared(true)
    ->addAlias(Gdn::AliasDispatcher)

    ->rule(\Vanilla\Web\Asset\DeploymentCacheBuster::class)
    ->setConstructorArgs([
        'deploymentTime' => ContainerUtils::config('Garden.Deployed')
    ])

    ->rule(\Vanilla\Web\Asset\WebpackAssetProvider::class)
    ->addCall('setHotReloadEnabled', [
        ContainerUtils::config('HotReload.Enabled'),
        ContainerUtils::config('HotReload.IP'),
    ])
    ->addCall('setLocaleKey', [ContainerUtils::currentLocale()])
    ->addCall('setCacheBusterKey', [ContainerUtils::cacheBuster()])

    ->rule(\Vanilla\Web\Asset\LegacyAssetModel::class)
    ->setConstructorArgs([ContainerUtils::cacheBuster()])


    ->rule(\Garden\ClassLocator::class)
    ->setClass(\Vanilla\VanillaClassLocator::class)

    ->rule('Gdn_Model')
    ->setShared(true)


    ->rule(\Vanilla\Models\AuthenticatorModel::class)
    ->setShared(true)
    ->addCall('registerAuthenticatorClass', [\Vanilla\Authenticator\PasswordAuthenticator::class])


;

// Run through the bootstrap with dependencies.
$dic->call(function (
    Container $dic,
    Gdn_Configuration $config,
    \Vanilla\AddonManager $addonManager
) {

    // Load default baseline Garden configurations.
    $config->load(PATH_CONF.'/config-defaults.php');

    // Load installation-specific configuration so that we know what apps are enabled.
    $config->load($config->defaultPath(), 'Configuration', true);

    /**
     * Bootstrap Early
     *
     * A lot of the framework is loaded now, most importantly the core autoloader,
     * default config and the general and error functions. More control is possible
     * here, but some things have already been loaded and are immutable.
     */
    if (file_exists(PATH_CONF.'/bootstrap.early.php')) {
        require_once PATH_CONF.'/bootstrap.early.php';
    }

    $config->caching(true);
    debug($config->get('Debug', false));

    setHandlers();


    /**
     * Extension Managers
     *
     * Now load the Addon, Application, Theme and Plugin managers into the Factory, and
     * process the application-specific configuration defaults.
     */

    // Start the addons, plugins, and applications.
    $addonManager->startAddonsByKey(c('EnabledPlugins'), Addon::TYPE_ADDON);
    $addonManager->startAddonsByKey(c('EnabledApplications'), Addon::TYPE_ADDON);
    $addonManager->startAddonsByKey(array_keys(c('EnabledLocales', [])), Addon::TYPE_LOCALE);


    // Load the configurations for enabled addons.
    foreach ($addonManager->getEnabled() as $addon) {
        /* @var Addon $addon */
        if ($configPath = $addon->getSpecial('config')) {
            $config->load($addon->path($configPath));
        }
    }

    // Re-apply loaded user settings.
    $config->overlayDynamic();

    /**
     * Bootstrap Late
     *
     * All configurations are loaded, as well as the Application, Plugin and Theme
     * managers.
     */
    if (file_exists(PATH_CONF.'/bootstrap.late.php')) {
        require_once PATH_CONF.'/bootstrap.late.php';
    }

    if ($config->get('Debug')) {
        debug(true);
    }

    Gdn_Cache::trace(debug()); // remove later

});

$dic->get('Authenticator')->startAuthenticator();
