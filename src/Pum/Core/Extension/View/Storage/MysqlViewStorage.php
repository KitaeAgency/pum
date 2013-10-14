<?php

namespace Pum\Core\Extension\View\Storage;

use Doctrine\DBAL\Connection;
use Pum\Core\Extension\View\Template;
use Pum\Core\Extension\View\TemplateInterface;

class MysqlViewStorage implements ViewStorageInterface
{
    const VIEW_TABLE_NAME = 'pum_view';

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
    * {@inheritDoc}
    */
    public function getAllPaths($asArrayKey = false)
    {
        $stmt = $this->runSql('SELECT `path` FROM `'. self::VIEW_TABLE_NAME .'`;');

        $paths = array();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if ($asArrayKey === false) {
                $paths[] = $row['path'];
            } else {
                $paths[$row['path']] = true;
            }
        }

        return $paths;
    }

    /**
    * {@inheritDoc}
    */
    public function storeTemplate(TemplateInterface $template, $erase = false)
    {
        $path        = $template->getPath();
        $source      = $template->getSource();
        $is_editable = $template->isEditable();
        $time        = time();

        if ($this->hasTemplate($path)) {
            if ($erase === false) {
                return false;
            }

            $this->runSQL('UPDATE `'.self::VIEW_TABLE_NAME.'` SET `source` = '.$this->connection->quote($source).', `is_editable` = '.$this->connection->quote($is_editable).', `updated` = '.$this->connection->quote($time).' WHERE `path` = '.$this->connection->quote($path).';');

            return true;
        }

        $this->runSQL('INSERT INTO `'.self::VIEW_TABLE_NAME.'` (`path`, `source`, `is_editable`, `updated`) VALUES ('.$this->connection->quote($path).','.$this->connection->quote($source).','.$this->connection->quote($is_editable).','.$this->connection->quote($time).');');

        return true;
    }

    /**
    * {@inheritDoc}
    */
    public function getTemplate($path)
    {
        $stmt = $this->runSQL('SELECT * FROM `'. self::VIEW_TABLE_NAME .'` WHERE `path` = '.$this->connection->quote($path).' LIMIT 1;');

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return Template::create($row['path'], $row['source'], $row['is_editable'], $row['updated']);
        }

        throw new \RuntimeException(sprintf('Template with path "%s" does not exists.', $path));
    }

    /**
    * {@inheritDoc}
    */
    public function removeTemplate(TemplateInterface $template)
    {
        $path = $template->getPath();

        if ($this->hasTemplate($path) === false) {
            return false;
        }

        $this->runSQL('DELETE FROM `'.self::VIEW_TABLE_NAME.'` WHERE `path` = '.$this->connection->quote($path).';');

        return true;
    }

    /**
    * {@inheritDoc}
    */
    public function removeAllTemplates()
    {
        return $this->runSQL('DELETE FROM `'.self::VIEW_TABLE_NAME.'`');
    }

    /**
    * {@inheritDoc}
    */
    public function hasTemplate($path)
    {
        $stmt = $this->runSQL('SELECT COUNT(*) AS counter FROM `'. self::VIEW_TABLE_NAME .'` WHERE `path` = '.$this->connection->quote($path).';');

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if ($row['counter'] == 0) {
                return false;
            }
        }

        return true;
    }

    /**
    * Proxy method to connection object. If an error occurred because of unfound table, tries to create table and rerun request.
    *
    * @param string $query SQL query
    * @param array $parameters query parameters
    */
    private function runSQL($query, array $parameters = array())
    {
        try {
            return $this->connection->executeQuery($query, $parameters);
        } catch (\Exception $e) {
            $this->connection->executeQuery(sprintf('CREATE TABLE %s (`path` VARCHAR(512) UNIQUE, `source` TEXT, `is_editable` TINYINT(1), `updated` INT(11));', self::VIEW_TABLE_NAME));
        }

        return $this->connection->executeQuery($query, $parameters);
    }
}
