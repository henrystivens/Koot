// permissions.js

(() => {
    'use strict';

    /**
     * Utility to show the loader.
     */
    function showLoader() {
        const loader = document.getElementById('loader');
        if (loader) loader.style.display = '';
    }

    /**
     * Utility to hide the loader.
     */
    function hideLoader() {
        const loader = document.getElementById('loader');
        if (loader) loader.style.display = 'none';
    }

    /**
     * Updates the permissions title according to the selected role.
     * @param {string} roleName
     */
    function updatePermissionsTitle(roleName) {
        const permissionsTitle = document.getElementById('permissions-title');
        if (permissionsTitle) {
            permissionsTitle.textContent = `Permissions of <${roleName}>`;
        }
    }

    /**
     * Checks the checkboxes according to the received permissions.
     * @param {number[]} permissions
     */
    function updateCheckboxes(permissions) {
        document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
            const resourceId = parseInt(checkbox.dataset.resourceId, 10);
            checkbox.checked = permissions.includes(resourceId);
        });
    }

    /**
     * Gets the selected role.
     * @returns {{id: string, name: string}|null}
     */
    function getSelectedRole() {
        const roleSelect = document.getElementById('role-select');
        if (!roleSelect) return null;
        const selectedOption = roleSelect.options[roleSelect.selectedIndex];
        if (!selectedOption || !selectedOption.value) return null;
        return {
            id: selectedOption.value,
            name: selectedOption.dataset.roleName
        };
    }

    /**
     * Handles the role change event.
     */
    function onRoleChange() {
        const role = getSelectedRole();
        if (!role) return;
        updatePermissionsTitle(role.name);
        showLoader();
        fetch(`/admin/permissions/byRole/${encodeURIComponent(role.id)}`)
            .then(response => response.json())
            .then(data => {
                if (Array.isArray(data.permissions)) {
                    updateCheckboxes(data.permissions);
                }
            })
            .catch(() => alert('Error loading permissions'))
            .finally(hideLoader);
    }

    /**
     * Handles the change event of a permission checkbox.
     * @param {Event} e
     */
    function onCheckboxChange(e) {
        const checkbox = e.target;
        if (!checkbox.classList.contains('permission-checkbox')) return;
        const role = getSelectedRole();
        if (!role) {
            alert('Please select a role first.');
            checkbox.checked = !checkbox.checked;
            return;
        }
        const resourceId = checkbox.dataset.resourceId;
        const action = checkbox.checked ? 'assignPermission' : 'revokePermission';
        showLoader();
        fetch(`/admin/permissions/${action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ roles_id: role.id, resources_id: resourceId })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert('Failed to update permission');
                checkbox.checked = !checkbox.checked;
            }
        })
        .catch(() => {
            alert('Network error');
            checkbox.checked = !checkbox.checked;
        })
        .finally(hideLoader);
    }

    /**
     * Initializes the events for the permissions page.
     */
    function init() {
        const roleSelect = document.getElementById('role-select');
        if (roleSelect) {
            roleSelect.addEventListener('change', onRoleChange);
        }
        // Event delegation for checkboxes
        const permissionsContainer = document.getElementById('permissions-container');
        if (permissionsContainer) {
            permissionsContainer.addEventListener('change', onCheckboxChange);
        }
    }

    document.addEventListener('DOMContentLoaded', init);
})();
