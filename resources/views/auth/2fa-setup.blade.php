@extends('layouts.auth')

@section('title', '2FA Setup')

@section('content')
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-2xl">
            <!-- Header -->
            <div class="text-center mb-8">
                <div
                    class="inline-flex items-center justify-center w-16 h-16 bg-white/20 backdrop-blur-lg rounded-full mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Setup Two-Factor Authentication</h1>
                <p class="text-blue-100">Secure your account with 2FA</p>
            </div>

            <!-- Setup Card -->
            <div class="backdrop-blur-lg bg-white/90 rounded-2xl shadow-2xl p-8">
                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-800 rounded-lg p-4">
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid md:grid-cols-2 gap-8">
                    <!-- QR Code Section -->
                    <div class="text-center">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Step 1: Scan QR Code</h3>
                        <p class="text-sm text-gray-600 mb-4">Use Google Authenticator, Authy, or any TOTP app</p>

                        <div class="bg-white p-4 rounded-lg inline-block border-2 border-gray-200">
                            {!! $qrCode !!}
                        </div>

                        <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-500 mb-1">Can't scan? Enter this code manually:</p>
                            <code class="text-sm font-mono font-semibold text-gray-900 break-all">{{ $secret }}</code>
                        </div>
                    </div>

                    <!-- Verification Section -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Step 2: Verify Code</h3>
                        <p class="text-sm text-gray-600 mb-6">Enter the 6-digit code from your authenticator app</p>

                        <form method="POST" action="{{ route('2fa.verify-setup') }}">
                            @csrf

                            <div class="mb-6">
                                <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                                    Verification Code
                                </label>
                                <input type="text" name="code" id="code" required autofocus maxlength="6" pattern="[0-9]{6}"
                                    class="block w-full px-4 py-3 text-center text-2xl font-mono border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition tracking-widest"
                                    placeholder="000000">
                                <p class="mt-2 text-xs text-gray-500">Enter a 6-digit number from your authenticator app</p>
                            </div>

                            <button type="submit"
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                Verify & Enable 2FA
                            </button>
                        </form>

                        <!-- Recommended Apps -->
                        <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                            <h4 class="text-sm font-semibold text-blue-900 mb-2">📱 Recommended Apps:</h4>
                            <ul class="text-xs text-blue-800 space-y-1">
                                <li>• Google Authenticator</li>
                                <li>• Microsoft Authenticator</li>
                                <li>• Authy</li>
                                <li>• 1Password</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-submit when 6 digits entered
        document.getElementById('code').addEventListener('input', function (e) {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 6);
            if (e.target.value.length === 6) {
                // Optional: auto-submit after brief delay
                // setTimeout(() => e.target.form.submit(), 500);
            }
        });
    </script>
@endsection