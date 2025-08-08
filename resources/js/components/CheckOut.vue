<template>
  <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Empty card</h5>
                </div>
                <div class="card-body">
                    <div class="my-5">&nbsp;</div>
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
      items: [
        { name: "Test Item 1" },
        { name: "Test Item 2" },
        { name: "Test Item 3" }
      ]
    };
  }
};
</script>
