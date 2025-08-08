<template>
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Create Purchase Order</h5>
        </div>
        <div class="card-body">
          <!-- Alerts -->
          <div v-if="successMessage" class="alert alert-success p-2">{{ successMessage }}</div>
          <div v-if="errorMessage" class="alert alert-danger p-2">{{ errorMessage }}</div>

          <form @submit.prevent="submitForm">
           <!-- Order Number (only visible in create mode) -->
            <div v-if="!isEdit">
              <label>Order Number</label>
              <input type="text" v-model="form.order_number" class="form-control" />
            </div>

            <!-- Customer Name -->
            <div class="mb-3">
              <label class="form-label">Customer Name</label>
              <input type="text" v-model="form.customer_name" class="form-control" required />
            </div>

            <!-- Status -->
            <div class="mb-3">
              <label class="form-label">Status</label>
              <select v-model="form.status" class="form-select" required>
                <option value="pending">Pending</option>
                <option value="partial">Partial</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>

            <!-- Notes -->
            <div class="mb-3">
              <label class="form-label">Notes</label>
              <textarea v-model="form.notes" class="form-control" rows="3"></textarea>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn btn-primary"> {{ isEdit ? 'Update Order' : 'Save Purchase Orders' }}</button>
                        <button v-if="form.id" type="button" @click="resetForm" class="btn btn-primary ms-2">Cancel</button>

          </form>
        </div>
      </div>

      <!-- Existing Orders Table -->
      <div class="card mt-4">
        <div class="card-header"><h5 class="card-title">
         
         </h5></div>
        <div class="card-body">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Status</th>
                <th>Notes</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="purchaseOrders.length === 0">
                <td colspan="5" class="text-center">No purchase orders found.</td>
              </tr>
              <tr v-for="order in purchaseOrders" :key="order.id">
                <td>{{ order.order_number }}</td>
                <td>{{ order.customer_name }}</td>
                <td>{{ order.status }}</td>
                <td>{{ order.notes }}</td>
                <td>
                  <a href="#" @click.prevent="editOrder(order)">
                    <i class="fas fa-pen"></i>
                  </a>
                  <a href="#" @click.prevent="deleteOrder(order.id)">
                    <i class="fas fa-trash text-danger ms-2"></i>
                  </a>
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
  name: 'PurchaseOrder',

  data() {
    return {
      form: {
        id: null,
        order_number: '',
        customer_name: '',
        status: 'pending',
        notes: ''
      },
      purchaseOrders: [],
      successMessage: '',
      errorMessage: ''
    };
  },

  methods: {
    generateOrderNumber() {
      const prefix = 'PO';
      const date = new Date().toISOString().slice(0, 10).replace(/-/g, '');
      const random = Math.random().toString(36).substring(2, 8).toUpperCase();
      return `${prefix}-${date}-${random}`;
    },

    async fetchOrders() {
      try {
        const response = await fetch(`${window.baseUrl}/purchase-orders`);
        const result = await response.json();

        if (response.ok) {
          this.purchaseOrders = result.data || [];
        } else {
          console.error('Failed to fetch orders');
        }
      } catch (err) {
        console.error('Error fetching orders', err);
      }
    },

async submitForm() {
  this.successMessage = '';
  this.errorMessage = '';
 
  const url = this.form.id
    ? `${window.baseUrl}/purchase-orders/${this.form.id}`
    : `${window.baseUrl}/purchase-orders`;

  const method = 'POST'; // use POST for both create and update

  try {
    const response = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'  // 👈 Force JSON response
      },
      body: JSON.stringify(this.form)
    });

    const result = await response.json();

    if (response.ok) {
      this.successMessage = result.message || (this.form.id
        ? 'Purchase order updated successfully!'
        : 'Purchase order created successfully!');
      this.resetForm();
      this.fetchOrders(); // reload orders
    } else {
      this.errorMessage = result.message || 'Something went wrong.';
    }
  } catch (error) {
    console.error('Request failed:', error);
    this.errorMessage = 'Network error or invalid server response.';
  }

  setTimeout(() => {
    this.successMessage = '';
    this.errorMessage = '';
  }, 3000);
},

   editOrder(order) {
    this.isEdit = true;
    this.form = {
      id: order.id,
      customer_name: order.customer_name,
      status: order.status,
      notes: order.notes,
      order_number: order.order_number // optional, if only for display
    };
  },

    async deleteOrder(id) {
      if (!confirm('Are you sure you want to delete this order?')) return;

      try {
        const response = await fetch(`${window.baseUrl}/purchase-orders/${id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });

        const result = await response.json();

        if (response.ok) {
          this.successMessage = 'Purchase order deleted successfully!';
          this.fetchOrders();
        } else {
          this.errorMessage = result.message || 'Failed to delete order.';
        }
      } catch (error) {
        console.error(error);
        this.errorMessage = 'An error occurred while deleting.';
      }

      setTimeout(() => {
        this.successMessage = '';
        this.errorMessage = '';
      }, 3000);
    },

    resetForm() {
      this.form = {
        id: null,
        order_number: this.generateOrderNumber(),
        customer_name: '',
        status: 'pending',
        notes: ''
      };
    }
  },

  mounted() {
    this.form.order_number = this.generateOrderNumber();
    this.fetchOrders();
  }
};
</script>
