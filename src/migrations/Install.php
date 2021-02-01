<?php
namespace pageworks\pushnotificationsregister\migrations;

use craft\db\Migration;
use Craft;

class Install extends Migration
{
    public function safeUp()
    {
      if (Craft::$app->projectConfig->get('plugins.pushnotificationsregister', true) === null)
      {
        if (!$this->db->tableExists('{{%pushNotifications}}')) {
          // create the push notifications table
          $this->createTable('{{%pushNotifications}}', [
              "id" => $this->primaryKey(),
              "endpoint" => $this->string()->notNull(),
              "key" => $this->string()->notNull(),
              "token" => $this->string()->notNull(),
              "userId" => $this->integer()->notNull(),
              "dateCreated" => $this->dateTime(),
              "dateUpdated" => $this->dateTime(),
              "lastSuccess" => $this->dateTime(),
              "lastAttempt" => $this->dateTime(),
              "failureMessage" => $this->string(750),
              "uid" => $this->string(),
          ]);
          $this->createIndex("endpoint", '{{%pushNotifications}}', "endpoint", true);
        }
      }
    }
}
