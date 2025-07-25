<template>
  <div class="col-12 col-xl-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">Assign Permission to Role</h5>
        <h6 class="card-subtitle text-muted">This form is used to assign permissions to a role.</h6>
      </div>
      <div class="card-body">
        <!-- Alert Messages -->
        <div v-if="successMessage" class="alert alert-success text-center p-2">
          {{ successMessage }}
        </div>
        <div v-if="errorMessage" class="alert alert-danger text-center p-2">
          {{ errorMessage }}
        </div>

        <form @submit.prevent="assignPermissionsToRole">
          <!-- Role Dropdown -->
          <div class="mb-3">
            <label class="form-label">Select Role</label>
            <select v-model="selectedRoleId" class="form-select" @change="loadRolePermissions" required>
              <option value="" disabled>Select a role</option>
              <option v-for="role in roles" :key="role.id" :value="role.id">
                {{ role.name }}
              </option>
            </select>
          </div>

          <!-- Grouped Permissions as Checkboxes -->
          <div v-for="(group, index) in permissionGroups" :key="index" class="mb-4">
            <div class="card shadow-sm">
              
              <div class="card-body row">
                <div v-for="(perm, pIndex) in group.permissions" :key="pIndex" class="col-md-4 mb-2">
                  <div class="form-check">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        :id="'perm_' + perm.value"
                        :value="perm.value.toString()"
                        v-model="selectedPermissions"
                        />
                    <label class="form-check-label" :for="'perm_' + perm.value">
                      {{ perm.label }}
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Assign Permissions</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UserPermission',
  data() {
    return {
      roles: [],
      permissionGroups: [],
      selectedPermissions: [],
      selectedRoleId: '',
      successMessage: '',
      errorMessage: '',
    };
  },
  mounted() {
    this.fetchRoles();
    this.fetchGroupedPermissions();
  },
  methods: {
    async fetchRoles() {
      try {
        const response = await fetch(`${window.baseUrl}/get-roles`);
        const data = await response.json();
        this.roles = data;
      } catch (error) {
        console.error('Failed to fetch roles:', error);
      }
    },

    async fetchGroupedPermissions() {
      try {
        const response = await fetch(`${window.baseUrl}/get-grouped-permissions`);
        const data = await response.json();
        this.permissionGroups = data;
      } catch (error) {
        console.error('Failed to fetch permissions:', error);
      }
    },

    //  Load selected role's permissions and pre-check checkboxes
    async loadRolePermissions() {
      if (!this.selectedRoleId) return;

      try {
        const response = await fetch(`${window.baseUrl}/get-role-permissions/${this.selectedRoleId}`);
        const data = await response.json();

        //  Convert to string to match checkbox values
        this.selectedPermissions = data.permission_ids.map(String);
      } catch (error) {
        console.error('Failed to load role permissions:', error);
      }
    },

    async assignPermissionsToRole() {
      this.successMessage = '';
      this.errorMessage = '';

      try {
        const response = await fetch(`${window.baseUrl}/assign-permissions-to-role`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          },
          body: JSON.stringify({
            role_id: this.selectedRoleId,
            permission_ids: this.selectedPermissions,
          }),
        });

        const result = await response.json();

        if (response.ok) {
          this.successMessage = result.message || 'Permissions assigned successfully.';
        } else {
          this.errorMessage = result.message || 'Something went wrong.';
        }

        setTimeout(() => {
          this.successMessage = '';
          this.errorMessage = '';
        }, 3000);
      } catch (error) {
        console.error('Error assigning permissions:', error);
        this.errorMessage = 'Failed to assign permissions. Check console for details.';
      }
    },
  },
};
</script>

