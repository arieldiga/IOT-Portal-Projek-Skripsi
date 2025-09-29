<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login - Lautan Air Indonesia</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="{{ asset('css/login.css') }}" rel="stylesheet">
</head>

<body>
    <div class="login-container">
        <div class="logo-container">
            <div class="logo">
                <img src="{{ asset('images/Logo_LAI.PNG') }}" alt="Lautan Air Indonesia Logo" class="logo">
            </div>
        </div>

        {{-- Display validation errors --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <small>{{ $error }}</small><br>
                @endforeach
            </div>
        @endif

        {{-- Display success message --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- Login Form --}}
        <form action="#" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="login">Username</label>
                <input type="text" 
                       name="login" 
                       id="login" 
                       placeholder="Input Username" 
                       class="form-control" 
                       value="{{ old('login') }}" 
                       required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" 
                       name="password" 
                       id="password" 
                       placeholder="Input password" 
                       class="form-control" 
                       required>
            </div>

            <button type="submit" class="btn-login">Login</button>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Input Animation -->
    <script>
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>
