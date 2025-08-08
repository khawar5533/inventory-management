<template>
  <div class="row">
    <div class="col-12">
      <!-- Items Table -->
      <div class="card">
        <div class="card-header"><h5>Inventory Available Items</h5></div>
        <div class="card-body table-responsive">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>#</th>
                <th>Product</th>
                <th>Price</th>
                <th>Batch Number</th>
                <th>Quantity</th>
                <th>Condition</th>
                <th>Expiration</th>
                <th>Location</th>
                <th>Floor</th>
                <th>Room</th>
                <th>Rack</th>
                <th>Box</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(lot, index) in lots" :key="lot.id">
                <td>{{ index + 1 }}</td>
                <td>{{ lot.product_lot?.product?.name ?? 'N/A' }}</td>
                <td>{{ lot.product_lot?.product?.price ?? 'N/A' }}</td>
                <td>{{ lot.product_lot?.lot_number ?? 'N/A' }}</td>
                <td>{{ lot.quantity }}</td>
                <td>{{ lot.product_lot?.condition ?? 'N/A' }}</td>
                <td>{{ lot.product_lot?.expiration_date ?? 'N/A' }}</td>
                <td>{{ lot.product_lot?.box?.rack?.room?.floor?.location?.name ?? 'N/A' }}</td>
                <td>{{ lot.product_lot?.box?.rack?.room?.floor?.name ?? 'N/A' }}</td>
                <td>{{ lot.product_lot?.box?.rack?.room?.name ?? 'N/A' }}</td>
                <td>{{ lot.product_lot?.box?.rack?.label ?? 'N/A' }}</td>
                <td>{{ lot.product_lot?.box?.label ?? 'N/A' }}</td>
                <td>
                  <input
                    type="number"
                    v-model.number="lot.selectedQty"
                    :max="lot.quantity"
                    min="1"
                    class="form-control form-control-sm mb-1"
                    placeholder="Qty"
                    style="width: 70px;"
                  />
                  <button
                    class="btn btn-sm btn-primary"
                    @click="addToCart(lot)"
                    :disabled="!lot.selectedQty || lot.selectedQty <= 0 || lot.selectedQty > lot.quantity"
                  >
                    Add Item
                  </button>
                </td>
              </tr>
              <tr v-if="lots.length === 0">
                <td colspan="13" class="text-center text-muted">No items found</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- View Cart Button -->
      <button
        class="btn btn-outline-primary my-3"
        v-if="cart.length > 0"
        @click="showCart = true"
      >
        View Cart ({{ cart.length }})
      </button>
    </div>
  </div>

  <!-- Cart Modal -->
  <div class="modal fade show d-block" id="cartModal" tabindex="-1" v-if="showCart">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Cart Preview</h5>
          <button type="button" class="btn-close" @click="showCart = false"></button>
        </div>
        <div class="modal-body table-responsive">
          <table class="table table-sm table-bordered">
            <thead>
            <tr>
              <th>#</th>
              <th>Product</th> 
              <th>Price</th>
              <th>Batch Number</th>
              <th>Qty</th>
              <th>Subtotal</th>
              <th>Action</th>
            </tr>
          </thead>
            <tbody>
              <tr v-for="(item, idx) in cart" :key="idx">
                <td>{{ idx + 1 }}</td>
                <td>{{ item.product }}</td>
                <td>{{ item.price }}</td>
                <td>{{ item.lot_number }}</td>
                <td>{{ item.qty }}</td>
                <td>{{ formatCurrency(item.price * item.qty) }}</td>
                <td>
                  <button class="btn btn-sm btn-danger" @click="removeFromCart(idx)">
                    Remove
                  </button>
                </td>
              </tr>
              <tr>
                <td colspan="5" class="text-end"><strong>Total:</strong></td>
                <td colspan="2"><strong>{{ formatCurrency(totalAmount) }}</strong></td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showCart = false">Close</button>
          <button class="btn btn-success" @click="submitCart">Submit Cart</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Backdrop -->
  <div class="modal-backdrop fade show" v-if="showCart"></div>
