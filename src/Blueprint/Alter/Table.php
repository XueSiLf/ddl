<?php

namespace EasySwoole\DDL\Blueprint\Alter;

use EasySwoole\DDL\Blueprint\AbstractInterface\ColumnInterface;
use EasySwoole\DDL\Enum\Alter;
use EasySwoole\DDL\Enum\Character;
use EasySwoole\DDL\Enum\Engine as Engines;
use InvalidArgumentException;

/**
 * 修改表结构描述
 * 支持:
 * table: modify
 * column: add,modify/change,drop
 * index: add,modify(drop&add),drop
 * foreign: add,modify(drop&add),drop
 *
 * Class Table
 * @package EasySwoole\DDL\Blueprint\Alter
 */
class Table
{
    // 基础属性
    protected $table;
    protected $renameTable;
    protected $comment;
    protected $engine;// = Engines::INNODB
    protected $charset;// = Character::UTF8_GENERAL_CI
    // 额外选项
    protected $autoIncrement;     // 自增起始值

    // 结构定义
    protected $columns = [];  // 表的行定义
    protected $indexes = [];  // 表的索引
    protected $foreignKeys = [];  // 表的外键

    /**
     * Table constructor.
     * @param $tableName
     */
    function __construct($tableName)
    {
        $this->settableName($tableName);
    }


    // 字段修改

    /**
     * 新增字段
     * @return ColumnAdd
     */
    function addColumn(): ColumnAdd
    {
        $this->columns[Alter::ADD][] = $columnAdd = new ColumnAdd();
        return $columnAdd;
    }

    /**
     * 修改字段属性
     * @return ColumnModify
     */
    function modifyColumn(): ColumnModify
    {
        $this->columns[Alter::MODIFY][] = $columnModify = new ColumnModify();
        return $columnModify;
    }

    /**
     * 变更字段名
     * @param string $column
     * @return ColumnChange
     */
    function changeColumn(string $column): ColumnChange
    {
        $this->columns[Alter::CHANGE][] = $columnChange = new ColumnChange($column);
        return $columnChange;
    }

    /**
     * 删除字段
     * @param string $column
     * @return ColumnDrop
     */
    function dropColumn(string $column): ColumnDrop
    {
        $this->columns[Alter::DROP][] = $columnDrop = new ColumnDrop($column);
        return $columnDrop;
    }

    // 索引修改

    /**
     * 新增索引
     * @return IndexAdd
     */
    public function addIndex()
    {
        $this->indexes[Alter::ADD][] = $indexAdd = new IndexAdd();
        return $indexAdd;
    }

    /**
     * 修改索引(drop&add)
     * @param string $indexName
     * @return IndexAdd
     */
    public function modifyIndex(string $indexName)
    {
        $this->indexes[Alter::DROP][] = new IndexDrop($indexName);
        $this->indexes[Alter::ADD][]  = $indexModify = new IndexAdd();
        return $indexModify;
    }

    /**
     * 删除索引
     * @param string $indexName
     * @return IndexDrop
     */
    public function dropIndex(string $indexName)
    {
        $this->indexes[Alter::DROP][] = $indexDrop = new IndexDrop($indexName);
        return $indexDrop;
    }

    // 外键修改

    /**
     * 新增索引
     * @param string|null $foreignName
     * @param string $localColumn
     * @param string $relatedTableName
     * @param string $foreignColumn
     * @return ForeignAdd
     */
    public function addForeign(?string $foreignName, string $localColumn, string $relatedTableName, string $foreignColumn)
    {
        $this->foreignKeys[Alter::ADD][] = $foreignAdd = new ForeignAdd($foreignName, $localColumn, $relatedTableName, $foreignColumn);
        return $foreignAdd;
    }

    /**
     * 修改索引(drop&add)
     * @param string $indexName
     * @return ForeignModify
     */
    public function modifyForeign(string $indexName)
    {
        $this->foreignKeys[Alter::DROP][] = new ForeignDrop($indexName);
        $this->foreignKeys[Alter::ADD][]  = $foreignAdd = new ForeignModify();
        return $foreignAdd;
    }

    /**
     * 删除索引
     * @param string $indexName
     * @return ForeignDrop
     */
    public function dropForeign(string $indexName)
    {
        $this->foreignKeys[Alter::DROP][] = $foreignDrop = new ForeignDrop($indexName);
        return $foreignDrop;
    }

    // 以下为表本身属性的设置方法

