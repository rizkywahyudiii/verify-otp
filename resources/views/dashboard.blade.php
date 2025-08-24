@extends('layouts.app')

@section('title', 'Dashboard')

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 20px;
        background-color: #f5f5f5;
    }
    .container {
        max-width: 500px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s ease;
    }
    .btn-outline-warning {
        background-color: transparent;
        color: #ffc107;
        border: 2px solid #ffc107;
        margin-right: 20px;
    }
    .btn-outline-warning:hover {
        background-color: #ffc107;
        color: #212529;
    }
    .btn-outline-secondary {
        background-color: transparent;
        color: #6c757d;
        border: 2px solid #6c757d;
    }
    .btn-outline-secondary:hover {
        background-color: #6c757d;
        color: white;
    }
    .btn-outline-danger {
        background-color: transparent;
        color: #dc3545;
        border: 2px solid #dc3545;
    }
    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: white;
    }
    .btn-danger {
        background-color: #dc3545;
        color: white;
    }
    .btn-danger:hover {
        background-color: #c82333;
    }
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        z-index: 1000;
    }
    .modal-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 30px;
        border-radius: 8px;
        max-width: 400px;
        width: 90%;
    }
    .form-group {
        margin-bottom: 20px;
    }
    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    input[type="password"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
    }
    .error {
        color: #dc3545;
        font-size: 14px;
        margin-top: 5px;
    }
    .modal-buttons {
        text-align: right;
        margin-top: 20px;
    }
    .modal-buttons button {
        margin-left: 10px;
    }
</style>

@section('content')
    <div style="text-align: center;">
        <h2 style="margin-bottom: 20px;">Dashboard</h2>

        <div style="background-color: #e9ecef; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
            <h3 style="margin: 0; color: #495057;">Selamat datang, {{ Auth::user()->name }}!</h3>
            <p style="margin: 10px 0 0 0; color: #6c757d;">Email: {{ Auth::user()->email }}</p>
        </div>

        <div style="margin-bottom: 20px;">
            <form method="POST" action="{{ route('logout') }}" style="display: inline-block; margin-right: 20px;">
                @csrf
                <button type="submit" class="btn btn-outline-warning" style="padding: 12px 24px; font-size: 16px;">
                    Logout
                </button>
            </form>

            <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()" style="padding: 12px 24px; font-size: 16px;">
                Hapus Akun
            </button>
        </div>

        <!-- Modal for account deletion -->
        <div id="deleteModal" class="modal">
            <div class="modal-content">
                <h3 style="margin-top: 0; color: #dc3545;">Hapus Akun</h3>
                <p style="color: #666; margin-bottom: 20px;">
                    Tindakan ini tidak dapat dibatalkan. Semua data Anda akan hilang selamanya.
                </p>

                <form method="POST" action="{{ route('delete-account') }}">
                    @csrf
                    <div class="form-group">
                        <label for="password">Masukkan password untuk konfirmasi:</label>
                        <input type="password" id="password" name="password" required>
                        @error('password')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modal-buttons">
                        <button type="button" class="btn btn-outline-secondary" onclick="closeDeleteModal()">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-danger">
                            Hapus Akun
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete() {
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modal when clicking outside
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });
    </script>
@endsection
