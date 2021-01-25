<?php
/**
 * Push Notifications Register plugin for Craft CMS 3.x
 *
 * A plugin created to store web push notification urls for craft users
 *
 * @link      https://www.page.works/
 * @copyright Copyright (c) 2020 Pageworks
 */

namespace pageworks\pushnotificationsregister;

use pageworks\pushnotificationsregister\services\PushNotificationService as PushNotificationServiceService;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;

use yii\base\Event;

/**
 * Class PushNotificationsRegister
 *
 * @author    Pageworks
 * @package   PushNotificationsRegister
 * @since     1.0.0
 *
 * @property  PushNotificationServiceService $pushNotificationService
 */
class PushNotificationsRegister extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var PushNotificationsRegister
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public $hasCpSettings = false;

    /**
     * @var bool
     */
    public $hasCpSection = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_SITE_URL_RULES, function (RegisterUrlRulesEvent $event) {
          $event->rules["/pushNotifications/register-push-notification"] = "pageworks-module/default/register-push-notification-data";
        });

        Craft::info(
            Craft::t(
                'push-notifications-register',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

}
