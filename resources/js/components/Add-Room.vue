<template>
  <div>
    <!-- Alert Messages -->
    <div v-if="successMessage" class="alert alert-success p-3">
      {{ successMessage }}
    </div>
    <div v-if="errorMessage" class="alert alert-danger p-3">
      {{ errorMessage }}
    </div>

    <!-- Add/Edit Room Form -->
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">{{ form.id ? 'Edit Room' : 'Add Room' }}</h5>
          <h6 class="card-subtitle text-muted">All submitted rooms will appear here.</h6>
      </div>
      <div class="card-body">
        <form @submit.prevent="submitForm">
          <input type="hidden" name="_token" :value="csrfToken" />

          <!-- Floor Dropdown -->
          <div class="mb-3">
            <label class="form-label">Floor</label>
            <select class="form-control" v-model="form.floor_id" required>
              <option value="">Select Floor</option>
              <option v-for="floor in floors" :key="floor.id" :value="floor.id">
                {{ floor.name }}
              </option>
            </select>
          </div>

          <!-- Room Name -->
          <div class="mb-3">
            <label class="form-label">Room Name</label>
            <input
              type="text"
              class="form-control"
              v-model="form.name"
              placeholder="Enter Room Name"
              required
            />
          </div>

          <button type="submit" class="btn btn-primary">
            {{ form.id ? 'Update Room' : 'Add Room' }}
          </button>
          <button v-if="form.id" type="button" @click="resetForm" class="btn btn-primary ms-2">Cancel</button>
        </form>
      </div>
    </div>

    <!-- Room List Table -->
    <div class="card mt-4">
      <div class="card-header">
        <h5 class="card-title mb-0">Room List</h5>
      </div>
      <div class="card-body">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Room Name</th>
              <th>Floor</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="rooms.length === 0">
              <td colspan="4" class="text-center">No rooms available.</td>
            </tr>
            <tr v-for="(room, index) in rooms" :key="room.id">
              <td>{{ room.id }}</td>
              <td>{{ room.name }}</td>
              <td>{{ room.floor?.name || 'N/A' }}</td>
              <td class="table-action">
                <a href="#" @click.prevent="editRoom(room)">
                  <i class="align-middle fas fa-fw fa-pen text-primary"></i>
                  <a href="#" @click.prevent="deleteRoom(room.id)"><i class="align-middle fas fa-fw fa-trash"></i></a>
                </a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AddRoom',
  data() {
    return {
      csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      form: {
        id: null, // Track id for editing
        name: '',
        floor_id: ''
      },
      floors: [],
      rooms: [],
      successMessage: '',
      errorMessage: ''
    };
  },
  mounted() {
    this.getFloors();
    this.getRooms();
  },
  methods: {
    async getFloors() {
      try {
        const response = await fetch(`${window.baseUrl}/floor-list`);
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const data = await response.json();
        this.floors = Array.isArray(data) ? data : [];
      } catch (error) {
        console.error('Failed to load floors:', error);
      }
    },
    async getRooms() {
      try {
        const response = await fetch(`${window.baseUrl}/room-list`);
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const data = await response.json();
        this.rooms = Array.isArray(data) ? data : [];
      } catch (error) {
        console.error('Failed to load rooms:', error);
      }
    },
    async submitForm() {
      const isUpdate = !!this.form.id;
      const url = isUpdate
        ? `${window.baseUrl}/update-room/${this.form.id}`
        : `${window.baseUrl}/add-room`;
      const method = isUpdate ? 'PUT' : 'POST';

      try {
        const response = await fetch(url, {
          method,
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': this.csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify(this.form)
        });

        const data = await response.json();

        if (response.ok) {
          this.successMessage = isUpdate
            ? 'Room updated successfully.'
            : 'Room added successfully.';

          this.form = { id: null, name: '', floor_id: '' };
          this.getRooms();
        } else {
          this.errorMessage = data.message || 'Something went wrong.';
        }
      } catch (error) {
        this.errorMessage = 'Error submitting form.';
        console.error(error);
      } finally {
        setTimeout(() => {
          this.successMessage = '';
          this.errorMessage = '';
        }, 4000);
      }
    },
    editRoom(room) {
      this.form = {
        id: room.id,
        name: room.name,
        floor_id: room.floor_id
      };
      window.scrollTo(0, 0); // optional: bring form into view
    },
    async deleteRoom(id) {
      if (!confirm('Are you sure you want to delete this room?')) return;

      try {
        const response = await fetch(`${window.baseUrl}/delete-room/${id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': this.csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        const data = await response.json();

        if (response.ok) {
          this.successMessage = data.message || 'Room deleted.';
          this.getRooms(); // Refresh list
        } else {
          this.errorMessage = data.message || 'Delete failed.';
        }
      } catch (error) {
        this.errorMessage = 'Error deleting room.';
        console.error(error);
      } finally {
        setTimeout(() => {
          this.successMessage = '';
          this.errorMessage = '';
        }, 4000);
      }
    },

    // Reset form method
    resetForm() {
      this.form = {
        id: null,
        name: '',
        floor_id: ''
      };
      this.successMessage = '';
      this.errorMessage = '';
      window.scrollTo(0, 0);
    }
  }
};
</script>

