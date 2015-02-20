<?php

namespace Pum\Core\Extension\Media\Metadata;

use Doctrine\DBAL\Connection;
use Pum\Core\Extension\Util\Namer;
use Pum\Bundle\TypeExtraBundle\Model\Media;
use Pum\Bundle\TypeExtraBundle\Model\MediaMetadata;

class MediaMetadataStorage
{
    const MEDIA_METADATA_TABLE_PREFIX = 'media_metadata_';

    /**
     * DBAL Connection
     * @var Connection
     */
    private $connection;

    /**
     * Project MediaMetadata table name
     * @var string
     */
    public $tableName;

    /**
     * @param Doctrine\DBAL\Connection     $connection
     * @param string                       $projectName
     */
    public function __construct(Connection $connection, $projectName)
    {
        $this->connection  = $connection;
        $this->tableName = self::MEDIA_METADATA_TABLE_PREFIX.Namer::toLowercase($projectName);
    }

    /**
     * Insert MediaMetadata in the database
     * @param  string  $mediaId
     * @param  string  $mediaMime
     * @param  string  $mediaWidth
     * @param  string  $mediaHeight
     * @return  Pum\Bundle\TypeExtraBundle\Model\MediaMetadata
     */
    public function storeMetadatas($mediaId, $mediaMime, $mediaSize, $mediaWidth, $mediaHeight)
    {
        return $this->runSQL('INSERT INTO '.$this->tableName.' (`id`, `mime`, `size`, `width`, `height`) VALUES ('.$this->connection->quote($mediaId).','.$this->connection->quote($mediaMime).','.$this->connection->quote($mediaSize).','.$this->connection->quote($mediaWidth).','.$this->connection->quote($mediaHeight).');');
    }

    /**
     * Remove MediaMetadata from the database
     * @param  string  $mediaId
     */
    public function removeMetadatas($mediaId)
    {
        return $this->runSQL("DELETE FROM `".$this->tableName."` WHERE id = '".$mediaId."'");
    }

    /**
     * Get existent MediaMetadatas in the database
     * @param   string  $mediaId
     * @return  Pum\Bundle\TypeExtraBundle\Model\MediaMetadata
     */
    public function getMediaMetadatas($mediaId)
    {
        $result = $this->runSQL("SELECT * FROM `".$this->tableName."` WHERE id = ".$this->connection->quote($mediaId))->fetch(\PDO::FETCH_ASSOC);

        return new MediaMetadata($result['mime'], $result['size'], $result['width'], $result['height']);
    }

    /**
     * Set the project name from context
     * @param  string  $projectName
     */
    public function refreshProjectName($projectName)
    {
        $this->tableName = self::MEDIA_METADATA_TABLE_PREFIX.Namer::toLowercase($projectName);

        return $this;
    }

    /**
    * Proxy method to connection object. If an error occurred because of unfound table, tries to create table and rerun request.
    * @param   string                           $query SQL query
    * @param   array                            $parameters query parameters
    * @return  \Doctrine\DBAL\Driver\Statement  The executed statement.
    */
    private function runSQL($query, array $parameters = array())
    {
        try {
            return $this->connection->executeQuery($query, $parameters);
        } catch (\Exception $e) {
            $isSqliteDriver = $this->connection->getDriver() instanceof SqliteDriver ? true : false;
            if ($isSqliteDriver) {
                $this->connection->executeQuery(sprintf('CREATE TABLE %s (`id` VARCHAR(512), `mime` VARCHAR(512), `size` VARCHAR(512), `width` VARCHAR(512), `height` VARCHAR(512), PRIMARY KEY (`id`))'.';', $this->tableName));
            } else {
                $this->connection->executeQuery(sprintf('CREATE TABLE %s (`id` VARCHAR(255) UNIQUE, `mime` VARCHAR(512), `size` VARCHAR(512), `width` VARCHAR(512), `height` VARCHAR(512), PRIMARY KEY (`id`))'.'DEFAULT CHARSET = utf8 COLLATE = utf8_unicode_ci;', $this->tableName));
            }
        }

        return $this->connection->executeQuery($query, $parameters);
    }
}
