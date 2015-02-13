<?php

namespace Pum\Core\Media;

use Doctrine\DBAL\Connection;
use Pum\Core\Extension\Util\Namer;
use Pum\Bundle\TypeExtraBundle\Model\Media;
use Pum\Bundle\TypeExtraBundle\Model\MediaMetadata;

class MediaStorage
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

	public function __construct(Connection $connection, $projectName)
	{
		$this->connection  = $connection;
		$this->tableName = self::MEDIA_METADATA_TABLE_PREFIX.Namer::toLowercase($projectName);
	}

	public function storeMetadatas($mediaId, $mediaMime, $mediaWidth, $mediaHeight){

		$this->runSQL('INSERT INTO '.$this->tableName.' (`id`, `mime`, `width`, `height`) VALUES ('.$this->connection->quote($mediaId).','.$this->connection->quote($mediaMime).','.$this->connection->quote($mediaWidth).','.$this->connection->quote($mediaHeight).');');

		return new MediaMetadata($mediaMime, $mediaWidth, $mediaHeight);
	}

	public function removeMetadatas($mediaId){
		$this->runSQL("DELETE FROM `".$this->tableName."` WHERE id = '".$mediaId."'");

		return true;
	}

	public function getMediaMetadatas($mediaId)
	{
		$result = $this->runSQL("SELECT * FROM `".$this->tableName."` WHERE id = '".$mediaId."'")->fetch(\PDO::FETCH_ASSOC);
		

		return new MediaMetadata($result['mime'], $result['width'], $result['height']);
	}

	public function refreshProjectName($projectName)
	{
		$this->tableName = self::MEDIA_METADATA_TABLE_PREFIX.Namer::toLowercase($projectName);
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
            $isSqliteDriver = $this->connection->getDriver() instanceof SqliteDriver ? true : false;
            if ($isSqliteDriver) {
                $this->connection->executeQuery(sprintf('CREATE TABLE %s (`id` VARCHAR(512), `mime` VARCHAR(512), `width` VARCHAR(512), `height` VARCHAR(512), PRIMARY KEY (`id`))'.';', $this->tableName));
            } else {
                $this->connection->executeQuery(sprintf('CREATE TABLE %s (`id` VARCHAR(255) UNIQUE, `mime` VARCHAR(512), `width` VARCHAR(512), `height` VARCHAR(512), PRIMARY KEY (`id`))'.'DEFAULT CHARSET = utf8 COLLATE = utf8_unicode_ci;', $this->tableName));
            }
        }

        return $this->connection->executeQuery($query, $parameters);
    }
}
