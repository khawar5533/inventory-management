<template>
  <div>
    <!-- Alerts -->
    <div v-if="successMessage" class="alert alert-success p-3">
      {{ successMessage }}
    </div>
    <div v-if="errorMessage" class="alert alert-danger p-3">
      {{ errorMessage }}
    </div>

    <!-- Form Section -->
    <div class="col-12 col-xl-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Add Rack</h5>
          <h6 class="card-subtitle text-muted">Enter rack details.</h6>
        </div>
        <div class="card-body">
          <form @submit.prevent="isEditing ? updateRack() : addRack()">
            <div class="mb-3">
              <label class="form-label">Room</label>
              <select v-model="form.room_id" class="form-select" required>
                <option value="" disabled>Select Room</option>
                <option v-for="room in rooms" :key="room.id" :value="room.id">
                  {{ room.name }}
                </option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Rack Label</label>
              <input v-model="form.label" type="text" class="form-control" placeholder="Rack Label" required>
            </div>

            <button v-if="!isEditing" type="submit" class="btn btn-primary">Add Rack</button>
            <button v-else type="submit" class="btn btn-primary">Update Rack</button>
            <button v-if="form.id" type="button" @click="resetForm" class="btn btn-primary ms-2">Cancel</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Table Section -->
    <div class="col-12 col-xl-12 mt-4">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Saved Racks</h5>
          <h6 class="card-subtitle text-muted">List of all added racks.</h6>
        </div>
        <table class="table">
          <thead>
            <tr>
              <th>Room</th>
              <th>Label</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(rack, index) in racks" :key="rack.id">
              <td>{{ rack.room?.name }}</td>
              <td>{{ rack.label }}</td>
              <td class="table-action">
                <a href="#" @click.prevent="editRack(index)">
                  <i class="fas fa-pen"></i>
                </a>
                <a href="#" @click.prevent="deleteRack(index)">
                  <i class="fas fa-trash text-danger ms-2"></i>
                </a>
              </td>
            </tr>
            <tr v-if="racks.length === 0">
              <td colspan="5" class="text-center">No racks added yet.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Rack',
  data() {
    return {
      rooms: [],
      racks: [],
      form: {
        room_id: '',
        label: '',
      },
      isEditing: false,
      editIndex: null,
      successMessage: '',
      errorMessage: ''
    };
  },
  mounted() {
    this.fetchRooms();
    this.fetchRacks(); // won't show error on auto-load
  },
  methods: {
    async fetchRooms() {
      try {
        const response = await fetch(`${window.baseUrl}/rooms`);
        const data = await response.json();
        this.rooms = data;
      } catch (error) {
        this.errorMessage = 'Failed to load rooms.';
        this.clearMessages();
        console.error(error);
      }
    },
    async fetchRacks(showError = false) {
      try {
        const response = await fetch(`${window.baseUrl}/get-rack`);
        const data = await response.json();
        this.racks = data;
      } catch (error) {
        if (showError) {
          this.errorMessage = 'Failed to load racks.';
          this.clearMessages();
        }
        console.error('Error fetching racks:', error);
      }
    },
    async addRack() {
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      try {
        const response = await fetch(`${window.baseUrl}/add-rack`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify(this.form)
        });

        if (!response.ok) throw new Error('Add failed');

        const newRackResponse = await response.json();
        this.racks.push(newRackResponse.rack);
        this.successMessage = newRackResponse.message || 'Rack added successfully!';
        this.clearMessages();
        this.resetForm();
      } catch (err) {
        this.errorMessage = 'Error adding rack.';
        this.clearMessages();
        console.error(err);
      }
    },
    editRack(index) {
      this.isEditing = true;
      this.editIndex = index;
       const rack = this.racks[index];
       this.form = {
       id: rack.id,
        room_id: rack.room_id || rack.room?.id || '',
        label: rack.label
        };
      },
      async updateRack() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const rackId = this.racks[this.editIndex].id;

        try {
          const response = await fetch(`${window.baseUrl}/racks/${rackId}`, {
            method: 'POST', // using POST (not PUT)
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
              room_id: this.form.room_id,
              label: this.form.label
            })
          });

          const result = await response.json();

          if (!response.ok) {
            throw new Error(result.message || 'Update failed');
          }

          // Vue 3 reactivity: direct assignment
          this.racks[this.editIndex] = result.rack || result;

          this.successMessage = result.message || 'Rack updated successfully!';
          this.resetForm();

          //  auto-hide success message
          setTimeout(() => {
            this.successMessage = '';
          }, 3000);

        } catch (err) {
          this.errorMessage = err.message || 'Error updating rack.';
          
          //  auto-hide error message
          setTimeout(() => {
            this.errorMessage = '';
          }, 3000);

          console.error('Update error:', err);
        }
      },
      async deleteRack(index) {
      const rackId = this.racks[index].id;
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

      if (!confirm('Are you sure you want to delete this rack?')) return;

      try {
        const response = await fetch(`${window.baseUrl}/racks/${rackId}/delete`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          }
        });

        if (!response.ok) throw new Error('Delete failed');

        // Remove rack from the list
        this.racks.splice(index, 1);
        this.successMessage = 'Rack deleted successfully!';
        this.clearMessages();
      } catch (err) {
        this.errorMessage = 'Error deleting rack.';
        this.clearMessages();
        console.error(err);
      }
    },
    resetForm() {
      this.form = {
        room_id: '',
        label: '',
      };
      this.isEditing = false;
      this.editIndex = null;
    },

    clearMessages(duration = 3000) {
      setTimeout(() => {
        this.successMessage = '';
        this.errorMessage = '';
      }, duration);
    }
  }
};
</script>



