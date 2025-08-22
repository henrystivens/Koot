<?php

declare(strict_types=1);

/**
 * Model for the `permissions` table.
 */
class Permissions extends LiteRecord
{
    /**
     * @var string The table name.
     */
    protected static $table = 'permissions';

    /**
     * @var string The primary key of the table.
     */
    protected static $pk = 'id';

    /**
     * Get permissions by role ID.
     *
     * @param int $roleId
     * @return array
     */
    public static function getByRoleId(int $roleId): array
    {
        return self::filter('WHERE roles_id = :roleId', ['roleId' => $roleId]);
    }
}
