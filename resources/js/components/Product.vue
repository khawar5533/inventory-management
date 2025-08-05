<template>
    	<div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Empty card</h5>
                    </div>
                    <div class="card-body">
                     
                    <!-- Alerts -->
                        <div v-if="successMessage" class="alert alert-success p-2">
                            {{ successMessage }}
                        </div>
                        <div v-if="errorMessage" class="alert alert-danger p-2">
                            {{ errorMessage }}
                        </div>

                        <!-- Form Card -->
                        <div class="col-12 col-xl-12">
                            <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Add Product</h5>
                                <h6 class="card-subtitle text-muted">Enter product details.</h6>
                            </div>
                            <div class="card-body">
                                <form @submit.prevent="submitForm">
                                <div class="mb-3">
                                    <label>Name</label>
                                    <input type="text" v-model="form.name" class="form-control" required />
                                </div>

                                <div class="mb-3">
                                    <label>Category</label>
                                    <select v-model="form.category_id" class="form-control">
                                    <option :value="null">None</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                        {{ cat.name }}
                                    </option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label>Reference Number</label>
                                    <input type="text" v-model="form.reference_number" class="form-control" />
                                </div>

                                <div class="mb-3">
                                    <label>RFID Code</label>
                                    <input type="text" v-model="form.rfid_code" class="form-control" />
                                </div>

                                <div class="mb-3">
                                    <label>Unit Description</label>
                                    <input type="text" v-model="form.unit_description" class="form-control" />
                                </div>

                                <div class="mb-3">
                                    <label>Price</label>
                                    <input type="number" step="0.01" v-model="form.price" class="form-control" />
                                </div>

                                <div class="mb-3">
                                <label>Weight</label>
                                <div class="d-flex gap-2">
                                  <input
                                    type="number"
                                    step="0.01"
                                    v-model="form.weight_value"
                                    class="form-control"
                                    placeholder="Enter weight"
                                  />
                                  <select v-model="form.weight_unit" class="form-select" style="max-width: 100px;">
                                    <option value="oz">oz</option>
                                    <option value="g">g</option>
                                    <option value="kg">kg</option>
                                    <option value="mg">mg</option>
                                  </select>
                                </div>
                              </div>
                                <div class="mb-3">
                                  <label>Dimensions (L × W × H)</label>
                                  <div class="d-flex gap-2">
                                    <input
                                      type="number"
                                      step="0.01"
                                      v-model="form.length"
                                      class="form-control"
                                      placeholder="Length"
                                    />
                                    <input
                                      type="number"
                                      step="0.01"
                                      v-model="form.width"
                                      class="form-control"
                                      placeholder="Width"
                                    />
                                    <input
                                      type="number"
                                      step="0.01"
                                      v-model="form.height"
                                      class="form-control"
                                      placeholder="Height"
                                    />
                                  </div>
                                </div>
                                <div class="mb-3">
                                    <label>Comment</label>
                                    <textarea v-model="form.comment" class="form-control"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label>Reorder Threshold</label>
                                    <input type="number" v-model="form.reorder_threshold" class="form-control" />
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    {{ form.id ? 'Update' : 'Add' }} Product
                                </button>
                                </form>
                            </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
		</div>
         <!-- Product List Card -->
        <div class="col-12 col-xl-12 mt-4">
            <div class="card">
            <div class="card-header">
                <h5 class="card-title">Saved Products</h5>
                <h6 class="card-subtitle text-muted">List of all added products.</h6>
            </div>
            <div class="card-body">
                <table class="table table-bordered mt-3">
                <thead>
              <tr>
                <th>Name</th>
                <th>Category</th>
                <th>Reference Number</th>
                <th>RFID Code</th>
                <th>Unit Description</th>
                <th>Price</th>
                <th>Weight</th>
                <th>Dimensions</th>
                <th>Comment</th>
                <th>Reorder Threshold</th>
                <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="products.length === 0">
              <td colspan="11" class="text-center">No data available</td>
            </tr>
            <tr v-else v-for="product in products" :key="product.id">
              <td>{{ product.name }}</td>
              <td>{{ product.category?.name || '—' }}</td>
              <td>{{ product.reference_number }}</td>
              <td>{{ product.rfid_code }}</td>
              <td>{{ product.unit_description }}</td>
              <td>{{ product.price }}</td>
              <td>
                {{ product.weight_value }} {{ product.weight_unit }}
              </td>
              <td>
                {{ product.length }} × {{ product.width }} × {{ product.height }}
              </td>
              <td>{{ product.comment }}</td>
              <td>{{ product.reorder_threshold }}</td>
              <td>
                <a href="#" @click.prevent="editProduct(product)">
                  <i class="fas fa-pen"></i>
                </a>
                <a href="#" @click.prevent="deleteProduct(product.id)">
                  <i class="fas fa-trash text-danger ms-2"></i>
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
  name: 'Product',
  data() {
    return {
      products: [],
      categories: [],
      form: {
        id: null,
        name: '',
        category_id: null,
        reference_number: '',
        rfid_code: '',
        unit_description: '',
        price: '',
        weight_oz: '',
        dimensions: '',
        comment: '',
        reorder_threshold: ''
      },
      successMessage: '',
      errorMessage: ''
    };
  },
  mounted() {
    this.fetchProducts();
    this.fetchCategories();
  },
  methods: {
    async fetchProducts() {
      try {
        const res = await fetch(`${window.baseUrl}/product-list`);
        this.products = await res.json();
      } catch (err) {
        this.errorMessage = 'Failed to load products.';
      }
    },
    async fetchCategories() {
      try {
        const res = await fetch(`${window.baseUrl}/category-list`);
        this.categories = await res.json();
      } catch (err) {
        this.errorMessage = 'Failed to load categories.';
      }
    },
    async submitForm() {
      const isUpdate = !!this.form.id;
      const url = isUpdate
        ? `${window.baseUrl}/update-product/${this.form.id}`
        : `${window.baseUrl}/add-product`;

      const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

      try {
        const res = await fetch(url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf
          },
          body: JSON.stringify(this.form)
        });

        const result = await res.json();

        if (!res.ok) throw new Error(result.message || 'Error saving product');

        this.successMessage = result.message || (isUpdate ? 'Product updated!' : 'Product added!');
        this.errorMessage = '';

        this.resetForm();
        await this.fetchProducts();

        setTimeout(() => (this.successMessage = ''), 3000);
      } catch (err) {
        this.errorMessage = err.message || 'Failed to save product';
        this.successMessage = '';
        setTimeout(() => (this.errorMessage = ''), 3000);
      }
    },
    editProduct(product) {
      this.form = { ...product };
    },
    async deleteProduct(id) {
      if (!confirm('Are you sure you want to delete this product?')) return;

      const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

      try {
        const res = await fetch(`${window.baseUrl}/delete-product/${id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrf
          }
        });

        if (!res.ok) throw new Error('Failed to delete');

        this.successMessage = 'Product deleted';
        await this.fetchProducts();
        setTimeout(() => (this.successMessage = ''), 3000);
      } catch (err) {
        this.errorMessage = err.message;
        this.successMessage = '';
        setTimeout(() => (this.errorMessage = ''), 3000);
      }
    },
   resetForm() {
  this.form = {
    id: null,
    name: '',
    category_id: null,
    reference_number: '',
    rfid_code: '',
    unit_description: '',
    price: '',
    weight_value: '',
    weight_unit: 'g',
    length: '',
    width: '',
    height: '',
    comment: '',
    reorder_threshold: ''
  };
}
  }
};
</script>
