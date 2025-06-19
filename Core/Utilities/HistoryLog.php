<?php

namespace App\Core\Utilities;

use App\Models\History;
use Exception;

class HistoryLog
{
    public static function taskCreated(string $message, int $authorId, int $taskId): bool
    {
        return self::publish('create', 'task', $message, $authorId, $taskId);
    }

    public static function taskUpdated(string $message, int $authorId, int $taskId): bool
    {
        return self::publish('update', 'task', $message, $authorId, $taskId);
    }

    public static function taskDeleted(string $message, int $authorId, int $taskId): bool
    {
        return self::publish('delete', 'task', $message, $authorId, $taskId);
    }

    public static function projectCreated(string $message, int $authorId, int $projectId): bool
    {
        return self::publish('create', 'project', $message, $authorId, $projectId);
    }

    public static function projectUpdated(string $message, int $authorId, int $projectId): bool
    {
        return self::publish('update', 'project', $message, $authorId, $projectId);
    }

    public static function projectDeleted(string $message, int $authorId, int $projectId): bool
    {
        return self::publish('delete', 'project', $message, $authorId, $projectId);
    }



    private static function publish(string $action, string $type, string $message, int $authorId, int $targetId): bool
    {
        try {
            $history = new History($action, $type, $message, $authorId, $targetId);
            return $history->save();
        } catch (Exception $e) {
            return false;
        }
    }
}