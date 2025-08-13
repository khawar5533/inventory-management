<template>
  <div class="row">
    <!-- Sidebar -->
    <div class="col-md-3 col-xl-2">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Profile Settings</h5>
        </div>
        <div class="list-group list-group-flush" role="tablist">
          <a class="list-group-item list-group-item-action active" data-bs-toggle="list" href="#account" role="tab">Account</a>
          <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#password" role="tab">Password</a>
        </div>
      </div>
    </div>

    <!-- Main content -->
    <div class="col-md-9 col-xl-10">
      <div class="tab-content">

        <!-- Account Tab -->
        <div class="tab-pane fade show active" id="account" role="tabpanel">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title mb-0">Private info</h5>
            </div>
            <div class="card-body">
              <div v-if="alertMessage" :class="['alert p-2', alertType === 'success' ? 'alert-success' : 'alert-danger']" role="alert">
                {{ alertMessage }}
              </div>

              <form @submit.prevent="updateUser">
                <div class="row">
                  <div class="col-md-8">
                    <div class="mb-3">
                      <label for="inputUsername">Username</label>
                      <input type="text" class="form-control" id="inputUsername" v-model="form.name" placeholder="Username">
                    </div>
                    <div class="mb-3">
                      <label for="inputCompany">Company</label>
                      <input type="text" class="form-control" id="inputCompany" v-model="form.company" placeholder="Company name">
                    </div>
                  </div>
                  <div class="col-md-4 text-center">
                    <img v-if="preview" :src="preview" class="rounded-circle img-responsive mt-2" width="128" height="128" />
                    <div class="mt-2">
                      <label for="user_image" class="btn btn-primary"><i class="fas fa-upload"></i> Upload</label>
                      <input type="file" id="user_image" name="user_image" accept="image/*" style="display: none;" @change="handleImage">
                    </div>
                    <small>For best results, use an image at least 128px by 128px in .jpg format</small>
                  </div>
                </div>

                <div class="row">
                  <div class="mb-3 col-md-6">
                    <label for="inputFirstName">First name</label>
                    <input type="text" class="form-control" id="inputFirstName" v-model="form.first_name" placeholder="First name">
                  </div>
                  <div class="mb-3 col-md-6">
                    <label for="inputLastName">Last name</label>
                    <input type="text" class="form-control" id="inputLastName" v-model="form.last_name" placeholder="Last name">
                  </div>
                </div>

                <div class="mb-3">
                  <label for="inputEmail4">Email</label>
                  <input type="email" class="form-control" id="inputEmail4" v-model="form.email" placeholder="Email">
                </div>

                <div class="mb-3">
                  <label for="inputAddress">Address</label>
                  <input type="text" class="form-control" id="inputAddress" v-model="form.address" placeholder="1234 Main St">
                </div>

                <div class="mb-3">
                  <label for="inputAddress2">Address 2</label>
                  <input type="text" class="form-control" id="inputAddress2" v-model="form.address2" placeholder="Apartment, studio, or floor">
                </div>

                <div class="row">
                  <div class="mb-3 col-md-3">
                    <label for="inputCity">City</label>
                    <input type="text" class="form-control" id="inputCity" v-model="form.city" placeholder="Enter the city">
                  </div>
                  <div class="mb-3 col-md-4">
                    <label for="inputState">State</label>
                    <input type="text" class="form-control" id="inputState" v-model="form.state" placeholder="Enter the state">
                  </div>
                  <div class="mb-3 col-md-3">
                    <label for="inputZip">Zip</label>
                    <input type="text" class="form-control" id="inputZip" v-model="form.zip" placeholder="Enter the zip">
                  </div>
                  <div class="mb-3 col-md-2">
                    <label for="inputCountry">Country</label>
                    <input type="text" class="form-control" id="inputCountry" v-model="form.country" placeholder="Enter the Country i.e United States">
                  </div>
                </div>

                <button type="submit" class="btn btn-primary">Save changes</button>
              </form>
            </div>
          </div>
        </div>

        <!-- Password Tab -->
        <div class="tab-pane fade" id="password" role="tabpanel">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Password</h5>
              <div v-if="alertMessage" :class="['alert p-2', alertType === 'success' ? 'alert-success' : 'alert-danger']" role="alert">
                {{ alertMessage }}
              </div>

              <form @submit.prevent="updatePassword">
                <div class="mb-3">
                  <label for="inputPasswordCurrent">Current password</label>
                  <input type="password" v-model="currentPassword" class="form-control" id="inputPasswordCurrent" placeholder="Enter current password" />
                </div>

                <div class="mb-3">
                  <label for="inputPasswordNew">New password</label>
                  <input type="password" v-model="newPassword" class="form-control" id="inputPasswordNew" placeholder="Enter new password" />
                </div>

                <div class="mb-3">
                  <label for="inputPasswordNew2">Verify password</label>
                  <input type="password" v-model="verifyPassword" class="form-control" id="inputPasswordNew2" placeholder="Confirm new password" />
                </div>

                <button type="submit" class="btn btn-primary">Save changes</button>
              </form>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "UserProfile",
  data() {
    return {
      form: {
        name: "",
        company: "",
        email: "",
        first_name: "",
        last_name: "",
        address: "",
        address2: "",
        city: "",
        state: "",
        zip: "",
        country: ""
      },
      currentPassword: "",   // user manually enters current password
      newPassword: "",
      verifyPassword: "",
      imageFile: null,
      preview: null,
      userId: null,
      alertMessage: "",
      alertType: "" // 'success' or 'error'
    };
  },
  mounted() {
    this.getUserData();
  },
  methods: {
    async getUserData() {
      try {
        const res = await fetch(`${window.baseUrl}/user/profile-data`);
        const data = await res.json();
        if (data.error) {
          this.showAlert(data.error, "error");
          return;
        }
        Object.keys(this.form).forEach(key => {
          this.form[key] = data[key] ?? "";
        });
        this.userId = data.id;
        if (data.user_image) {
          this.preview = `${window.baseUrl}/storage/${data.user_image}`;
        }
        // Do NOT pre-fill currentPassword for security
        this.currentPassword = "";
        this.newPassword = "";
        this.verifyPassword = "";
      } catch (error) {
        console.error("Error fetching user data:", error);
        this.showAlert("Failed to load user data.", "error");
      }
    },

    async handleImage(event) {
      const file = event.target.files[0];
      if (!file) return;

      // Just preview the new image, do NOT delete old image yet
      this.imageFile = file;
      this.preview = URL.createObjectURL(file);
    },

    async updateUser() {
      const formData = new FormData();
      Object.keys(this.form).forEach(key => formData.append(key, this.form[key]));
      if (this.imageFile) formData.append("user_image", this.imageFile);

      try {
        const res = await fetch(`${window.baseUrl}/users/update/${this.userId}`, {
          method: "POST",
          headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
          },
          body: formData
        });

        const result = await res.json();

        if (result.error) {
          this.showAlert(result.error, "error");
        } else {
          this.showAlert("Profile updated successfully.", "success");

          // Optional: delete old image on server if replaced
          if (this.imageFile) {
            try {
              await fetch(`${window.baseUrl}/users/${this.userId}/delete-old-image`, {
                method: "POST",
                headers: {
                  "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                }
              });
            } catch (err) {
              console.warn("Failed to delete old image:", err);
            }
          }

          this.getUserData(); // refresh form
          this.imageFile = null;
        }
      } catch (error) {
        console.error("Error updating user:", error);
        this.showAlert("Failed to update user profile.", "error");
      }
    },

    async updatePassword() {
      // Validate new password & confirmation first
      if (!this.currentPassword) return this.showAlert("Enter your current password", "error");
      if (!this.newPassword) return this.showAlert("Enter a new password", "error");
      if (this.newPassword !== this.verifyPassword) return this.showAlert("New passwords do not match", "error");

      try {
        const res = await fetch(`${window.baseUrl}/user/update-password`, {
          method: "POST",
          headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            current_password: this.currentPassword,
            new_password: this.newPassword,
            new_password_confirmation: this.verifyPassword
          })
        });

        const result = await res.json();

        if (result.success) {
          this.showAlert(result.message, "success");
          this.currentPassword = "";
          this.newPassword = "";
          this.verifyPassword = "";
        } else {
          this.showAlert(result.message, "error");
        }
      } catch (error) {
        console.error("Error updating password:", error);
        this.showAlert("Failed to update password.", "error");
      }
    },

    showAlert(message, type) {
      this.alertMessage = message;
      this.alertType = type;
      setTimeout(() => {
        this.alertMessage = "";
        this.alertType = "";
      }, 3000);
    }
  }
};
</script>

