@extends('layouts.app')

@section('title', 'Settings')

@section('content')
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Settings</h1>
            <p class="text-gray-500 mt-1">Manage your account settings and preferences</p>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-lg p-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 rounded-lg p-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="space-y-6">
            <!-- Profile Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Profile Information</h2>

                <form method="POST" action="{{ route('settings.profile') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name', Auth::user()->name) }}" required
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email', Auth::user()->email) }}"
                                required
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                Update Profile
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Change Password</h2>

                <form method="POST" action="{{ route('settings.password') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current
                                Password</label>
                            <input type="password" name="current_password" id="current_password" required
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('current_password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input type="password" name="password" id="password" required
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Must be at least 8 characters with mixed case, numbers,
                                and symbols</p>
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm
                                New Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                Change Password
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Two-Factor Authentication -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Two-Factor Authentication</h2>
                        <p class="text-sm text-gray-600 mt-1">Add an extra layer of security to your account</p>
                    </div>
                    @if(Auth::user()->has2FA())
                        <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                            ✓ Enabled
                        </span>
                    @else
                        <span class="px-3 py-1 bg-gray-100 text-gray-800 text-sm font-medium rounded-full">
                            Disabled
                        </span>
                    @endif
                </div>

                @if(Auth::user()->has2FA())
                    <p class="text-sm text-gray-700 mb-4">
                        2FA is currently enabled on your account. You'll need to enter a code from your authenticator app each
                        time you sign in.
                    </p>

                    <form method="POST" action="{{ route('2fa.disable') }}"
                        onsubmit="return confirm('Are you sure you want to disable 2FA? This will make your account less secure.')">
                        @csrf
                        <div class="mb-4">
                            <label for="disable_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm
                                Password</label>
                            <input type="password" name="password" id="disable_password" required
                                class="block w-full max-w-sm px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                            Disable 2FA
                        </button>
                    </form>
                @else
                    <p class="text-sm text-gray-700 mb-4">
                        2FA is not enabled. Enable it to secure your account with an authenticator app.
                    </p>

                    <form method="POST" action="{{ route('2fa.enable') }}">
                        @csrf
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            Enable 2FA
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection