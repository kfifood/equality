<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>K-SQUID | Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <!-- Font: Montserrat -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- iCheck -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/square/_all.css">

    <style>
        /* SEMUA CSS YANG SUDAH ADA TETAP SAMA */
        body {
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .background-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("{{ asset('login-bg.jpg') }}") no-repeat center center;
            background-size: cover;
            z-index: -1;
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding-right: 10%;
            position: relative;
        }

        .login-box {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            width: 420px;
            padding: 60px 40px;
            border-radius: 25px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            text-align: center;
            margin: 20px 0;
        }

        .login-box h3 {
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 40px;
            color: #30318B;
        }

        .form-control {
            font-size: 15px;
            padding: 14px;
            height: auto;
            margin-bottom: 28px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .login-logo {
            margin-bottom: 20px;
        }
        .login-logo img {
            height: 70px;
            margin-bottom: 10px;
        }
        .login-logo h3 {
            font-weight: 700;
            font-size: 2rem;
            color: #30318B;
            margin-bottom: 0;
        }
        .login-logo h2{
            display: none;
        }

        .form-control:focus {
            border-color: #30318B;
            box-shadow: 0 0 0 2px rgba(48,49,139,0.2);
        }

        .checkbox label {
            font-size: 14px;
            color: #555;
        }

        .btn-login {
            background: linear-gradient(to left, #1E90FF, #30318B);
            color: #fff;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            padding: 12px;
            width: 70%;
            transition: 0.3s;
            margin-bottom: 15px;
        }

        .btn-login:hover {
            opacity: 0.9;
        }

        .btn-rfid {
            background: linear-gradient(to left, #28a745, #20c997);
            color: #fff;
            border: none;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            padding: 10px;
            width: 70%;
            transition: 0.3s;
            margin-bottom: 15px;
        }

        .btn-rfid:hover {
            opacity: 0.9;
        }

        .register-link {
            text-align: center;
            margin-top: 15px;
            font-size: 1.5rem;
        }
        .register-link a {
            color: #4361EE;
            text-decoration: none;
            font-weight: 500;
        }
        .register-link a:hover {
            text-decoration: underline;
        }

        /* MODAL RFID - VERSI DIPERBAIKI */
        .rfid-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .rfid-modal-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            margin: 10% auto;
            width: 400px;
            border-radius: 25px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.3);
            padding: 40px 30px;
            position: relative;
            text-align: center;
        }

        .close-rfid {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #30318B;
        }

        .rfid-scan-area {
            border: 2px dashed #30318B;
            border-radius: 15px;
            padding: 40px 20px;
            text-align: center;
            margin: 20px 0;
            background: rgba(48, 49, 139, 0.05);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .rfid-scan-area.scanning {
            border-color: #28a745;
            background: rgba(40, 167, 69, 0.1);
            box-shadow: 0 0 15px rgba(40, 167, 69, 0.3);
        }

        .rfid-icon {
            font-size: 48px;
            color: #30318B;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .rfid-scan-area.scanning .rfid-icon {
            color: #28a745;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .rfid-scan-text {
            font-size: 16px;
            color: #30318B;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .rfid-scan-subtext {
            font-size: 12px;
            color: #666;
        }

        .rfid-loading {
            display: none;
            text-align: center;
            margin: 20px 0;
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #4361EE;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s ease-in-out infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Input Manual RFID */
        .rfid-manual-input {
            margin-top: 20px;
            padding: 15px;
            border-top: 1px solid #eee;
        }

        .manual-input-field {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            text-align: center;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .manual-input-btn {
            background: linear-gradient(to left, #1E90FF, #30318B);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            width: 100%;
        }

        .toggle-manual {
            color: #30318B;
            text-decoration: none;
            font-size: 12px;
            cursor: pointer;
            margin-top: 10px;
            display: inline-block;
        }

        .toggle-manual:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .login-container {
                justify-content: center;
                padding: 20px;
            }
            .login-box {
                width: 100%;
                max-width: 400px;
                padding: 30px 20px;
            }
            .rfid-modal-content {
                width: 90%;
                margin: 20% auto;
            }
            .background-container{
                background-image: none;
                background-color: #F4F6F9;
            }
            .login-logo h3{
                display: none;
            }
            .login-logo h2{
                color: #30318B;
                display: block;
                font-weight: 700;
            }
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <!-- Background Container -->
    <div class="background-container"></div>
    
    <!-- Main Content - TIDAK BERUBAH -->
    <div class="login-container">
        <div class="login-box">
            <div class="login-logo">
                <img src="{{ asset('logo.png') }}" alt="Logo K-FISH">
                <h2> Login to K-FISH </h2>
                <h3>Login User</h3>
            </div>

            <form method="POST" action="{{ url('/login') }}" id="loginForm">
                {!! csrf_field() !!}

                <div class="form-group has-feedback {{ $errors->has('username') ? ' has-error' : '' }}">
                    <input type="text" class="form-control" name="username" value="{{ old('username') }}" placeholder="Username" autocomplete="off">
                    <span class="glyphicon glyphicon-user form-control-feedback"></span>
                    @if ($errors->has('username'))
                        <span class="help-block"><strong>{{ $errors->first('username') }}</strong></span>
                    @endif
                </div>

                <div class="form-group has-feedback {{ $errors->has('password') ? ' has-error' : '' }}">
                    <input type="password" class="form-control" name="password" placeholder="Password">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                    @if ($errors->has('password'))
                        <span class="help-block"><strong>{{ $errors->first('password') }}</strong></span>
                    @endif
                </div>

                <div class="checkbox text-left">
                    <label><input type="checkbox" name="remember"> Remember Me</label>
                </div>

                <button type="submit" class="btn-login">LOGIN</button>
                
                <!-- Tombol Login dengan RFID -->
                <button type="button" class="btn-rfid" id="btnRfidLogin">
                    <i class="fa fa-id-card"></i> LOGIN DENGAN RFID
                </button>

                <div class="register-link">
                    Belum punya akun? <a href="https://wa.me/082139385685" target="_blank">Hubungi Administrator</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal RFID - VERSI DIPERBAIKI -->
    <div id="rfidModal" class="rfid-modal">
        <div class="rfid-modal-content">
            <span class="close-rfid">&times;</span>
            <div class="login-logo">
                <h3 style="margin-bottom: 20px;">Login dengan RFID</h3>
            </div>
            
            <div class="rfid-scan-area" id="rfidScanArea">
                <div class="rfid-icon">
                    <i class="fa fa-credit-card"></i>
                </div>
                <div class="rfid-scan-text">
                    Tempelkan Kartu RFID Anda di Reader
                </div>
                <div class="rfid-scan-subtext">
                    Scanner akan membaca secara otomatis
                </div>
            </div>

            <div class="rfid-loading" id="rfidLoading">
                <div class="loading-spinner"></div>
                <span>Memproses kartu RFID...</span>
            </div>

            <!-- Input Manual -->
            <div class="rfid-manual-input" id="manualInput" style="display: none;">
                <input type="text" class="manual-input-field" id="manualRfidCode" 
                       placeholder="Masukkan kode RFID manual" maxlength="20">
                <button class="manual-input-btn" id="submitManualRfid">PROSES RFID</button>
            </div>

            <a class="toggle-manual" id="toggleManual">
                ↳ Input Kode RFID Manual
            </a>
        </div>
    </div>

    <!-- Input Tersembunyi untuk Scanner -->
    <input type="text" id="hiddenRfidInput" autocomplete="off" 
           style="opacity:0; position:absolute; left:-9999px;">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    $(document).ready(function() {
        const rfidModal = $('#rfidModal');
        const btnRfidLogin = $('#btnRfidLogin');
        const closeRfid = $('.close-rfid');
        const rfidScanArea = $('#rfidScanArea');
        const rfidLoading = $('#rfidLoading');
        const hiddenRfidInput = $('#hiddenRfidInput');
        const manualInput = $('#manualInput');
        const manualRfidCode = $('#manualRfidCode');
        const submitManualRfid = $('#submitManualRfid');
        const toggleManual = $('#toggleManual');
        
        let isScanning = false;
        let isManualMode = false;

        // Buka modal RFID
        btnRfidLogin.on('click', function() {
            rfidModal.fadeIn(200);
            resetRfidForm();
            hiddenRfidInput.val('').focus();
        });

        // Tutup modal
        closeRfid.on('click', function() {
            rfidModal.fadeOut(200);
            resetRfidForm();
        });

        $(window).on('click', function(event) {
            if (event.target === rfidModal[0]) {
                rfidModal.fadeOut(200);
                resetRfidForm();
            }
        });

        function resetRfidForm() {
            rfidScanArea.show().removeClass('scanning');
            rfidLoading.hide();
            isScanning = false;
            hiddenRfidInput.val('');
            manualRfidCode.val('');
            
            if (isManualMode) {
                toggleManualMode();
            }
        }

        // Toggle mode manual/scan
        toggleManual.on('click', function() {
            toggleManualMode();
        });

        function toggleManualMode() {
            isManualMode = !isManualMode;
            
            if (isManualMode) {
                manualInput.slideDown(300);
                rfidScanArea.hide();
                toggleManual.text('↳ Kembali ke Mode Scan');
                manualRfidCode.focus();
            } else {
                manualInput.slideUp(300);
                rfidScanArea.slideDown(300);
                toggleManual.text('↳ Input Kode RFID Manual');
                hiddenRfidInput.focus();
            }
        }

        // Submit manual RFID
        submitManualRfid.on('click', function() {
            const rfidCode = manualRfidCode.val().trim();
            if (rfidCode.length >= 5) {
                processRfidLogin(rfidCode);
            } else {
                Swal.fire({
                    title: 'Kode RFID terlalu pendek',
                    text: 'Kode RFID harus minimal 5 karakter',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
            }
        });

        manualRfidCode.on('keypress', function(e) {
            if (e.which === 13) {
                submitManualRfid.click();
            }
        });

        // Tangkap input dari RFID reader
        hiddenRfidInput.on('input', function() {
            const rfidCode = $(this).val().trim();
            if (rfidCode.length >= 5 && !isManualMode) {
                $(this).blur();
                processRfidLogin(rfidCode);
            }
        });

        // Klik area scan untuk testing
        rfidScanArea.on('click', function() {
            if (!isScanning && !isManualMode) {
                const testCode = prompt('Masukkan kode RFID untuk testing:');
                if (testCode && testCode.length >= 5) {
                    processRfidLogin(testCode);
                }
            }
        });

        // Fungsi proses login RFID
        function processRfidLogin(rfidCode) {
            console.log('RFID Code received:', rfidCode);
            
            if (!isScanning) {
                isScanning = true;
                rfidScanArea.addClass('scanning');
                rfidLoading.show();
                
                Swal.fire({
                    title: 'Memproses Kartu RFID',
                    text: 'Sedang memverifikasi...',
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Kirim ke server
                $.ajax({
                    url: '{{ route("login.rfid") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        rfid_code: rfidCode
                    },
                    success: function(response) {
                        Swal.close();
                        console.log('RFID login response:', response);

                        if (response.success) {
                            Swal.fire({
                                title: 'Login Berhasil!',
                                text: 'Mengarahkan ke dashboard...',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = response.redirect;
                            });
                        } else {
                            Swal.fire({
                                title: 'Gagal',
                                text: response.errors ? response.errors[0] : 'Login gagal',
                                icon: 'error',
                                confirmButtonText: 'Coba Lagi'
                            });
                            resetRfidForm();
                            if (isManualMode) {
                                manualRfidCode.focus();
                            } else {
                                hiddenRfidInput.focus();
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        console.log('Error:', xhr.responseText);
                        
                        let message = 'Terjadi kesalahan sistem.';
                        if (xhr.status === 422) message = 'Kode RFID tidak terdaftar.';
                        else if (xhr.status === 419) message = 'Session expired. Refresh halaman.';
                        else if (xhr.status === 500) message = 'Server error. Cek log Laravel.';

                        Swal.fire({
                            title: 'Error',
                            text: message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        
                        resetRfidForm();
                        if (isManualMode) {
                            manualRfidCode.focus();
                        } else {
                            hiddenRfidInput.focus();
                        }
                    },
                    complete: function() {
                        isScanning = false;
                    }
                });
            }
        }

        // Login manual form biasa (tetap sama)
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Memproses Login',
                text: 'Tunggu sebentar...',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            this.submit();
        });
    });
    </script>
</body>
</html>