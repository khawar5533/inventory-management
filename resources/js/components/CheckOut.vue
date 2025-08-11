<template>
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">My Orders</h5>
        </div>

        <div class="card-body">
          <div v-if="loading" class="mb-3">Loading orders...</div>
          <div v-else-if="orders.length === 0" class="alert alert-info">
            No orders found.
          </div>

          <table v-else class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Order #</th>
                <th>Lot ID</th>
                <th>Product Name</th>
                <th>Ordered Qty</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
                <th>Total</th>
                <th>Action</th>
              </tr>
            </thead>

            <tbody>
              <!-- iterate groups -->
              <template v-for="(group, orderId) in groupedOrders" :key="orderId">
                <tr
                  v-for="(item, idx) in group.items"
                  :key="item.order_id + '-' + item.lot_id"
                >
                  <!-- order number only once per group -->
                  <td v-if="idx === 0" :rowspan="group.items.length">
                    {{ group.order_number }}
                  </td>

                  <td>{{ item.lot_id }}</td>
                  <td>{{ item.product_name || item.name || '—' }}</td>
                  <td>{{ item.ordered_quantity }}</td>
                  <td>{{ formatPrice(item.unit_price) }}</td>
                  <td>{{ formatPrice(item.subtotal) }}</td>

                  <!-- total only once per group -->
                  <td v-if="idx === 0" :rowspan="group.items.length">
                    {{ formatPrice(group.total) }}
                  </td>

                  <!-- action only once per group -->
                  <td v-if="idx === 0" :rowspan="group.items.length">
                    <button
                      v-if="group.status !== 'completed'"
                      :disabled="processing[orderId]"
                      @click="checkoutOrder(orderId)"
                      class="btn btn-primary btn-sm"
                    >
                      <span v-if="processing[orderId]">Processing…</span>
                      <span v-else>Checkout</span>
                    </button>

                    <button
                      v-else
                      @click="printOrder(orderId)"
                      class="btn btn-success btn-sm"
                    >
                      Print Order
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
      orders: [],
      loading: false,
      processing: {}, // { [orderId]: true/false }
    };
  },
  computed: {
    // returns an object: { orderId: { items: [...], total: 123.45, status: 'completed', order_number: 'PO-...' } }
    groupedOrders() {
      const groups = {};
      for (const o of this.orders) {
        const id = String(o.order_id);
        if (!groups[id]) {
          groups[id] = {
            items: [],
            total: 0,
            status: o.status ?? o.order_status ?? null,
            order_number: o.order_number ?? o.order_no ?? "",
          };
        }
        groups[id].items.push(o);
        const subtotal = parseFloat(o.subtotal || 0);
        groups[id].total += isNaN(subtotal) ? 0 : subtotal;
      }
      return groups;
    },
  },
  methods: {
    buildUrl(path) {
      // If you have window.baseUrl set in blade, it'll be used,
      // otherwise it falls back to relative path.
      const base = (window.baseUrl || "").replace(/\/$/, "");
      return `${base}${path.startsWith("/") ? path : "/" + path}`;
    },

    formatPrice(val) {
      const n = parseFloat(val || 0);
      if (isNaN(n)) return "0.00";
      return n.toFixed(2);
    },

    async fetchOrders() {
      this.loading = true;
      try {
        // adjust path if your endpoint is /user-orders or /orders - change here
        const url = this.buildUrl("/orders"); // <-- change to '/user-orders' if needed
        const res = await fetch(url, {
          headers: { Accept: "application/json" },
          credentials: "same-origin",
        });
        if (!res.ok) {
          // attempt alternate route if /orders returned 404
          if (res.status === 404) {
            const alt = this.buildUrl("/user-orders");
            const r2 = await fetch(alt, { headers: { Accept: "application/json" }, credentials: "same-origin" });
            if (!r2.ok) throw new Error(`Failed to load orders (${r2.status})`);
            this.orders = await r2.json();
          } else {
            throw new Error(`Failed to load orders (${res.status})`);
          }
        } else {
          this.orders = await res.json();
        }
      } catch (err) {
        console.error("fetchOrders error:", err);
        alert("Error loading orders. Check console for details.");
      } finally {
        this.loading = false;
      }
    },

    async checkoutOrder(orderId) {
      if (!confirm("Are you sure you want to checkout this order?")) return;

      // mark processing
      this.$set ? this.$set(this.processing, orderId, true) : (this.processing = { ...this.processing, [orderId]: true });

      try {
        // adjust path if necessary
        const url = this.buildUrl(`/orders/${orderId}/checkout`);
        const res = await fetch(url, {
          method: "POST",
          headers: {
            "Accept": "application/json",
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": (document.querySelector('meta[name="csrf-token"]') || {}).content || "",
          },
          credentials: "same-origin",
          body: JSON.stringify({}), // send empty body if your endpoint expects none
        });

        if (!res.ok) {
          // try alternate endpoint if 404
          if (res.status === 404) {
            const alt = this.buildUrl(`/checkout-order/${orderId}`);
            const r2 = await fetch(alt, {
              method: "POST",
              headers: {
                "Accept": "application/json",
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": (document.querySelector('meta[name="csrf-token"]') || {}).content || "",
              },
              credentials: "same-origin",
              body: JSON.stringify({}),
            });
            if (!r2.ok) throw new Error(`Checkout failed (${r2.status})`);
            const data2 = await r2.json();
            alert(data2.message || "Checked out");
            await this.fetchOrders();
            return;
          }

          const text = await res.text();
          throw new Error(`Checkout failed: ${res.status} ${text}`);
        }

        const data = await res.json();
        alert(data.message || "Order checked out successfully");
        await this.fetchOrders();
      } catch (err) {
        console.error("checkoutOrder error:", err);
        alert("Checkout failed. See console for details.");
      } finally {
        this.processing = { ...this.processing, [orderId]: false };
      }
    },

    printOrder(orderId) {
      // adjust print path if needed
     const url = this.buildUrl(`/orders/${orderId}/print`);
  window.open(url, "_blank");
    },
  },

  mounted() {
    this.fetchOrders();
  },
};
</script>


