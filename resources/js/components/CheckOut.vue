<template>
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">My Orders</h5>
        </div>
        <div class="card-body">
          <table class="table">
            <thead>
              <tr>
                <th>Order #</th>
                <th>Lot ID</th>
                <th>Product Name</th>
                <th>Ordered Qty</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <template v-for="(items, orderId) in groupedOrders" :key="orderId">
                <tr v-for="(item, index) in items" :key="item.order_id + '-' + item.lot_id">
                  <!-- Show order_number only on the first row of each group -->
                  <td v-if="index === 0" :rowspan="items.length">{{ item.order_number }}</td>
                  <td>{{ item.lot_id }}</td>
                  <td>{{ item.product_name }}</td>
                  <td>{{ item.ordered_quantity }}</td>
                  <td>{{ item.unit_price }}</td>
                  <td>{{ item.subtotal }}</td>
                  <!-- Show checkout button only on the first row -->
                  <td v-if="index === 0" :rowspan="items.length">
                    <button @click="checkout(item.order_id)" class="btn btn-primary">
                      Checkout
                    </button>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "CheckOut",
  data() {
    return {
      orders: []
    };
  },
  computed: {
    groupedOrders() {
      return this.orders.reduce((groups, order) => {
        if (!groups[order.order_id]) {
          groups[order.order_id] = [];
        }
        groups[order.order_id].push(order);
        return groups;
      }, {});
    }
  },
  async mounted() {
    const res = await fetch(`${window.baseUrl}/orders`);
    this.orders = await res.json();
  },
  methods: {
    async checkout(orderId) {
      if (confirm("Are you sure you want to checkout this order?")) {
        const res = await fetch(`${window.baseUrl}/orders/${orderId}/checkout`, {
          method: "POST",
          headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
          }
        });
        const data = await res.json();
        alert(data.message);
        this.mounted(); // reload orders
      }
    }
  }
};
</script>
