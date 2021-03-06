<?php
namespace FMUP\Db\Driver\Pdo;

use FMUP\Db\Driver\Pdo;

class SqlSrv extends Pdo
{
    protected $instance = null;

    protected function getOptions()
    {
        $options = parent::getOptions();
        $charset = $this->getCharset();
        $mustSetUtf8Option = (!isset($options[$this->getSqlSrvEncodingUtf8Const()]));
        if ($charset == self::CHARSET_UTF8 && $mustSetUtf8Option) {
            $options[$this->getSqlSrvEncodingUtf8Const()] = true;
        }
        return $options;
    }

    protected function getSqlSrvEncodingUtf8Const()
    {
        return defined('\PDO::SQLSRV_ENCODING_UTF8') ? \PDO::SQLSRV_ENCODING_UTF8 : 'utf8';
    }

    protected function getDsn()
    {
        $dsn = $this->getDsnDriver() . ':Server={' . $this->getHost() . '}';
        $database = $this->getDatabase();
        if ($database) {
            $dsn .= ';Database={' . $database . '};';
        }
        return $dsn;
    }

    protected function getDsnDriver()
    {
        return 'sqlsrv';
    }
}
