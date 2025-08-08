<template>
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header"><h5 class="card-title mb-0">Manage Product Lots</h5></div>
        <div class="card-body">
          <div v-if="successMessage" class="alert alert-success p-2">{{ successMessage }}</div>
          <div v-if="errorMessage" class="alert alert-danger p-2">{{ errorMessage }}</div>

          <form @submit.prevent="submitForm">
            <div class="mb-3">
              <label>Product</label>
              <select v-model="form.product_id" class="form-control" required>
                <option value="">Select Product</option>
                <option v-for="product in products" :key="product.id" :value="product.id">{{ product.name }}</option>
              </select>
            </div>

            <div class="mb-3">
              <label>Lot Number (e.g. 1234)</label>
              <input type="text" v-model="form.lot_number" class="form-control" :disabled="!!form.id" required />
            </div>

            <div class="mb-3">
              <label>Expiration Date</label>
              <input type="date" v-model="form.expiration_date" class="form-control" />
            </div>

            <div class="mb-3">
              <label>Condition</label>
              <select v-model="form.condition" class="form-control" required>
                <option value="">Select Condition</option>
                <option value="new-sterile">New - sterile</option>
                <option value="open-box">Open box</option>
              </select>
            </div>

            <div class="mb-3">
              <label>Quantity</label>
              <input type="number" v-model="form.quantity" class="form-control" :disabled="!!form.id" required min="1" />
            </div>

            <div class="mb-3">
              <label>Box Location</label>
              <select v-model="form.box_id" class="form-control" required>
                <option value="">Select Box</option>
                <option v-for="box in boxes" :key="box.id" :value="box.id">{{ box.label }}</option>
              </select>
            </div>

            <button type="submit" class="btn btn-primary">
              {{ form.id ? 'Update' : 'Add' }} Product Lot
            </button>
            <button v-if="form.id" type="button" @click="resetForm" class="btn btn-primary ms-2">Cancel</button>
          </form>
        </div>
      </div>

      <div class="card mt-4">
        <div class="card-header"><h5 class="card-title">Saved Lots</h5></div>
        <div class="card-body">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Product</th>
                <th>Formatted Lot #</th>
                <th>Expiration</th>
                <th>Condition</th>
                <th>Quantity</th>
                <th>Box</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="lots.length === 0">
                <td colspan="7" class="text-center">No lots added yet.</td>
              </tr>
              <tr v-for="lot in lots" :key="lot.id">
                <td>{{ lot.product?.name }}</td>
                <td>{{ lot.lot_number }}</td>
                <td>{{ lot.expiration_date }}</td>
                <td>{{ lot.condition }}</td>
                <td>{{ lot.quantity }}</td>
                <td>{{ lot.box?.label }}</td>
                <td>
                  <a href="#" @click.prevent="editLot(lot)"><i class="fas fa-pen"></i></a>
                  <a href="#" @click.prevent="deleteLot(lot.id)"><i class="fas fa-trash text-danger ms-2"></i></a>
                </td>
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
  name: 'ProductLot',
  data() {
    return {
      lots: [],
      products: [],
      boxes: [],
      form: {
        id: null,
        product_id: '',
        lot_number: '',
        expiration_date: '',
        condition: '',
        quantity: '',
        box_id: ''
      },
      successMessage: '',
      errorMessage: ''
    };
  },
  mounted() {
    this.fetchProducts();
    this.fetchBoxes();
    this.fetchData();
  },
  methods: {
    async fetchData() {
      try {
        const res = await fetch(`${window.baseUrl}/lot-list`);
        this.lots = await res.json();
      } catch {
        this.showError('Failed to load lots.');
      }
    },
    async fetchProducts() {
      try {
        const res = await fetch(`${window.baseUrl}/item-list`);
        this.products = await res.json();
      } catch {
        this.showError('Failed to load products.');
      }
    },
    async fetchBoxes() {
      try {
        const res = await fetch(`${window.baseUrl}/box-list`);
        this.boxes = await res.json();
      } catch {
        this.showError('Failed to load boxes.');
      }
    },
   async submitForm() {
    this.successMessage = '';
    this.errorMessage = '';

    const isUpdate = !!this.form.id;
    const url = isUpdate
      ? `${window.baseUrl}/update-lot/${this.form.id}`
      : `${window.baseUrl}/add-lot`;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const payload = {
      ...this.form,
      lot_condition: this.form.condition // ensure mapping matches backend
    };

    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'Accept': 'application/json', // 👈 forces Laravel to return JSON
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf
        },
        body: JSON.stringify(payload)
      });

      if (res.status === 422) {
        // Laravel validation failed
        const errorData = await res.json();
        const errorMessage = errorData?.message || 'Validation failed.';
        const firstError = errorData?.errors ? Object.values(errorData.errors)[0][0] : errorMessage;
        this.showError(firstError);
        return;
      }

      if (!res.ok) {
        const errorData = await res.json();
        throw new Error(errorData?.message || 'Server error');
      }

      const result = await res.json();
      this.successMessage = result.message || 'Saved successfully.';
      this.resetForm();
      await this.fetchData();
      this.clearMessages();
    } catch (err) {
      this.showError(err.message || 'Unexpected error occurred.');
    }
  },
    editLot(lot) {
      this.form = {
        id: lot.id,
        product_id: lot.product_id,
        lot_number: lot.lot_number.split('-').pop(), // extract raw number
        expiration_date: lot.expiration_date,
        condition: lot.condition,
        quantity: lot.quantity,
        box_id: lot.box_id
      };
    },
    async deleteLot(id) {
      if (!confirm('Are you sure you want to delete this lot?')) return;

      const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

      try {
        const res = await fetch(`${window.baseUrl}/delete-lot/${id}`, {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': csrf }
        });

        if (!res.ok) throw new Error('Delete failed.');

        this.successMessage = 'Deleted successfully.';
        await this.fetchData();
        this.clearMessages();
      } catch (err) {
        this.showError(err.message);
      }
    },
    resetForm() {
      this.form = {
        id: null,
        product_id: '',
        lot_number: '',
        expiration_date: '',
        condition: '',
        quantity: '',
        box_id: ''
      };
    },
    showError(msg) {
      this.errorMessage = msg;
      setTimeout(() => (this.errorMessage = ''), 3000);
    },
    clearMessages() {
      setTimeout(() => {
        this.successMessage = '';
        this.errorMessage = '';
      }, 3000);
    }
  }
};
</script>
