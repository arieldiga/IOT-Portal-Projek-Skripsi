@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Ganti Password</h2>
    <form method="POST" action="/change-password">
        @csrf
        <div>
            <label>Password Lama</label>
            <input type="password" name="current_password" required>
        </div>
        <div>
            <label>Password Baru</label>
            <input type="password" name="new_password" required>
        </div>
        <div>
            <label>Konfirmasi Password Baru</label>
            <input type="password" name="new_password_confirmation" required>
        </div>
        <button type="submit">Update</button>
    </form>
</div>
@endsection
