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
              "id" => $this->integer()->notNull(),
              "endpoint" => $this->string()->notNull(),
              "key" => $this->string()->notNull(),
              "token" => $this->string()->notNull(),
              "userId" => $this->integer()->notNull(),
              "dateCreated" => $this->dateTime(),
              "dateUpdated" => $this->dateTime(),
              "uid" => $this->string(),
          ]);
        }
      }
    }
}
