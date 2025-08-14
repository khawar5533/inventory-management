<template>
  <nav id="sidebar" class="sidebar">
    <a class="sidebar-brand" href="index.html">
      <svg>
        <use xlink:href="#ion-ios-pulse-strong"></use>
      </svg>
      Spark
    </a>

    <div class="sidebar-content">
      <div class="sidebar-user">
        <img
          :src="profileImage"
          alt="User avatar"
          class="rounded-circle mb-2"
          style="width: 60px; height: 60px; object-fit: cover;"
        />
        <div>{{ formattedUserName }}</div>
        <div>{{ formattedUserRole }}</div>
      </div>

      <ul class="sidebar-nav">
        <li class="sidebar-header">Main</li>

        <li class="sidebar-item active">
          <a
            data-bs-target="#dashboards"
            data-bs-toggle="collapse"
            class="sidebar-link"
          >
            <i class="align-middle me-2 fas fa-fw fa-home"></i>
            <span class="align-middle">Dashboards</span>
          </a>
          <ul
            id="dashboards"
            class="sidebar-dropdown list-unstyled collapse show"
            data-bs-parent="#sidebar"
          >
            <li
              class="sidebar-link"
              @click="$emit('change-component', 'Content')"
            >
              Dashboard
            </li>
          </ul>
        </li>

        <li class="sidebar-item">
          <a
            data-bs-target="#wharehouse"
            data-bs-toggle="collapse"
            class="sidebar-link collapsed"
          >
            <i class="align-middle me-2 fas fa-warehouse fa-sign-in-alt"></i>
            <span class="align-middle">WhareHouse</span>
          </a>
          <ul
            id="wharehouse"
            class="sidebar-dropdown list-unstyled collapse"
            data-bs-parent="#sidebar"
          >
            <li class="sidebar-link" @click="$emit('change-component', 'Add-Location')">Add Location</li>
            <li class="sidebar-link" @click="$emit('change-component', 'Floor')">Add Floor</li>
            <li class="sidebar-link" @click="$emit('change-component', 'Room')">Add Room</li>
            <li class="sidebar-link" @click="$emit('change-component', 'Rack')">Add Rack</li>
            <li class="sidebar-link" @click="$emit('change-component', 'Box')">Add Box</li>
            <li class="sidebar-link" @click="$emit('change-component', 'Product')">Add Product</li>
            <li class="sidebar-link" @click="$emit('change-component', 'Category')">Add Category</li>
          </ul>
        </li>

        <!-- Show Auth tab only if user is admin -->
        <li v-if="isAdmin" class="sidebar-item">
          <a
            data-bs-target="#auth"
            data-bs-toggle="collapse"
            class="sidebar-link collapsed"
          >
            <i class="align-middle me-2 fas fa-fw fa-sign-in-alt"></i>
            <span class="align-middle">Auth</span>
          </a>
          <ul
            id="auth"
            class="sidebar-dropdown list-unstyled collapse"
            data-bs-parent="#sidebar"
          >
            <li class="sidebar-link" @click="$emit('change-component', 'Register')">Register</li>
            <li class="sidebar-link" @click="$emit('change-component', 'Role')">AddRole</li>
            <li class="sidebar-link" @click="$emit('change-component', 'UserRole')">Assign Role</li>
            <li class="sidebar-link" @click="$emit('change-component', 'User-Permission')">Assign Permission</li>
            <li class="sidebar-link" @click="$emit('change-component', 'Permission')">AddPermission</li>
            <li class="sidebar-link" @click="$emit('change-component', 'Show-Permission')">Show Permission</li>
          </ul>
        </li>

        <li class="sidebar-header">Elements</li>

        <li class="sidebar-item">
          <a
            data-bs-target="#mang"
            data-bs-toggle="collapse"
            class="sidebar-link collapsed"
          >
            <i class="align-middle me-2 fas fa-fw fa-briefcase"></i>
            <span class="align-middle">Management</span>
          </a>
          <ul
            id="mang"
            class="sidebar-dropdown list-unstyled collapse"
            data-bs-parent="#sidebar"
          >
            <li v-if="isAdmin"  class="sidebar-link" @click="$emit('change-component', 'ProductLot')">Product Lot</li>
            <li class="sidebar-link" @click="$emit('change-component', 'Get-Items')">Available Items</li>
            <li class="sidebar-link" @click="$emit('change-component', 'CheckOut')">Confirm Checkout</li>
          </ul>
        </li>

        <li class="sidebar-item">
          <a
            data-bs-target="#documentation"
            data-bs-toggle="collapse"
            class="sidebar-link collapsed"
          >
            <i class="align-middle me-2 fas fa-fw fa-book"></i>
            <span class="align-middle">Documentation</span>
          </a>
          <ul
            id="documentation"
            class="sidebar-dropdown list-unstyled collapse"
            data-bs-parent="#sidebar"
          >
            <li class="sidebar-item">
              <a class="sidebar-link" href="docs-getting-started.html">Getting Started</a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="docs-plugins.html">Plugins</a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="docs-changelog.html">Changelog</a>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </nav>
</template>

<script>
export default {
  name: "Sidebar",
  data() {
    return {
      roles: [],
      userName: "User",
      userRole: "User",
      profileImage: this.assetPath("admin/assets/img/avatars/avatar.jpg") // default fallback
    };
  },
  computed: {
    isAdmin() {
      return this.roles.includes("admin");
    },
    formattedUserRole() {
      if (!this.userRole) return "Role";
      return this.userRole.charAt(0).toUpperCase() + this.userRole.slice(1).toLowerCase();
    },
	formattedUserName() {
    if (!this.userName) return 'User';
    // Capitalize first letter of each word
    return this.userName
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
      .join(' ');
  },
  },
  methods: {
    assetPath(path) {
      return `${window.baseUrl}/${path}`;
    },
    fetchUserRoles() {
      fetch(`${window.baseUrl}/user-roles`, {
        credentials: "same-origin",
        headers: {
          Accept: "application/json",
        },
      })
        .then((res) => {
          if (!res.ok) throw new Error("Failed to fetch user roles");
          return res.json();
        })
        .then((data) => {
          this.roles = data.roles || [];
          this.userName = data.userName || "User";
          this.userRole = this.roles.length > 0 ? this.roles[0] : "User";
          // dynamically set profile image if exists
          if (data.user_image) {
            this.profileImage = `${window.baseUrl}/storage/${data.user_image}`;
          }
        })
        .catch((err) => {
          console.error(err);
          this.roles = [];
          this.userName = "User";
          this.userRole = "User";
        });
    },
  },
  mounted() {
    this.fetchUserRoles();
  },
};
</script>
