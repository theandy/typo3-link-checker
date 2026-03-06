<?php

namespace LinkChecker\Typo3;

use LinkChecker\Infrastructure\DatabaseConnection;

class PageRepository
{

    private $conn;

    public function __construct(DatabaseConnection $db)
    {
        $this->conn = $db->getConnection();
    }

    public function getPagesByRoot(int $rootId): array
    {

        $sql = "
            SELECT uid, slug
            FROM pages
            WHERE
                deleted = 0
                AND hidden = 0
                AND doktype = 1
        ";

        return $this->conn->fetchAllAssociative($sql);

    }

}