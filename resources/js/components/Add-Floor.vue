<template>
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ isEditing ? 'Edit Floor' : 'Add Floor' }}</h5>
        </div>
        <div class="card-body">
          <!-- Alerts -->
            <div v-if="successMessage" class="alert alert-success p-3">
            {{ successMessage }}
            </div>
            <div v-if="errorMessage" class="alert alert-danger p-3">
            {{ errorMessage }}
            </div>

          <form @submit.prevent="submitForm">
            <input type="hidden" name="_token" :value="csrfToken" />
            <div class="mb-3">
              <label class="form-label">Location</label>
              <select class="form-control" v-model="form.location_id" required>
                <option value="">Select Location</option>
                <option v-for="location in locations" :key="location.id" :value="location.id">
                  {{ location.name }}
                </option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Floor Name</label>
              <input
                type="text"
                class="form-control"
                v-model="form.name"
                placeholder="Enter Floor Name"
                required
              />
            </div>
            <button type="submit" class="btn btn-primary">
              {{ isEditing ? 'Update' : 'Submit' }}
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Floor List -->
    <div class="col-12 mt-4">
      <div class="card">
        <div class="card-body">
        <div class="card-header">
          <h5 class="card-title">Saved Floors</h5>
          <h6 class="card-subtitle text-muted">All submitted floors will appear here.</h6>
        </div>
          <table class="table table-action">
            <thead>
              <tr>
                <th>Floor Name</th>
                <th>Location</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(floor, index) in floors" :key="floor.id">
                <td>{{ floor.name }}</td>
                <td>{{ floor.location?.name }}</td>
                <td>
                  <a href="#" @click.prevent="editFloor(index)">
                     <i class="align-middle fas fa-fw fa-pen"></i>
                  </a>
                  <a href="#" @click.prevent="deleteFloor(index)">
                     <i class="fas fa-trash text-danger"></i>
                  </a>
                </td>
              </tr>
              <tr v-if="floors.length === 0">
                <td colspan="3" class="text-center">No floor added yet.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AddFloor',
  data() {
    return {
      form: {
        id: null,
        name: '',
        location_id: ''
      },
      floors: [],
      locations: [],
      isEditing: false,
      successMessage: '',
      showAlert: false
    };
  },
  mounted() {
    this.getFloors();
    this.getLocations();
  },
  computed: {
    csrfToken() {
      return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }
  },
  methods: {
    async getLocations() {
      try {
        const response = await fetch(`${window.baseUrl}/locations`);
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const data = await response.json();
        this.locations = Array.isArray(data) ? data : [];
      } catch (error) {
        console.error('Failed to load locations:', error);
      }
    },

    async getFloors() {
      try {
        const response = await fetch(`${window.baseUrl}/floors`);
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const data = await response.json();
        this.floors = Array.isArray(data) ? data : [];
      } catch (error) {
        console.error('Failed to load floors:', error);
      }
    },

    async submitForm() {
      const url = this.isEditing
        ? `${window.baseUrl}/floors/update/${this.form.id}`
        : `${window.baseUrl}/floors`;

      try {
        const response = await fetch(url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': this.csrfToken
          },
          body: JSON.stringify(this.form)
        });

        if (!response.ok) {
          const error = await response.json();
          throw new Error(error.message || 'Failed to save');
        }

        const result = await response.json();
        this.successMessage = this.isEditing ? 'Floor updated successfully!' : 'Floor added successfully!';
        this.showAlert = true;

        await this.getFloors();
        this.resetForm();
      } catch (error) {
        console.error('Submit error:', error.message);
      } finally {
        this.clearMessagesAfterDelay();
      }
    },

    editFloor(index) {
      const floor = this.floors[index];
      this.form = {
        id: floor.id,
        name: floor.name,
        location_id: floor.location?.id || ''
      };
      this.isEditing = true;
    },

    async deleteFloor(index) {
      const floor = this.floors[index];
      if (!confirm(`Are you sure you want to delete floor "${floor.name}"?`)) return;

      try {
        const response = await fetch(`${window.baseUrl}/floors/${floor.id}`, {
          method: 'DELETE',
          headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': this.csrfToken
          }
        });

        if (!response.ok) {
          const error = await response.json();
          throw new Error(error.message || 'Delete failed');
        }

        this.successMessage = 'Floor deleted successfully!';
        this.showAlert = true;
        await this.getFloors();
      } catch (error) {
        console.error('Delete error:', error.message);
      } finally {
        this.clearMessagesAfterDelay();
      }
    },

    resetForm() {
      this.form = {
        id: null,
        name: '',
        location_id: ''
      };
      this.isEditing = false;
    },

    clearMessagesAfterDelay() {
      setTimeout(() => {
        this.showAlert = false;
        this.successMessage = '';
      }, 3000);
    }
  }
};
</script>



