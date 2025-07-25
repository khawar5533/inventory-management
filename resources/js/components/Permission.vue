<template>
  <div class="col-12 col-xl-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">Add User Permission</h5>
        <h6 class="card-subtitle text-muted">Add  permissions only</h6>
      </div>
      <div class="card-body">
        <!-- Alerts -->
        <div v-if="successMessage" class="alert alert-success text-center p-2 mb-3">
          {{ successMessage }}
        </div>
        <div v-if="errorMessage" class="alert alert-danger text-center p-2 mb-3">
          {{ errorMessage }}
        </div>

        <!-- Permission Form -->
        <form @submit.prevent="submitPermsnForm">
          <!-- User Permissions -->
          <div v-for="(group, index) in permissionGroups" :key="index" class="mb-4">
            <div class="card shadow-sm">
              <div class="card-header bg-light">
                <strong>{{ group.title }}</strong>
              </div>
              <div class="card-body row">
                <div v-for="(perm, pIndex) in group.permissions" :key="pIndex" class="col-md-4 mb-2">
                  <div class="form-check">
                    <input class="form-check-input"
                           type="checkbox"
                           :id="perm.value"
                           :value="perm.value"
                           v-model="selectedPermissions">
                    <label class="form-check-label" :for="perm.value">
                      {{ perm.label }}
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary" @click="createPermissions" >Submit Permissions</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Permission',
  data() {
    return {
      successMessage: '',
      errorMessage: '',
      selectedPermissions: [],
      permissionGroups: [
        {
          title: 'User',
          permissions: [
            { value: 'user_create', label: 'User Create' },
            { value: 'user_view', label: 'User View' },
            { value: 'user_edit', label: 'User Edit' },
            { value: 'user_delete', label: 'User Delete' },
            { value: 'user_assign_role', label: 'User Assign Role' },
            { value: 'user_assign_permission', label: 'User Assign Permission' },
          ]
        },
        {
          title: 'Product & Inventory',
          permissions: [
            { value: 'product_create', label: 'Product Create' },
            { value: 'product_view', label: 'Product View' },
            { value: 'product_edit', label: 'Product Edit' },
            { value: 'product_delete', label: 'Product Delete' },
            { value: 'product_lot_tracking', label: 'Product Lot Tracking' },
            { value: 'product_expiry_tracking', label: 'Product Expiry Tracking' },
            { value: 'product_import_csv', label: 'Product Import CSV' },
            { value: 'product_export_csv', label: 'Product Export CSV' },
          ]
        },
        {
          title: 'Medicine / Manufacturing',
          permissions: [
            { value: 'medicine_create', label: 'Medicine Create' },
            { value: 'medicine_view', label: 'Medicine View' },
            { value: 'medicine_edit', label: 'Medicine Edit' },
            { value: 'medicine_delete', label: 'Medicine Delete' },
            { value: 'batch_create', label: 'Batch Create' },
            { value: 'batch_edit', label: 'Batch Edit' },
            { value: 'batch_expiry_check', label: 'Batch Expiry Check' },
            { value: 'quality_control_record', label: 'Quality Control Record' },
          ]
        },
        {
          title: 'Warehouse',
          permissions: [
            { value: 'warehouse_create', label: 'Warehouse Create' },
            { value: 'warehouse_view', label: 'Warehouse View' },
            { value: 'warehouse_edit', label: 'Warehouse Edit' },
            { value: 'warehouse_delete', label: 'Warehouse Delete' },
            { value: 'rack_assign', label: 'Rack Assign' },
            { value: 'location_manage', label: 'Location Manage' },
            { value: 'stock_transfer', label: 'Stock Transfer' },
            { value: 'stock_reconcile', label: 'Stock Reconcile' },
          ]
        },
        {
          title: 'Purchase & Vendor',
          permissions: [
            { value: 'purchase_order_create', label: 'Purchase Order Create' },
            { value: 'purchase_order_view', label: 'Purchase Order View' },
            { value: 'purchase_order_edit', label: 'Purchase Order Edit' },
            { value: 'purchase_order_delete', label: 'Purchase Order Delete' },
            { value: 'vendor_create', label: 'Vendor Create' },
            { value: 'vendor_view', label: 'Vendor View' },
            { value: 'vendor_edit', label: 'Vendor Edit' },
            { value: 'vendor_delete', label: 'Vendor Delete' },
          ]
        },
        {
          title: 'Stock Handling',
          permissions: [
            { value: 'stock_receive', label: 'Stock Receive' },
            { value: 'stock_issue', label: 'Stock Issue' },
            { value: 'stock_return', label: 'Stock Return' },
            { value: 'stock_adjust', label: 'Stock Adjust' },
            { value: 'stock_audit', label: 'Stock Audit' },
          ]
        },
        {
          title: 'Sales',
          permissions: [
            { value: 'sales_order_create', label: 'Sales Order Create' },
            { value: 'sales_order_view', label: 'Sales Order View' },
            { value: 'sales_order_process', label: 'Sales Order Process' },
            { value: 'invoice_generate', label: 'Invoice Generate' },
          ]
        },
        {
          title: 'Reports & Audit',
          permissions: [
            { value: 'report_view', label: 'Report View' },
            { value: 'report_export', label: 'Report Export' },
            { value: 'audit_log_view', label: 'Audit Log View' },
          ]
        },
        {
          title: 'System Settings',
          permissions: [
            { value: 'settings_manage', label: 'Settings Manage' },
            { value: 'permission_manage', label: 'Permission Manage' },
            { value: 'role_manage', label: 'Role Manage' },
            { value: 'backup_create', label: 'Backup Create' },
            { value: 'logs_view', label: 'Logs View' },
          ]
        }
      ]
    };
  },
methods: {
  createPermissions() {
    fetch(`${window.baseUrl}/create-permissions`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        permissions: this.selectedPermissions
      })
    })
    .then(response => {
      if (!response.ok) {
        return response.json().then(data => {
          throw new Error(data.message || 'Failed to create permissions.');
        });
      }
      return response.json();
    })
    .then(data => {
      this.successMessage = data.message || 'Permissions created successfully.';
      this.errorMessage = '';
      
      //  Clear selected checkboxes
      this.selectedPermissions = [];

      //  Scroll to top
      window.scrollTo({ top: 0, behavior: 'smooth' });

      //  Auto-hide success
      setTimeout(() => {
        this.successMessage = '';
      }, 3000);
    })
    .catch(error => {
      this.errorMessage = error.message || 'An error occurred.';
      this.successMessage = '';

      // Scroll to top
      window.scrollTo({ top: 0, behavior: 'smooth' });

      // Auto-hide error
      setTimeout(() => {
        this.errorMessage = '';
      }, 3000);
    });
  }
}




};
</script>
