<?php
/**
 * Push Notifications Register plugin for Craft CMS 3.x
 *
 * A plugin created to store web push notification urls for craft users
 *
 * @link      https://www.page.works/
 * @copyright Copyright (c) 2020 Pageworks
 */

namespace pageworks\pushnotificationsregister\services;

use pageworks\pushnotificationsregister\PushNotificationsRegister;
use craft\elements\db\ElementQuery;

use Craft;
use craft\base\Component;

/**
 * @author    Pageworks
 * @package   PushNotificationsRegister
 * @since     1.0.0
 */
class PushNotificationService extends Component
{
    // Public Methods
    // =========================================================================

    /*
     * @return mixed
     */
    public function exampleService()
    {
        $result = 'something';

        return $result;
    }

    public function getFieldFullName($fieldName)
    {
      $ret = "";
      $fields = Craft::$app->getFields()->getAllFields();
      foreach ($fields as $field)
      {
        if ($fieldName === $field->handle) {
          $ret = ($field->columnPrefix ?: 'field_') . $field->handle;
          break ;
        }
      }
      return $ret;
    }

    public function getPushNotifications($userConditions)
    {
      $query = new Craft\db\Query();
      $result = array();
      if (count($userConditions === 0) {
        $result = $query->select(["endpoint", "key", "token", "userId", "uid", "id"])
          ->from("pushNotifications")
          ->all();
      } else {
        $fields = array();
        foreach ($userConditions as $conditionKey => $conditionValue) {
          $fieldName = $this->getFieldFullName($conditionKey);
          if ($fieldName !== "") {
            $fields[$fieldName] = $conditionValue;
          }
        }
        $result = $query->select(["pushNotifications.endpoint", "pushNotifications.key", "pushNotifications.token", "pushNotifications.userId", "pushNotifications.uid", "pushNotifications.id"])
          ->from("pushNotifications")
          ->join("INNER JOIN", "content", "content.elementId = pushNotifications.userId")
          ->where($fields)
          ->all();
      }
      return $result;
    }

    public function insertPushNotificationData($pushNotificationData)
    {
      $ret = array(
        "success" => false,
      );
      $query = $this->insert("pushNotifications", [
          "endpoint" => $pushNotificationData->endpoint,
          "key" => $pushNotificationData->key,
          "token" => $pushNotificationData->token,
          "userId" => $pushNotificationData->userId,
          "dateCreated" => $date->format("Y-m-d h:m:s"),
          "dateUpdated" => $date->format("Y-m-d h:m:s"),
        ])
        ->execute();
        if ($query === 1) {
          $ret["success"] = true;
        }
        return $ret;
    }
}
