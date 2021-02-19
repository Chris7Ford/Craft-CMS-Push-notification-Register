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
use craft\db\mysql\QueryBuilder;
use Minishlink\WebPush\WebPush;

/**
 * @author    Pageworks
 * @package   PushNotificationsRegister
 * @since     1.0.0
 */
class PushNotificationService extends Component
{

    private $pluginTableName;

    function __construct() {
      $this->pluginTableName = getenv("DB_TABLE_PREFIX") . "pushNotifications";
    }
    // Public Methods
    // =========================================================================

    /*
     * @return mixed
     */

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

    public function getPushNotificationsByUserConditions($userConditions = array())
    {
      $query = new Craft\db\Query();
      $result = array();
      if (count($userConditions) === 0) {
        $result = $query->select(["endpoint", "key", "token", "userId", "uid", "id"])
          ->from($this->pluginTableName)
          ->all();
      } else {
        $fields = array();
        foreach ($userConditions as $conditionKey => $conditionValue) {
          $fieldName = $this->getFieldFullName($conditionKey);
          if ($fieldName !== "") {
            $fields["content." . $fieldName] = $conditionValue;
          }
        }
        $result = $query->select([$this->pluginTableName . ".endpoint", $this->pluginTableName . ".key", $this->pluginTableName . ".token", $this->pluginTableName . ".userId", $this->pluginTableName . ".uid", $this->pluginTableName . ".id"])
          ->from($this->pluginTableName)
          ->join("INNER JOIN", "content", "content.elementId = " . $this->pluginTableName . ".userId")
          ->where($fields)
          ->all();
      }
      return $result;
    }

    public function getPushNotificationsByUserId(array $userIds)
    {
      $query = new Craft\db\Query();
      $result = $query->select(["endpoint", "key", "token", "userId", "uid", "id"])
        ->from($this->pluginTableName)
        ->where(array("in", "userId", $userIds))
        ->all();
      return $result;
    }

    public function sendPushNotifications($pushNotifications, string $payload)
    {
      $ret = array(
        "success" => true
      );
      $auth = array(
        "VAPID" => array(
          "subject" => "Push Notification",
          "publicKey" => getenv("PUSH_NOTIFICATION_APPLICATION_SERVER_KEY_PUBLIC"),
          "privateKey" => getenv("PUSH_NOTIFICATION_APPLICATION_SERVER_KEY_PRIVATE"),
        ),
      );
      $webPush = new WebPush($auth);
      $failures = array();
      foreach($pushNotifications as $notification)
      {
        $flush = true;
        $date = (new \DateTime())->format("Y-m-d h:i:s");
        $res = $webPush->sendNotification(
          $notification["endpoint"],
          $payload,
          str_replace(['_', '-'], ['/', '+'], $notification["key"]),
          str_replace(['_', '-'], ['/', '+'], $notification["token"]),
          $flush
        );
        if (gettype($res) === "array") {
          if (isset($res["success"]) && $res["success"] === false) {
            $failures[] = $notification["userId"];
            if (isset($res["message"])) {
              $params = array();
              $includeDateUpdated = true;
              Craft::$app->db->createCommand()->update($this->pluginTableName, ["failureMessage" => $res["message"]], "id = " . $notification["id"], $params, $includeDateUpdated)->execute();
            }
          }
        } else if (gettype($res) === "boolean") {
          if ($res === true) {
            $params = array();
            $includeDateUpdated = true;
            Craft::$app->db->createCommand()->update($this->pluginTableName, ["lastSuccess" => $date, "failureMessage" => null], "id = " . $notification["id"], $params, $includeDateUpdated)->execute();
            Craft::$app->db->createCommand()->update($this->pluginTableName, ["failureMessage" => null], "id = " . $notification["id"], $params, $includeDateUpdated)->execute();
          }
        }
        $params = array();
        $includeDateUpdated = true;
        Craft::$app->db->createCommand()->update($this->pluginTableName, ["lastAttempt" => $date], "id = " . $notification["id"], $params, $includeDateUpdated)->execute();
      }
      if (count($failures) > 0) {
        $ret["success"] = false;
        $ret["message"] = implode(", ", $failures);
      }
      return $ret;
    }

    public function insertPushNotificationData($pushNotificationData)
    {
      $ret = array(
        "success" => false,
      );
      $date = new \DateTime();
      $updateIfExists = true;
      $includeAuditColumns = true;
      $params = array();
      $query = Craft::$app->db->createCommand()
        ->upsert($this->pluginTableName, [
          "endpoint" => $pushNotificationData["endpoint"],
          "key" => $pushNotificationData["key"],
          "token" => $pushNotificationData["token"],
          "userId" => $pushNotificationData["userId"],
          "dateCreated" => $date->format("Y-m-d h:i:s"),
          "dateUpdated" => $date->format("Y-m-d h:i:s"),
          "lastSuccess" => null,
          "lastAttempt" => null,
          "failureMessage" => null,
        ], $updateIfExists, $params, $includeAuditColumns)
        ->execute();
        if ($query === 1) {
          $ret["success"] = true;
        } else {
          $ret["message"] = "Record was not inserted or updated";
        }
        return $ret;
    }
}