</template>

<script>
export default {
  data() {
    return {
      lots: [],
      cart: [],
      showCart: false
    };
  },
  computed: {
    totalAmount() {
      return this.cart.reduce((sum, item) => {
        const price = parseFloat(item.price) || 0;
        return sum + price * item.qty;
      }, 0);
    }
  },
  mounted() {
    this.fetchAvailableItems();
  },
  methods: {
    async fetchAvailableItems() {
      try {
        const res = await fetch(`${window.baseUrl}/available-items`);
        const data = await res.json();
        this.lots = data.map(item => ({
          ...item,
          selectedQty: null
        }));
      } catch (error) {
        console.error("Error fetching available items:", error);
      }
    },

    addToCart(lot) {
      // Extract IDs safely
      const lotId = lot.id;
      const productId = lot.product_lot?.product?.id ?? null;
      const price = lot.product_lot?.product?.price ?? 0;

      const existingIndex = this.cart.findIndex(item => item.lot_id === lotId);

      if (existingIndex !== -1) {
        this.cart[existingIndex].qty += lot.selectedQty;
      } else {
        this.cart.push({
          lot_id: lotId,
          product_id: productId,
          product: lot.product_lot?.product?.name ?? 'N/A',
          lot_number: lot.product_lot?.lot_number ?? 'N/A',
          price: parseFloat(price),
          qty: lot.selectedQty
        });
      }

      lot.quantity -= lot.selectedQty;
      lot.selectedQty = null;
    },

    removeFromCart(index) {
      const item = this.cart[index];
      const lot = this.lots.find(l => l.id === item.lot_id);
      if (lot) {
        lot.quantity += item.qty;
      }
      this.cart.splice(index, 1);
    },

    formatCurrency(amount) {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }).format(amount);
    },

    async submitCart() {
    try {
      // Step 1: Create Purchase Order
      const orderResponse = await fetch(`${window.baseUrl}/purchase-orders`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ notes: 'Auto-generated from cart' })
      });

      const orderData = await orderResponse.json();
      if (!orderResponse.ok || !orderData.order?.id) {
        alert(orderData.message || 'Failed to create order.');
        return;
      }

      const orderId = orderData.order.id;

      // Step 2: Prepare Cart Items
      const itemsPayload = this.cart.map(item => {
        const qty = parseInt(item.qty) || 0;
        const price = parseFloat(item.price) || 0;
        return {
          purchase_order_id: orderId,
          lot_id: item.lot_id,
          product_id: item.product_id,
          quantity: qty,
          unit_price: price,
          subtotal: qty * price
        };
      });

      console.log("Items being sent:", itemsPayload);

      // Step 3: Store Items
      const itemsResponse = await fetch(`${window.baseUrl}/purchase-orders/${orderId}/items`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ items: itemsPayload })
      });

      const itemsData = await itemsResponse.json();
      if (!itemsResponse.ok) {
        console.error(itemsData);
        alert('Failed to store purchase order items.');
        return;
      }

      // Step 4: Success
      console.log("Saved Items Response:", itemsData);
      this.cart = [];

      // Step 5: Close Popup (Bootstrap Modal Example)
      const modalEl = document.getElementById('cartModal');
      if (modalEl) {
        modalEl.classList.remove('show');
        modalEl.setAttribute('aria-hidden', 'true');
        modalEl.style.display = 'none';
        document.body.classList.remove('modal-open');
        document.querySelector('.modal-backdrop')?.remove();
      }
      // Refresh page after slight delay to ensure UI updates
      
      setTimeout(() => {
        window.location.reload();
      }, 300);

    } catch (error) {
      console.error("Submit Error:", error);
      alert("An error occurred during submission.");
    }
  }

}

};
</script>




