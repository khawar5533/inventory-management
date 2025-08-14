<template>
<div class="container-fluid">

					<div class="header">
						<h1 class="header-title">
							Welcome back, {{ user.name }}!
						</h1>
						<p class="header-subtitle">You have 24 new messages and 5 new notifications.</p>
					</div>

					<div class="row">
						<div class="col-xl-6 col-xxl-7">
							<div class="card flex-fill w-100">
								<div class="card-header">
									<div class="card-actions float-end">
										<a href="#" class="me-1">
											<i class="align-middle" data-feather="refresh-cw"></i>
										</a>
										<div class="d-inline-block dropdown show">
											<a href="#" data-bs-toggle="dropdown" data-bs-display="static">
												<i class="align-middle" data-feather="more-vertical"></i>
											</a>

											<div class="dropdown-menu dropdown-menu-end">
												<a class="dropdown-item" href="#">Action</a>
												<a class="dropdown-item" href="#">Another action</a>
												<a class="dropdown-item" href="#">Something else here</a>
											</div>
										</div>
									</div>
									<h5 class="card-title mb-0">Monthly Sales</h5>
								</div>
								<div class="card-body py-3">
									<div class="card-body d-flex w-100">
									<div class="align-self-center chart chart-lg">
										<canvas id="chartjs-dashboard-bar"></canvas>
									</div>
								</div>
								</div>
							</div>
						</div>

						<div class="col-xl-6 col-xxl-5 d-flex">
							<div class="w-100">
								<div class="row">
									<div class="col-sm-6">
										<div class="card">
											<div class="card-body">
												<div class="row">
													<div class="col mt-0">
														<h5 class="card-title">Sales Today</h5>
													</div>

													<div class="col-auto">
														<div class="avatar">
															<div class="avatar-title rounded-circle bg-primary-dark">
																<i class="align-middle" data-feather="truck"></i>
															</div>
														</div>
													</div>
												</div>
												<h1 class="display-5 mt-1 mb-3">${{ dashboard.salesToday }}</h1>

											</div>
										</div>
										<div class="card">
											<div class="card-body">
												<div class="row">
													<div class="col mt-0">
														<h5 class="card-title">Pending Orders</h5>
													</div>

													<div class="col-auto">
														<div class="avatar">
															<div class="avatar-title rounded-circle bg-primary-dark">
																<i class="align-middle" data-feather="shopping-cart"></i>
															</div>
														</div>
													</div>
												</div>
												<h1 class="display-5 mt-1 mb-3">{{ dashboard.pendingOrders }}</h1>
											</div>
										</div>
									</div>
									<div class="col-sm-6">
										<div class="card">
											<div class="card-body">
												<div class="row">
													<div class="col mt-0">
														<h5 class="card-title">Total Earnings</h5>
													</div>

													<div class="col-auto">
														<div class="avatar">
															<div class="avatar-title rounded-circle bg-primary-dark">
																<i class="align-middle" data-feather="dollar-sign"></i>
															</div>
														</div>
													</div>
												</div>
												<h1 class="display-5 mt-1 mb-3">${{ dashboard.totalEarnings }}</h1>
											</div>
										</div>
										
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-12 col-lg-8 col-xxl-12 d-flex">
							<div class="card flex-fill">
								<div class="card-header">
									<div class="card-actions float-end">
										<a href="#" class="me-1">
											<i class="align-middle" data-feather="refresh-cw"></i>
										</a>
										<div class="d-inline-block dropdown show">
											<a href="#" data-bs-toggle="dropdown" data-bs-display="static">
												<i class="align-middle" data-feather="more-vertical"></i>
											</a>

											<div class="dropdown-menu dropdown-menu-end">
												<a class="dropdown-item" href="#">Action</a>
												<a class="dropdown-item" href="#">Another action</a>
												<a class="dropdown-item" href="#">Something else here</a>
											</div>
										</div>
									</div>
									<h5 class="card-title mb-0">Product Quantity Thres</h5>
								</div>
								<table id="datatables-dashboard-projects" class="table table-striped my-0">
									<thead>
										<tr>
									<th>Name</th>
									<th>Threshold</th>
									<th>Quantity</th>
									<th>Status</th>
									</tr>
									</thead>
									<tbody>
									 <tr v-for="(product, index) in products" :key="index">
									<td>{{ product.product_name }}</td>
									<td>{{ product.reorder_threshold }}</td>
									<td>{{ product.total_quantity }}</td>
									<td>
										<span
										class="badge"
										:class="product.stock_status === 'low' ? 'bg-danger' : 'bg-success'"
										>
										{{ product.stock_status }}
										</span>
									</td>
									</tr>
									<tr v-if="products.length === 0">
									<td colspan="4" class="text-center">No products found</td>
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
		name: "Dashboard",
		data() {
			return {
			user: window.authUser || {}, // user info
			dashboard: {},               // holds salesToday, totalEarnings, etc.
			products: [],                // holds stock status data
			};
		},
		mounted() {
			// fetch dashboard metrics
			fetch(`${window.baseUrl}/dashboard-data`)
			.then((res) => res.json())
			.then((data) => {
				this.dashboard = data;
			})
			.catch((err) => console.error("Error fetching dashboard:", err));

			// fetch stock status
			fetch(`${window.baseUrl}/purchase-orders/stock-status`)
			.then((res) => res.json())
			.then((data) => {
				this.products = data;
				// Initialize DataTables after Vue renders DOM
			this.$nextTick(() => {
				$('#datatables-dashboard-projects').DataTable({
				pageLength: 4,
				lengthChange: false,
				bFilter: false,
				autoWidth: false
				});
			});
			
			})
			.catch((err) => console.error("Error fetching stock status:", err));
		},
		};
		</script>