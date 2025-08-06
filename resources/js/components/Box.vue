<template>
  <div class="row">
    <div class="col-12">
      <!-- Alert Messages -->
    <div v-if="successMessage" class="alert alert-success p-3">
        {{ successMessage }}
    </div>

    <div v-if="errorMessage" class="alert alert-danger p-3">
        {{ errorMessage }}
    </div>

      <!-- Box Form -->
      <div class="card mb-4">
        <div class="card-header"><h5>{{ form.id ? 'Edit' : 'Add' }} Box</h5></div>
        <div class="card-body">
          <form @submit.prevent="submitForm">
            <div class="mb-3">
              <label for="rack_id" class="form-label">Select Rack</label>
              <select v-model="form.rack_id" class="form-select" required>
                <option value="" disabled>Select Rack</option>
                <option v-for="rack in racks" :key="rack.id" :value="rack.id">
                  {{ rack.label }}
                </option>
              </select>
            </div>
            <div class="mb-3">
              <label for="name" class="form-label">Box Name</label>
              <input v-model="form.label" type="text" class="form-control" placeholder="Enter Box Name" required>
            </div>
            <button type="submit" class="btn btn-primary">{{ form.id ? 'Update' : 'Add' }} Box</button>
            <button v-if="form.id" type="button" @click="resetForm" class="btn btn-primary ms-2">Cancel</button>
          </form>
        </div>
      </div>

      <!-- Box List Table -->
      <div class="card">
        <div class="card-header"><h5>Box List</h5></div>
        <div class="card-body table-responsive">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>#</th>
                <th>Box Name</th>
                <th>Rack Name</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(box, index) in boxes" :key="box.id">
                <td>{{ index + 1 }}</td>
                <td>{{ box.label }}</td>
                <td>{{ box.rack?.label }}</td>
                <td>
                <a href="#" @click.prevent="editBox(box)">
                  <i class="fas fa-pen"></i>
                </a>
                <a href="#" @click.prevent="deleteBox(box.id)">
                  <i class="fas fa-trash text-danger ms-2"></i>
                </a>
                </td>
              </tr>
              <tr v-if="boxes.length === 0">
                <td colspan="4" class="text-center">No boxes found</td>
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
  name: 'AddBox',
  data() {
    return {
      form: {
        label: '',
        rack_id: '',
      },
      boxes: [],
      racks: [],
      successMessage: '',
      errorMessage: '',
    };
  },
  mounted() {
    this.getBoxes();
    this.getRacks();
  },
  methods: {
    async getBoxes() {
      try {
        const response = await fetch(`${window.baseUrl}/box-list`);
        const data = await response.json();
        this.boxes = data;
      } catch (error) {
        this.errorMessage = 'Failed to load boxes.';
        this.clearAlertAfterDelay();
      }
    },
    async getRacks() {
      try {
        const response = await fetch(`${window.baseUrl}/rack-list`);
        const data = await response.json();
        this.racks = data;
      } catch (error) {
        this.errorMessage = 'Failed to load racks.';
        this.clearAlertAfterDelay();
      }
    },
   async submitForm() {
    const isUpdate = !!this.form.id;
    const url = isUpdate
        ? `${window.baseUrl}/update-box/${this.form.id}`
        : `${window.baseUrl}/add-box`;
    const method = 'POST'; //  Always use POST

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    try {
        const response = await fetch(url, {
        method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(this.form),
        });

        const result = await response.json();

        if (!response.ok) throw new Error(result.message || 'Request failed');

        this.successMessage = result.message || (isUpdate ? 'Box updated.' : 'Box added.');
        this.resetForm();
        this.getBoxes();
    } catch (error) {
        this.errorMessage = error.message;
    } finally {
         this.clearAlertAfterDelay();
    }
    },
    editBox(box) {
      this.form = { ...box, rack_id: box.rack_id };
    },
    async deleteBox(id) {
    if (!confirm('Are you sure you want to delete this box?')) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    try {
        const response = await fetch(`${window.baseUrl}/delete-box/${id}`, {
        method: 'POST', // changed from DELETE to POST
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json'
        }
        });

        const result = await response.json();

        if (!response.ok) throw new Error(result.message || 'Delete failed');

        this.successMessage = result.message || 'Box deleted.';
        this.getBoxes();
    } catch (error) {
        this.errorMessage = error.message;
    } finally {
        this.clearAlertAfterDelay();
    }
    },
    resetForm() {
      this.form = {
        label: '',
        rack_id: '',
      };
    },
    clearAlertAfterDelay() {
      setTimeout(() => {
        this.successMessage = '';
        this.errorMessage = '';
      }, 3000);
    }
  }
};
</script>

