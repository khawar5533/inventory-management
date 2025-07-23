<template>
  <body class="theme-blue">
    <main class="main h-100 w-100">
      <div class="container h-100">
        <div class="row h-100">
          <div class="col-sm-10 col-md-8 col-lg-6 mx-auto d-table h-100">
            <div class="d-table-cell align-middle">
              <div class="text-center mt-4">
                <h1 class="h2">Welcome back</h1>
                <p class="lead">Sign in to your account to continue</p>
              </div>

              <div class="card">
                <div class="card-body">
                  <div class="m-sm-4">
                    <div class="text-center mb-3">
                      <!-- <img src="/assets/img/avatars/avatar.jpg" alt="User Avatar" class="img-fluid rounded-circle" width="132" height="132" /> -->
                    </div>

                    <!-- Success/Error Message -->
                    <div v-if="errorMessage" class="alert alert-danger text-center p-3">{{ errorMessage }}</div>
                    <div v-if="successMessage" class="alert alert-success text-center p-3">{{ successMessage }}</div>

                    <form @submit.prevent="submitLogin">
                      <div class="mb-3">
                        <label>Email</label>
                        <input
                          v-model="email"
                          class="form-control form-control-lg"
                          type="email"
                          name="email"
                          placeholder="Enter your email"
                          required
                        />
                      </div>
                      <div class="mb-3">
                        <label>Password</label>
                        <input
                          v-model="password"
                          class="form-control form-control-lg"
                          type="password"
                          name="password"
                          placeholder="Enter your password"
                          required
                        />
                        <small>
                          <a href="#">Forgot password?</a>
                        </small>
                      </div>
                      <div class="form-check align-items-center mb-3">
                        <input id="rememberMe" type="checkbox" class="form-check-input" value="remember-me" name="remember-me" />
                        <label class="form-check-label text-small" for="rememberMe">Remember me next time</label>
                      </div>
                      <div class="text-center mt-3">
                        <button type="submit" class="btn btn-lg btn-primary">Sign in</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </main>
  </body>
</template>

<script>
	export default {
	name: "Login",
	data() {
		return {
		email: "",
		password: "",
		errorMessage: "",
		successMessage: "",
		};
	},
	methods: {
		async submitLogin() {
		const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

		if (!csrfToken) {
			this.errorMessage = "CSRF token missing.";
			setTimeout(() => (this.errorMessage = ""), 3000);
			return;
		}

		try {
	
			const baseUrl = window.location.origin + window.location.pathname.split('/').slice(0, 3).join('/');
			const response = await fetch(`${baseUrl}/login-user`, {
			method: "POST",
			headers: {
				"Content-Type": "application/json",
				"X-CSRF-TOKEN": csrfToken,
			},
			body: JSON.stringify({
				email: this.email,
				password: this.password,
			}),
			});

			const result = await response.json();

			if (!response.ok || !result.success) {
			this.errorMessage = result.message || "Login failed.";
			setTimeout(() => (this.errorMessage = ""), 3000);
			return;
			}

			this.successMessage = result.message || "Login successful!";
				setTimeout(() => {
			const baseUrl = window.location.origin + window.location.pathname.split('/').slice(0, 3).join('/');
			window.location.href = `${baseUrl}/dashboard`; // redirect dynamically
			}, 1000); // adjust delay as needed
		} catch (err) {
			this.errorMessage = "Something went wrong. Please try again.";
			setTimeout(() => (this.errorMessage = ""), 3000);
		}
		},
	},
	};
</script>


