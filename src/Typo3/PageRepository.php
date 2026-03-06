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

        $pageIds = $this->getTreePageIds($rootId);

        if (empty($pageIds)) {
            return [];
        }

        $idList = implode(',', $pageIds);

        $sql = "
            SELECT uid, slug
            FROM pages
            WHERE
                uid IN ($idList)
                AND deleted = 0
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

    private function getTreePageIds(int $rootId): array
    {

        $ids = [$rootId];
        $queue = [$rootId];

        while (!empty($queue)) {

            $pid = array_shift($queue);

            $sql = "
                SELECT uid
                FROM pages
                WHERE
                    pid = $pid
                    AND deleted = 0
            ";

            $result = $this->conn->query($sql);

            if (!$result) {
                continue;
            }

            while ($row = $result->fetch_assoc()) {

                $ids[] = $row['uid'];
                $queue[] = $row['uid'];

            }

        }

        return $ids;

    }

}