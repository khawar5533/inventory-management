<template>
  <div class="col-12 col-xl-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">Assign Role to User</h5>
        <h6 class="card-subtitle text-muted">This form is used to assign the role to a user.</h6>
      </div>
      <div class="card-body">
        <!-- Alert Messages -->
        <div v-if="successMessage" class="alert alert-success text-center p-2">
          {{ successMessage }}
        </div>
        <div v-if="errorMessage" class="alert alert-danger text-center p-2">
          {{ errorMessage }}
        </div>

        <form @submit.prevent="assignRole">
          <!-- User Dropdown -->
          <div class="mb-3">
            <label class="form-label">Select User</label>
            <select v-model="selectedUserId" class="form-select">
              <option value="" disabled>Select a user</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.name }}
              </option>
            </select>
          </div>

          <!-- Role Dropdown -->
          <div class="mb-3">
            <label class="form-label">Select Role</label>
            <select v-model="selectedRoleId" class="form-select">
              <option value="" disabled>Select a role</option>
              <option v-for="role in roles" :key="role.id" :value="role.id">
                {{ role.name }}
              </option>
            </select>
          </div>

          <button type="submit" class="btn btn-primary">Save</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UserRole',
  data() {
    return {
      users: [],
      roles: [],
      selectedUserId: '',
      selectedRoleId: '',
      successMessage: '',
      errorMessage: '',
    };
  },
  mounted() {
    this.fetchUsers();
    this.fetchRoles();
  },
  methods: {
    async fetchUsers() {
      try {
        const response = await fetch(`${window.baseUrl}/get-users`);
        const data = await response.json();
        this.users = data;
      } catch (error) {
        console.error('Failed to fetch users:', error);
      }
    },
    async fetchRoles() {
      try {
        const response = await fetch(`${window.baseUrl}/get-roles`);
        const data = await response.json();
        this.roles = data;
      } catch (error) {
        console.error('Failed to fetch roles:', error);
      }
    },
    async assignRole() {
      this.successMessage = '';
      this.errorMessage = '';

      try {
        const response = await fetch(`${window.baseUrl}/assign-role/${this.selectedUserId}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          },
          body: JSON.stringify({
            role_id: this.selectedRoleId, // 👈 if backend expects single role_id, not array
          }),
        });

        const result = await response.json();

        if (response.ok) {
          this.successMessage = result.message || 'Role assigned successfully';
            setTimeout(() => {
            this.successMessage = '';
        }, 3000);
        } else {
          this.errorMessage = result.message || 'Something went wrong.';
          setTimeout(() => {
            this.errorMessage = '';
        }, 3000);
          console.error(result);
        }
      } catch (error) {
        console.error('Role assignment failed:', error);
        this.errorMessage = 'Failed to assign role. Check console for details.';
        setTimeout(() => {
        this.errorMessage = '';
        }, 3000);
      }
    },
  },
};
</script>
