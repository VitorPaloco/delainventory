<?php
namespace GlpiPlugin\Delainventory;

use DBConnection;
use CommonDBTM;
use CommonGLPI;
use Session;

class Log extends CommonDBTM
{
    public static function getMenuName()
    {
        return "DelaInventory";
    }

    public static function install()
    {
        global $DB;

        $table = self::getTable();

        if (!$DB->tableExists($table)) {

            $default_charset   = DBConnection::getDefaultCharset();
            $default_collation = DBConnection::getDefaultCollation();

            $query = "CREATE TABLE `$table` (
                `id` INT UNSIGNED AUTO_INCREMENT,
                `itemtype` VARCHAR(100) NOT NULL,
                `item_id` INT UNSIGNED NOT NULL,
                `date_creation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `comment` VARCHAR(255) NOT NULL,
                `users_id` INT UNSIGNED NOT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_item` (`itemtype`, `item_id`)
            ) ENGINE=InnoDB
            DEFAULT CHARSET={$default_charset}
            COLLATE={$default_collation}";

            $DB->doQuery($query);
        }
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (Config::isEnabled($item->getType())) {
            return self::getMenuName();
        }

        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        require GLPI_ROOT . '/plugins/delainventory/front/log.php';
    }

    public static function getLogs($itemtype, $item_id)
    {
        global $DB;

        return $DB->request([
            'FROM' => self::getTable(),
            'WHERE' => [
                'itemtype' => $itemtype,
                'item_id'  => $item_id
            ],
            'ORDER' => ['date_creation DESC']
        ]);
    }

    public static function addNewLog($itemtype, $item_id, $comment)
    {
        global $DB;

        return $DB->insert(
            self::getTable(),
            [
                'itemtype' => $itemtype,
                'item_id'  => $item_id,
                'comment' => $comment,
                'users_id' => Session::getLoginUserID()
            ]
        );
    }
}