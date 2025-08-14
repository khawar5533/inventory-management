<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Use Laravel asset helper correctly --}}
  
<script src="{{ asset('public/assets/js/settings.js') }}"></script>
<link href="{{ asset('public/assets/css/modern.css') }}" rel="stylesheet">
</head>
<body>

    @if ($defaultComponent === 'Login')
    @if (session('status'))
    <script>
        window.statusMessage = @json(session('status'));
    </script>
        @endif
        @vite('resources/js/login.js')
        <div id="login" data-component="Login"></div>
    @else
        @vite('resources/js/app.js')
        @if (session('status'))
       <script>
          window.statusMessage = @json(session('status'));
       </script>
      @endif
        <div id="app" class="wrapper" data-component="{{ $defaultComponent }}"></div>
      @endif

    {{-- FOOTER: Custom JS --}}
    <script src="{{ asset('public/assets/js/app.js') }}"></script>

    <script>
        window.baseUrl = "{{ url('/') }}";
        window.defaultComponent = @json($defaultComponent ?? 'Login');
        window.authUser = @json(Auth::user());
    </script>
    

    {{-- Your charts and map JS remain unchanged below --}}
    <script>
        document.addEventListener("DOMContentLoaded", function () {
        fetch("{{ route('dashboard.graph') }}") // use new route name
            .then(res => res.json())
            .then(data => {
                console.log("Sales data:", data.salesThisMonth);

                new Chart(document.getElementById("chartjs-dashboard-bar"), {
                    type: 'bar',
                    data: {
                        labels: ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
                        datasets: [{
                            label: "This year",
                            backgroundColor: window.theme.primary,
                            borderColor: window.theme.primary,
                            hoverBackgroundColor: window.theme.primary,
                            hoverBorderColor: window.theme.primary,
                            data: data.salesThisMonth,
                            barPercentage: 0.75,
                            categoryPercentage: 0.5
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        legend: { display: false },
                        scales: {
                            yAxes: [{
                                gridLines: { display: false },
                                stacked: false,
                                ticks: { stepSize: 20 }
                            }],
                            xAxes: [{
                                stacked: false,
                                gridLines: { color: "transparent" }
                            }]
                        }
                    }
                });
            })
            .catch(err => console.error(err));
    });

    </script>
</body>
</html>
