<template>
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Role Permissions</h5>
        </div>
        <div class="card-body">

          <!--  Loop each permission as a separate alert -->
          <div
            v-for="perm in permissions"
            :key="perm.id"
            class="alert alert-primary alert-outline alert-dismissible"
            role="alert"
          >
            <div class="alert-icon">
              <i class="far fa-fw fa-bell"></i>
            </div>
            <div class="alert-message">
              <strong>{{ formatPermissionName(perm.name) }}</strong> 
            </div>
           <button
            type="button"
            class="btn-close"
            @click="softDeletePermission(perm.id)"
            aria-label="Close"
            >
         </button>
          </div>

          <!-- Fallback (only shown after loading completes) -->
          <div
            v-if="!loading && permissions.length === 0"
            class="alert alert-warning"
            role="alert"
          >
            No permissions found for this user.
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ShowPermission',
  data() {
    return {
      roleIds: [],
      permissions: [],
      loading: true, // loading state added
    };
  },
  mounted() {
    const baseUrl = window.baseUrl || '';
    fetch(`${baseUrl}/get-user-role-ids`)
      .then(response => response.json())
      .then(data => {
        this.roleIds = data.roleIds;

        if (this.roleIds.length > 0) {
          this.fetchPermissions(this.roleIds[0]);
        } else {
          this.loading = false; // still stop loading if no roles
        }
      })
      .catch(error => {
        console.error('Error fetching roleIds:', error);
        this.loading = false;
      });
  },
  methods: {
  formatPermissionName(name) {
    if (!name) return '';
    return name
      .replace(/_/g, ' ')
      .replace(/\b\w/g, char => char.toUpperCase());
  },

  fetchPermissions(roleId) {
    const baseUrl = window.baseUrl || '';
    fetch(`${baseUrl}/get-role-permissions/${roleId}`)
      .then(response => response.json())
      .then(data => {
        this.permissions = data.permissions;
        this.loading = false;
      })
      .catch(error => {
        console.error('Error fetching permissions:', error);
        this.loading = false;
      });
  },

  softDeletePermission(id) {
    const baseUrl = window.baseUrl || '';
    fetch(`${baseUrl}/soft-delete-permission/${id}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
    })
      .then(response => response.json())
      .then(data => {
        // Remove the deleted permission from the list
        this.permissions = this.permissions.filter(p => p.id !== id);
      })
      .catch(error => {
        console.error('Soft delete failed:', error);
      });
  }
}
};
</script>
