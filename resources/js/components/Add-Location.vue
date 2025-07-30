<template>
  <div>
    <!-- Form Section -->
    <div class="col-12 col-xl-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Add Location</h5>
          <h6 class="card-subtitle text-muted">Enter location details.</h6>
        </div>
        <div class="card-body">
          <form @submit.prevent="addLocation">
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input v-model="form.name" type="text" class="form-control" placeholder="Location Name">
            </div>
            <div class="mb-3">
              <label class="form-label">Address</label>
              <textarea v-model="form.address" class="form-control" placeholder="Address" rows="2"></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">City</label>
              <input v-model="form.city" type="text" class="form-control" placeholder="City">
            </div>
            <div class="mb-3">
              <label class="form-label">State</label>
              <input v-model="form.state" type="text" class="form-control" placeholder="State">
            </div>
            <div class="mb-3">
              <label class="form-label">ZIP Code</label>
              <input v-model="form.zip_code" type="text" class="form-control" placeholder="ZIP Code">
            </div>
            <div class="mb-3">
              <label class="form-label">Country</label>
              <input v-model="form.country" type="text" class="form-control" placeholder="Country">
            </div>
            <button v-if="!isEditing" type="submit" class="btn btn-primary" @click.prevent="addLocation">Add</button>
            <button v-else type="submit" class="btn btn-success" @click.prevent="updateLocation">Update</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Table Section -->
    <div class="col-12 col-xl-12 mt-4">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Saved Locations</h5>
          <h6 class="card-subtitle text-muted">All submitted locations will appear here.</h6>
        </div>
        <table class="table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Address</th>
              <th>City</th>
              <th>State</th>
              <th>Country</th>
              <th>ZIP</th>

              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(location, index) in locations" :key="location.id">
              <td>{{ location.name }}</td>
              <td>{{ location.address }}</td>
              <td>{{ location.city }}</td>
              <td>{{ location.state }}</td>
              <td>{{ location.country }}</td>
              <td>{{ location.zip_code }}</td>
              <td class="table-action">
               <a href="#" @click.prevent="editLocation(index)">
                <i class="align-middle fas fa-fw fa-pen"></i>
              </a>
                <a href="#" @click.prevent="deleteLocation(index)">
                  <i class="fas fa-trash text-danger"></i>
                </a>
              </td>
            </tr>
            <tr v-if="locations.length === 0">
              <td colspan="7" class="text-center">No locations added yet.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AddLocation',
  data() {
    return {
      form: {
        id: null, // Include ID for edit mode
        name: '',
        address: '',
        city: '',
        state: '',
        zip_code: '',
        country: ''
      },
      locations: [],
      isEditing: false // Track whether editing or not
    };
  },
  mounted() {
    this.getLocations();
  },
  methods: {
    async getLocations() {
      try {
        const response = await fetch(`${window.baseUrl}/locations`);
        this.locations = await response.json();
      } catch (error) {
        console.error("Error loading locations:", error);
      }
    },

    async addLocation() {
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const response = await fetch(`${window.baseUrl}/locations`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify(this.form)
        });

        if (!response.ok) {
          throw new Error(`Request failed with status ${response.status}`);
        }

        const result = await response.json();
        this.locations.unshift(result.location);
        this.resetForm();
      } catch (error) {
        console.error("Error saving location:", error);
      }
    },

    async updateLocation() {
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const response = await fetch(`${window.baseUrl}/locations/update/${this.form.id}`, {
          method: 'POST', // Using POST instead of PUT
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify(this.form)
        });

        if (!response.ok) {
          throw new Error(`Update failed with status ${response.status}`);
        }

        const result = await response.json();

        // Update the local array
        const index = this.locations.findIndex(l => l.id === this.form.id);
        if (index !== -1) {
          this.$set(this.locations, index, result.location);
        }

        this.resetForm();
      } catch (error) {
        console.error("Error updating location:", error);
      }
    },

    editLocation(index) {
      const location = this.locations[index];
      this.form = { ...location }; // Copy fields including ID
      this.isEditing = true;
    },

    async deleteLocation(index) {
      const location = this.locations[index];

      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const response = await fetch(`${window.baseUrl}/locations/${location.id}`, {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          }
        });

        if (!response.ok) {
          const errorText = await response.text();
          console.error("Server error:", errorText);
          throw new Error(`Delete failed with status ${response.status}`);
        }

        this.locations.splice(index, 1);
      } catch (error) {
        console.error("Delete failed:", error);
      }
    },

    resetForm() {
      this.form = {
        id: null,
        name: '',
        address: '',
        city: '',
        state: '',
        zip_code: '',
        country: ''
      };
      this.isEditing = false;
    }
  }
};
</script>


