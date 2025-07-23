<template>
    <!-- Success Message -->
<div v-if="successMessage" class="alert alert-success p-3">
  {{ successMessage }}
</div>

<!-- Error Message -->
<div v-if="errorMessage" class="alert alert-danger p-3">
  {{ errorMessage }}
</div>
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header"><h5 class="card-title mb-0">Register</h5></div>
        <div class="card-body">
          <form @submit.prevent="submitForm">
            <div class="mb-3">
              <label>Name</label>
              <input v-model="form.name" class="form-control" type="text" placeholder="Enter your name" />
            </div>
            <div class="mb-3">
              <label>Company</label>
              <input v-model="form.company" class="form-control" type="text" placeholder="Enter company" />
            </div>
            <div class="mb-3">
              <label>Email</label>
              <input v-model="form.email" class="form-control" type="email" placeholder="Enter email" />
            </div>
            <div class="mb-3">
              <label>Password</label>
              <input v-model="form.password" class="form-control" type="password" placeholder="Enter password" />
            </div>
            <div class="text-center mt-3">
              <button type="submit" class="btn btn-primary">Sign up</button>
            </div>
          </form>
          <div v-if="registeredUser">
            <hr />
            <h6>User Registered:</h6>
            <p><strong>Name:</strong> {{ registeredUser.name }}</p>
            <p><strong>Email:</strong> {{ registeredUser.email }}</p>
            <p><strong>Company:</strong> {{ registeredUser.company }}</p>
            <p><strong>Created At:</strong> {{ registeredUser.created_at }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Register',
  data() {
    return {
      form: {
        name: '',
        company: '',
        email: '',
        password: ''
      },
      registeredUser: null,
      successMessage: '', // ✅ for success alert
      errorMessage: ''    // for error alert
    };
  },
  methods: {
  async submitForm() {
    this.successMessage = '';
    this.errorMessage = '';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
      this.errorMessage = "CSRF token missing.";
      setTimeout(() => {
        this.errorMessage = '';
      }, 3000);
      return;
    }

    try {
      const baseUrl = `${window.location.origin}${window.location.pathname.split('/').slice(0, 3).join('/')}`;
      const response = await fetch(`${baseUrl}/register-user`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(this.form)
      });

      const data = await response.json();
      console.log(data);

      if (response.ok && data.success) {
        this.successMessage = data.message;
        this.registeredUser = data.user;
        this.form = { name: '', company: '', email: '', password: '' };

        setTimeout(() => {
          this.successMessage = '';
        }, 3000); //  Hide after 3 seconds
      } else {
        this.errorMessage = data.message || 'Validation failed.';
        setTimeout(() => {
          this.errorMessage = '';
        }, 3000); // Hide after 3 seconds
      }
    } catch (error) {
      console.error('Fetch error:', error);
      this.errorMessage = 'Something went wrong.';
      setTimeout(() => {
        this.errorMessage = '';
      }, 3000); //  Hide after 3 seconds
    }
  }
}

};

</script>
