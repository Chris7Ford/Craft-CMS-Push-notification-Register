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

use Craft;
use craft\web\Controller;
use pageworks\pushnotificationsregister\services\PushNotificationService as PushNotificationService;

/**
 * @author    Pageworks
 * @package   PushNotificationsRegister
 * @since     1.0.0
 */
class PushNotificationsController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var array
     */
    protected $allowAnonymous = [];

    // Public Methods
    // =========================================================================

    /**
     * Controller action to clear image transforms
     *
     * @throws \yii\base\ErrorException
     */
    public function actionRegisterPushNotificationData()
    {
      $this->requireAcceptsJson();
      $this->requirePostRequest();
      $request = Craft::$app->getRequest();
      $params = $request->getBodyParams();
      $ret = array(
        "success" => false,
      );
      $fields = array(
        "endpoint" => $params["endpoint"],
        "key" => $params["key"],
        "token" => $params["token"],
        "userId" => $params["userId"],
      )
      $currentUser = Craft::$app->getUser()->getIdentity();
      if ($params["userId"] === $currentUser->id) {
        $service = new PushNotificationServiceService();
        $insertResult = $service->insertPushNotificationData($params);
        if ($insertResult["success"] === true) {
          $ret["success"] = true;
        } else {
          $ret["message"] = $insertResult["message"];
        }
      } else {
        $ret["message"] = "Current user id and user id param do not match";
      }
    }
}
