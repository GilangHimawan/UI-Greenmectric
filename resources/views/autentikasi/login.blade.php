<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UI Green Metric</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #10b981 0%, #ffffff 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            padding: 40px;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
        }

        .logo-text {
            font-size: 22px;
            font-weight: bold;
            color: #10b981;
            margin-top: 10px;
        }

        .logo-sub {
            font-size: 13px;
            color: #6b7280;
            margin-top: 4px;
        }

        /* Alert error */
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .alert-error ul {
            margin: 0;
            padding-left: 16px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #374151;
            font-weight: 500;
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper input {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
            color: #1f2937;
        }

        .input-wrapper input:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .input-wrapper input.is-invalid {
            border-color: #dc2626;
        }

        /* Toggle password */
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            font-size: 16px;
            padding: 0;
        }

        .toggle-password:hover {
            color: #10b981;
        }

        .invalid-feedback {
            color: #dc2626;
            font-size: 12px;
            margin-top: 4px;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }

        .form-check input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #10b981;
            cursor: pointer;
        }

        .form-check label {
            font-size: 13px;
            color: #6b7280;
            cursor: pointer;
            margin: 0;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s, opacity 0.2s;
        }

        .login-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .login-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .footer-text {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">

            {{-- Logo --}}
            <div class="logo">
                <img src="{{ asset('assets/img/logo.png') }}" alt="Logo"
                     onerror="this.src='https://via.placeholder.com/80/10b981/ffffff?text=GM'">
                <div class="logo-text">UI Green Metric</div>
                <div class="logo-sub">Sistem Penilaian Kinerja Internal</div>
            </div>

            {{-- Alert error --}}
            @if($errors->any())
                <div class="alert-error">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('error'))
                <div class="alert-error">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Form Login --}}
            <form action="{{ route('login.perform') }}" method="POST" id="formLogin">
                @csrf

                {{-- Email atau Username --}}
                <div class="form-group">
                    <label for="login">Email/Username</label>
                    <div class="input-wrapper">
                        <input type="text"
                               id="login"
                               name="login"
                               value="{{ old('login') }}"
                               placeholder="Masukkan email atau username Anda"
                               class="{{ $errors->has('login') ? 'is-invalid' : '' }}"
                               autofocus
                               required>
                    </div>
                    @error('login')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password"
                               id="password"
                               name="password"
                               placeholder="Masukkan password Anda"
                               class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                               required>
                        <button type="button"
                                class="toggle-password"
                                id="togglePassword"
                                title="Tampilkan/sembunyikan password">
                            👁
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Ingat Saya --}}
                <div class="form-check">
                    <input type="checkbox" id="remember" name="remember"
                           {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">Ingat saya</label>
                </div>

                {{-- Tombol Login --}}
                <button type="submit" class="login-btn" id="btnLogin">
                    Masuk
                </button>

            </form>

            <div class="footer-text">
                &copy; {{ date('Y') }} Politeknik Negeri Banyuwangi
            </div>

        </div>
    </div>

    <script>
        // Toggle show/hide password
        document.getElementById('togglePassword').addEventListener('click', function () {
            const input = document.getElementById('password')
            const isHidden = input.type === 'password'
            input.type = isHidden ? 'text' : 'password'
            this.textContent = isHidden ? '👁' : '👁'
        })

        // Disable tombol saat submit (cegah double click)
        document.getElementById('formLogin').addEventListener('submit', function () {
            const btn = document.getElementById('btnLogin')
            btn.disabled = true
            btn.textContent = 'Memproses...'
        })
    </script>
</body>
</html>