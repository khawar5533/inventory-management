<template>
  <div class="col-12 col-xl-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">Add User Role</h5>
        <h6 class="card-subtitle text-muted">Add role for users</h6>
      </div>
      <div class="card-body">
        <!-- Alerts -->
        <div v-if="successMessage" class="alert alert-success text-center p-2 mb-3">
          {{ successMessage }}
        </div>
        <div v-if="errorMessage" class="alert alert-danger text-center p-2 mb-3">
          {{ errorMessage }}
        </div>

        <form @submit.prevent="submitForm">
          <div class="mb-3">
            <label class="form-label">User Role</label>
            <select v-model="role" name="role" id="role" class="form-select">
              <option value="">-- Select Role --</option>
              <option value="admin">Admin</option>
              <option value="manager">Manager</option>
              <option value="vendor">Vendor</option>
            </select>
          </div>

          <button type="submit" class="btn btn-primary">Submit</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Role',
  data() {
    return {
      role: '',
      successMessage: '',
      errorMessage: ''
    };
  },
  methods: {
    async submitForm() {
      this.successMessage = '';
      this.errorMessage = '';

      try {
        const baseUrl = `${window.location.origin}${window.location.pathname.split('/').slice(0, 3).join('/')}`;
        const response = await fetch(`${window.baseUrl}/create-role`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({
            role: this.role
          })
        });

        const data = await response.json();

        if (response.ok) {
          this.successMessage = data.message;
          this.role = '';
          setTimeout(() => this.successMessage = '', 3000); // auto-clear message
        } else {
          this.errorMessage = data.error || 'Something went wrong.';
          setTimeout(() => this.errorMessage = '', 3000);
        }
      } catch (err) {
        this.errorMessage = 'Request failed.';
        setTimeout(() => this.errorMessage = '', 3000);
      }
    }
  }
};
</script>
