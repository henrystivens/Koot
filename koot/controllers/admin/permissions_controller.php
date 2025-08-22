<?php

declare(strict_types=1);

/**
 * Controller for managing permissions.
 */
class PermissionsController extends ControllerRest
{
    /**
     * List all permissions.
     */
    public function getAll(): void
    {
        View::select('index', 'admin');
        $this->roles = Roles::all();
        $this->resources = Resources::all();
        $this->permissions = Permissions::all();
    }

    /**
     * Get permissions for a specific role via AJAX.
     */
    public function get_byRole(int $rolesId): void
    {

        if (!$rolesId) {
            $this->data = ['permissions' => []];
            return;
        }

        $permissions = Permissions::getByRoleId($rolesId);
        $resourceIds = [];
        foreach ($permissions as $permission) {
            $resourceIds[] = (int) $permission->resources_id;
        }

        $this->data = ['permissions' => $resourceIds];
    }

    /**
     * Assign a permission via AJAX.
     */
    public function post_assignPermission(): void
    {
        $json = $this->param();

        if ($json['roles_id'] && $json['resources_id']) {

            $permission = new Permissions([
                'roles_id' => (int) $json['roles_id'],
                'resources_id' => (int) $json['resources_id']
            ]);

            if ($permission->create()) {
                $this->data = ['success' => true];
            } else {
                $this->data = ['success' => false];
            }
        }
    }

    /**
     * Revoke a permission via AJAX.
     */
    public function post_revokePermission(): void
    {
        $json = $this->param();

        if ($json['roles_id'] && $json['resources_id']) {

            $permission = Permissions::filter(
                'WHERE roles_id = :rolesId AND resources_id = :resourcesId',
                    [
                    'rolesId' => (int) $json['roles_id'],
                    'resourcesId' => (int) $json['resources_id']
                ])[0] ?? null;

            if ($permission && Permissions::delete($permission->id)) {
                $this->data = ['success' => true];
            } else {
                $this->data = ['success' => false];
            }
        }
    }
}