import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Composable to check permission level for current admin
 * 
 * Permission Levels:
 * - 'write'  : Full access - can do everything
 * - 'read'   : Read only - can view but cannot edit/submit
 * - 'access' : Visibility only - should not reach here (blocked by middleware)
 * - 'none'   : No access - should not reach here (blocked by middleware)
 * 
 * Usage:
 * const { isReadOnly, canWrite, readOnlyClass } = useReadOnly();
 */
export function useReadOnly() {
    const page = usePage();

    // Get admin data from page props
    const admin = computed(() => page.props.admin);

    // Get permission level (set by middleware)
    const permissionLevel = computed(() => {
        return admin.value?.permissionLevel || 'write';
    });

    // Current menu group being accessed
    const menuGroup = computed(() => {
        return admin.value?.menuGroup || null;
    });

    // Check if super admin (always has full access)
    const isSuperAdmin = computed(() => {
        return admin.value?.role === 'super_admin';
    });

    // Check if in read-only mode
    const isReadOnly = computed(() => {
        if (isSuperAdmin.value) return false;
        return permissionLevel.value === 'read';
    });

    // Check if can write (full access)
    const canWrite = computed(() => {
        if (isSuperAdmin.value) return true;
        return permissionLevel.value === 'write';
    });

    // Check if has specific permission
    const hasPermission = (permission) => {
        if (isSuperAdmin.value) return true;
        return admin.value?.permissions?.includes(permission) || false;
    };

    // Get read-only message for display
    const readOnlyMessage = computed(() => {
        if (!isReadOnly.value) return null;
        return 'Mode Baca Saja - Anda hanya dapat melihat data, tidak dapat melakukan perubahan.';
    });

    // CSS class for form containers when read-only
    const readOnlyClass = computed(() => {
        return isReadOnly.value ? 'read-only-mode' : '';
    });

    // CSS class for individual form elements
    const disabledClass = computed(() => {
        return isReadOnly.value ? 'opacity-60 pointer-events-none cursor-not-allowed' : '';
    });

    // Form submit handler - blocks submit if read-only
    const handleSubmit = (event, callback) => {
        if (isReadOnly.value) {
            event.preventDefault();
            alert('Mode Baca Saja - Tidak dapat melakukan perubahan.');
            return false;
        }
        if (callback) callback(event);
        return true;
    };

    return {
        permissionLevel,
        menuGroup,
        isReadOnly,
        canWrite,
        isSuperAdmin,
        hasPermission,
        readOnlyMessage,
        readOnlyClass,
        disabledClass,
        handleSubmit
    };
}

export default useReadOnly;