    /**
     * 设置表名称
     * @param string $name
     * @return Table
     */
    function setTableName(string $name): Table
    {
        $name = trim($name);
        if (empty($name)) {
            throw new InvalidArgumentException('The table name cannot be empty');
        }
        $this->table = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTableName()
    {
        return $this->table;
    }

    /**
     * 修改表明
     * @param string|null $name
     * @return $this
     */
    function setRenameTable(?string $name = null): Table
    {
        $this->renameTable = trim($name) ?: null;
        return $this;
    }

    /**
     * @return mixed
     */
    function getRenameTable()
    {
        return $this->renameTable;
    }

    /**
     * 设置储存引擎
     * @param string $engine
     * @return Table
     */
    function setTableEngine(string $engine): Table
    {
        $engine = trim($engine);
        if (!Engines::isValidValue($engine)) {
            throw new InvalidArgumentException('The engine ' . $engine . ' is invalid');
        }
        $this->engine = $engine;
        return $this;
    }

    /**
     * @return mixed
     */
    function getTableEngine()
    {
        return $this->engine;
    }

    /**
     * 设置表注释
     * @param string $comment
     * @return Table
     */
    function setTableComment(string $comment): Table
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return mixed
     */
    function getTableComment()
    {
        return $this->comment;
    }

    /**
     * 设置表字符集
     * @param string $charset
     * @return Table
     */
    function setTableCharset(string $charset): Table
    {
        $charset = trim($charset);
        if (!Character::isValidValue($charset)) {
            throw new InvalidArgumentException('The character ' . $charset . ' is invalid');
        }
        $this->charset = $charset;
        return $this;
    }

    /**
     * @return mixed
     */
    function getTableCharset()
    {
        return $this->charset;
    }

    /**
     * 设置起始自增值
     * @param int $startIncrement
     * @return Table
     */
    function setTableAutoIncrement(int $startIncrement)
    {
        $this->autoIncrement = $startIncrement;
        return $this;
    }

    /**
     * @return mixed
     */
    function getTableAutoIncrement()
    {
        return $this->autoIncrement;
    }

    // 生成表结构 带有下划线的方法请不要自行调用
    function __createDDL()
    {
        // 表名称定义
        $tableName = "`{$this->getTableName()}`"; // 安全起见引号包裹

        $renameTableSql = null;
        if ($this->getRenameTable()) {
            $renameTableSql = "ALTER TABLE {$tableName} RENAME TO `{$this->getRenameTable()}`";
            $tableName      = "`{$this->getRenameTable()}`";
        }

        // 表格字段定义
        $tmpColumnDefinitions = $columnDefinitions = [];
        foreach ($this->columns as $operate => $columnObjs) {
            /** @var ColumnInterface $columnObj */
            foreach ($columnObjs as $columnObj) {
                $tmpColumnDefinitions[$operate][$columnObj->getColumnName()] = "ALTER TABLE {$tableName} " . (string)$columnObj;
            }
        }
        //避免一些先新增，后删除的操作报错
        $columnDefinitions = array_merge(
            isset($tmpColumnDefinitions[Alter::DROP]) ? array_values((array)$tmpColumnDefinitions[Alter::DROP]) : [],
            isset($tmpColumnDefinitions[Alter::CHANGE]) ? array_values((array)$tmpColumnDefinitions[Alter::CHANGE]) : [],
            isset($tmpColumnDefinitions[Alter::MODIFY]) ? array_values((array)$tmpColumnDefinitions[Alter::MODIFY]) : [],
            isset($tmpColumnDefinitions[Alter::ADD]) ? array_values((array)$tmpColumnDefinitions[Alter::ADD]) : []
        );

        // 表格索引定义
        $tmpIndexDefinitions = $indexDefinitions = [];
        foreach ($this->indexes as $operate => $indexObjs) {
            foreach ($indexObjs as $indexObj) {
                $tmpIndexDefinitions[$operate][] = "ALTER TABLE {$tableName} " . (string)$indexObj;
            }
        }
        //避免一些先新增，后删除的操作报错
        $indexDefinitions = array_merge(
            isset($tmpIndexDefinitions[Alter::DROP]) ? array_values((array)$tmpIndexDefinitions[Alter::DROP]) : [],
            isset($tmpIndexDefinitions[Alter::ADD]) ? array_values((array)$tmpIndexDefinitions[Alter::ADD]) : []
        );

        // 表格外键定义
        $tmpForeignDefinitions = $foreignDefinitions = [];
        foreach ($this->foreignKeys as $operate => $foreignObjs) {
            foreach ($foreignObjs as $foreignObj) {
                $tmpForeignDefinitions[$operate][] = "ALTER TABLE {$tableName} " . (string)$foreignObj;
            }
        }
        //避免一些先新增，后删除的操作报错
        $foreignDefinitions = array_merge(
            isset($tmpForeignDefinitions[Alter::DROP]) ? array_values((array)$tmpForeignDefinitions[Alter::DROP]) : [],
            isset($tmpForeignDefinitions[Alter::ADD]) ? array_values((array)$tmpForeignDefinitions[Alter::ADD]) : []
        );

        // 表格属性定义
        $tableOptions = array_filter(
            [
                $this->getTableEngine() ? 'ENGINE = ' . strtoupper($this->getTableEngine()) : null,
                $this->getTableAutoIncrement() ? "AUTO_INCREMENT = " . intval($this->getTableAutoIncrement()) : null,
                $this->getTableCharset() ? "DEFAULT COLLATE = '" . $this->getTableCharset() . "'" : null,
                $this->getTableComment() ? "COMMENT = '" . addslashes($this->getTableComment()) . "'" : null
            ]
        );
        // 构建表格DDL
        return $AlterDDL = implode(";" . PHP_EOL,
                array_filter(
                    [
                        $renameTableSql ? $renameTableSql : null,
                        $tableOptions ? "ALTER TABLE {$tableName} " . implode(' ', $tableOptions) : null,
                        implode(";" . PHP_EOL,
                            array_merge(
                                $columnDefinitions,
                                $indexDefinitions,
                                $foreignDefinitions
                            )
                        ),
                    ]
                )
            ) . ';';
    }

    /**
     * 转化为字符串
     * @return string
     */
    function __toString()
    {
        return $this->__createDDL();
    }
}