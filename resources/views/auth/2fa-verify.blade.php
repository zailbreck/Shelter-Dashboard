@extends('layouts.auth')

@section('title', '2FA Verification')

@section('content')
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <!-- Header -->
            <div class="text-center mb-8">
                <div
                    class="inline-flex items-center justify-center w-16 h-16 bg-white/20 backdrop-blur-lg rounded-full mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">Two-Factor Authentication</h1>
                <p class="text-blue-100">Enter the code from your authenticator app</p>
            </div>

            <!-- Verification Card -->
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

                <form method="POST" action="{{ route('2fa.verify') }}">
                    @csrf

                    <div class="mb-6">
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-3 text-center">
                            Verification Code
                        </label>
                        <input type="text" name="code" id="code" required autofocus maxlength="6" pattern="[0-9]{6}"
                            class="block w-full px-4 py-4 text-center text-3xl font-mono border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition tracking-widest"
                            placeholder="● ● ● ● ● ●">
                        <p class="mt-3 text-xs text-gray-500 text-center">Open your authenticator app to view your code</p>
                    </div>

                    <button type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        Verify
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-gray-600 hover:text-gray-900">
                            ← Back to Login
                        </button>
                    </form>
                </div>
            </div>

            <!-- Help Text -->
            <div class="mt-6 text-center text-blue-100 text-sm">
                <p>Lost access? Contact your administrator</p>
            </div>
        </div>
    </div>

    <script>
        // Auto-format and submit
        const codeInput = document.getElementById('code');

        codeInput.addEventListener('input', function (e) {
            // Only allow digits
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 6);

            // Auto-submit when 6 digits entered
            if (e.target.value.length === 6) {
                setTimeout(() => e.target.form.submit(), 300);
            }
        });

        // Auto-focus
        codeInput.focus();
    </script>
@endsection