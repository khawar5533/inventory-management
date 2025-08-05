<template>
<!-- Form Card -->
 <div v-if="successMessage" class="alert alert-success p-2">
  {{ successMessage }}
</div>
<div v-if="errorMessage" class="alert alert-danger p-2">
  {{ errorMessage }}
</div>
     <div class="col-12 col-xl-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Add Category</h5>
          <h6 class="card-subtitle text-muted">Enter Category details.</h6>
        </div>
        <div class="card-body">
          <form @submit.prevent="submitForm">
            <div class="mb-3">
              <label>Name</label>
              <input type="text" v-model="form.name" class="form-control" required />
            </div>

            <div class="mb-3">
              <label>Parent Category (Optional)</label>
              <select v-model="form.parent_id" class="form-control">
                <option :value="null">None</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
            </div>

            <button type="submit" class="btn btn-primary">
              {{ form.id ? 'Update' : 'Add' }} Category
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Category List Card -->
    <div class="col-12 col-xl-12 mt-4">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Saved Categories</h5>
          <h6 class="card-subtitle text-muted">List of all added categories.</h6>
        </div>
        <div class="card-body">
          <table class="table table-bordered mt-3">
            <thead>
              <tr>
                <th>Name</th>
                <th>Parent</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="categories.length === 0">
                <td colspan="3" class="text-center">No data available</td>
              </tr>
              <tr v-else v-for="cat in categories" :key="cat.id">
                <td>{{ cat.name }}</td>
                <td>{{ cat.parent?.name || '—' }}</td>
                <td>
               <a href="#" @click.prevent="editCategory(cat)">
                  <i class="fas fa-pen"></i>
                </a>
                <a href="#" @click.prevent="deleteCategory(cat.id)">
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
  name: 'Category',
  data() {
    return {
      categories: [],
      form: {
        name: '',
        parent_id: null
      },
      successMessage: '',
      errorMessage: ''
    };
  },
  mounted() {
     this.fetchCategories();
  },
  methods: {
    async fetchCategories() {
      try {
        const res = await fetch(`${window.baseUrl}/category-list`);
        const data = await res.json();
        this.categories = data;
      } catch (err) {
        this.errorMessage = 'Failed to load categories.';
      }
    },
   async submitForm() {
    const isUpdate = !!this.form.id;
    const url = isUpdate
        ? `${window.baseUrl}/update-category/${this.form.id}`
        : `${window.baseUrl}/add-category`;

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

        if (!res.ok) {
        throw new Error(result.message || 'Error saving category');
        }

        this.successMessage = result.message || (isUpdate ? 'Category updated!' : 'Category added!');
        this.errorMessage = '';

        this.form = { id: null, name: '', parent_id: null };
        await this.fetchCategories();

        setTimeout(() => {
        this.successMessage = '';
        }, 3000);
    } catch (err) {
        this.errorMessage = err.message || 'Failed to save category';
        this.successMessage = '';

        setTimeout(() => {
        this.errorMessage = '';
        }, 3000);
    }
    },
    editCategory(cat) {
      this.form = {
        id: cat.id,
        name: cat.name,
        parent_id: cat.parent_id
      };
    },
    async deleteCategory(id) {
      if (!confirm('Are you sure you want to delete this category?')) return;

      const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

      try {
        const res = await fetch(`${window.baseUrl}/delete-category/${id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrf
          }
        });

        if (!res.ok) throw new Error('Failed to delete');

        this.successMessage = 'Category deleted';
        await this.fetchCategories();
        setTimeout(() => (this.successMessage = ''), 3000);
      } catch (err) {
        this.errorMessage = err.message;
        this.successMessage = '';
      }
    }
  }
};
</script>
