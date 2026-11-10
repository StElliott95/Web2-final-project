<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MovieHub</title>
<style>
       body { font-family: Arial, sans-serif; margin: 0; background: #f4f4f9; }
       .navbar { background: #1a1a2e; padding: 15px 30px; display: flex; gap: 20px; align-items: center; }
       .navbar a { color: #e0e0e0; text-decoration: none; }
       .navbar .logo { color: white; font-weight: bold; font-size: 20px; margin-right: auto; }
       .content { max-width: 900px; margin: 30px auto; background: white; padding: 25px; border-radius: 8px; }
       table { width: 100%; border-collapse: collapse; margin-top: 15px; }
       th { background: #0f3460; color: white; }
       th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
       .success { background: #e6f6ec; color: #135c32; padding: 10px; border-radius: 6px; }
       .error { background: #fdeaea; color: #8a1f1f; padding: 10px; border-radius: 6px; }
       button, .btn { background: #0f3460; color: white; border: none; padding: 7px 14px; border-radius: 5px; cursor: pointer; text-decoration: none; }
       .danger { background: #d64545; }
       textarea, select { padding: 8px; margin: 5px 0 12px; width: 100%; box-sizing: border-box; }
       form.inline { display: inline; }
</style>
</head>
<body>
<nav class="navbar">
<div class="logo">MovieHub</div>
   @auth
<span style="color:#aaa">Logged in as: {{ auth()->user()->name }}</span>
       @if(auth()->user()->role == 'admin')
<a href="{{ route('admin-reviews') }}">Moderation</a>
       @endif
   @endauth
</nav>
<div class="content">
   {{-- flash messages --}}
   @if(session('success'))
<p class="success">{{ session('success') }}</p>
   @endif
   @if(session('error'))
<p class="error">{{ session('error') }}</p>
   @endif
   @foreach($errors->all() as $error)
<p class="error">{{ $error }}</p>
   @endforeach
   {{-- dynamic content --}}
   @yield('app-content')
</div>
</body>
</html>