<?php

namespace SymfonyCMS\Engine\Cms\_Core\Base\ORM;

use Doctrine\DBAL\Connection;

class Sql
{
    const OLD_COLUMN_PREFIX = '____';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var
     */
    private $table;

    /**
     * Db constructor.
     * @param Connection $connection
     * @param $table
     */
    public function __construct(Connection $connection, $table)
    {
        $this->pdo = $connection;
        $this->table = $table;
    }

    /**
     * @param $oldColumn
     * @param $tableColumns
     * @return string
     */
    private function getTrashColumnName($oldColumn, $tableColumns)
    {
        $idx = 1;
        $oldColumn = static::OLD_COLUMN_PREFIX . $oldColumn;

        do {
            $oldColumn = $oldColumn . ($idx == 1 ? '' : '_' . $idx);
            $idx = $idx + 1;
        } while (in_array($oldColumn, $tableColumns));
        return $oldColumn;
    }

    /**
     * @param $oldColumn
     * @param $tableColumns
     * @return string
     */
    private function getLastColumn($oldColumn, $tableColumns)
    {
        $result = array_reverse($tableColumns);
        foreach ($result as $itm) {
            if ($itm != $oldColumn && substr($itm, 0, 4) != static::OLD_COLUMN_PREFIX) {
                return $itm;
            }
        }
        return 'id';
    }

    /**
     * @return array
     */
    public function getFields()
    {
        $fields = array();
        $sql = "DESCRIBE `{$this->table}`";
        $stmt = $this->pdo->prepare($sql);
        $stmtResult = $stmt->executeQuery();
        $result = $stmtResult->fetchAllAssociative();
        foreach ($result as $itm) {
            $fields[$itm['Field']] = $itm['Type'] . ($itm['Null'] == 'NO' ? ' NOT NULL' : ' NULL');
        }
        return $fields;
    }

    /**
     * @param $ormFields
     */
    public function sync($ormFields)
    {
        $syncResponse = 3;

        $tableFields = $this->getFields();
        $tableColumns = array_keys($tableFields);
        $ormColumns = array_keys($ormFields);

        $newColumns = array_diff($ormColumns, $tableColumns);
        $oldColumns = array_diff($tableColumns, $ormColumns);

//var_dump($newColumns, $oldColumns);exit;
        foreach ($newColumns as $newColumn) {
            $lastColumnName = $this->getLastColumn($newColumn, $tableColumns);
            $this->addColumn($newColumn, $ormFields[$newColumn], $lastColumnName);
            $tableColumns[] = $newColumn;
            $syncResponse = 2;
        }

        foreach ($oldColumns as $oldColumn) {
            if (substr($oldColumn, 0, 4) == static::OLD_COLUMN_PREFIX) {
                continue;
            }
            $trashColumnName = $this->getTrashColumnName($oldColumn, $tableColumns);
            $lastColumnName = $this->getLastColumn($oldColumn, $tableColumns);
            $this->renameColumn($oldColumn, $trashColumnName, $tableFields[$oldColumn], $lastColumnName);
            $tableColumns[] = $trashColumnName;
            $syncResponse = 2;
        }

        return $syncResponse;
    }

    /**
     * @param $column
     * @param $attrs
     * @param string $lastColumn
     * @return bool
     */
    public function addColumn($column, $attrs, $lastColumn = '')
    {
        try {
            $sql = "ALTER TABLE `{$this->table}` 
                      ADD COLUMN `$column` $attrs" . ($lastColumn ? " AFTER `$lastColumn`" : '');
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute();
        } catch (\Exception $ex) {
            var_dump($ex->getMessage());
            exit;
        }
    }

    /**
     * @param $oldColumn
     * @param $newColumn
     * @param $dataType
     * @param string $lastColumn
     * @return bool
     */
    public function renameColumn($oldColumn, $newColumn, $dataType, $lastColumn = '')
    {
        try {
            $sql = "ALTER TABLE `{$this->table}` 
                      CHANGE `$oldColumn` `$newColumn` $dataType" . ($lastColumn ? " AFTER `$lastColumn`" : '');
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute();
        } catch (\Exception $ex) {
            var_dump($ex->getMessage());
            exit;
        }
    }

    /**
     *
     */
    public function create()
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
                    `id` int(11) NOT NULL AUTO_INCREMENT, 
                    PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
        } catch (\Exception $ex) {
            var_dump($ex->getMessage());
            exit;
        }
    }

    /**
     * @param $newTableName
     */
    public function rename($newTableName)
    {
        try {
            $sql = "ALTER TABLE `$this->table` RENAME TO `$newTableName`;";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $this->table = $newTableName;
        } catch (\Exception $ex) {
            var_dump($ex->getMessage());
            exit;
        }
    }

    /**
     * @return int
     */
    public function exists()
    {
        try {
            $sql = "SELECT 1 FROM `{$this->table}`";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return 1;
        } catch (\Exception $ex) {
//            var_dump($ex->getMessage());
//            exit;
        }
        return 0;
    }

    /**
     * @param $index
     * @param $column
     * @return bool
     */
    public function addIndex($index, $column)
    {
        try {
            $sql = "ALTER TABLE `{$this->table}`
                      ADD INDEX `$index` (`$column` ASC);";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute();
        } catch (\Exception $ex) {
            var_dump($ex->getMessage());
            exit;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function drop()
    {
        $sql = "DROP TABLE IF EXISTS `$this->table`";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute();
    }
}