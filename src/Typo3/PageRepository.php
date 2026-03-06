<?php

namespace LinkChecker\Typo3;

use LinkChecker\Infrastructure\DatabaseConnection;

class PageRepository
{

    private \mysqli $conn;

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

        $result = $this->conn->query($sql);

        if (!$result) {
            throw new \RuntimeException('SQL Error: ' . $this->conn->error);
        }

        $pages = [];

        while ($row = $result->fetch_assoc()) {
            $pages[] = $row;
        }

        return $pages;
    }

}