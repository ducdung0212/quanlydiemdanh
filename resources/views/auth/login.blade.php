 <x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-white to-sky-50">
        <div class="w-full max-w-[1400px] mx-auto px-6 py-20">
            <div class="relative rounded-3xl overflow-hidden shadow-md">
                <div class="grid grid-cols-1 md:grid-cols-12">
                    <!-- Blue hero / illustration -->
                    <div class="hidden md:block md:col-span-8 bg-gradient-to-br from-sky-600 to-sky-400 p-16">
                        <div class="max-w-xl text-white">
                            <h1 class="text-5xl font-extrabold mb-4">Welcome</h1>
                            <p class="text-lg opacity-90">Hệ thống quản lý, điểm danh sinh viên bằng hình ảnh.</p>
                        </div>
                    </div>

                    <!-- White card (login form) -->
                    <div class="col-span-1 md:col-span-4 bg-white p-8 md:p-12 flex items-center">
                        <div class="w-full">
                            <h2 class="text-2xl font-bold text-black text-center mb-6">Sign in</h2>

                            <!-- Session Status -->
                            <x-auth-session-status class="mb-4" :status="session('status')" />

                            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                                @csrf

                                <div>
                                    <label for="email" class="sr-only">Email</label>
                                    <div class="flex items-center bg-gray-50 rounded-full px-4 py-3 border border-gray-200">
                                        <svg class="w-5 h-5 text-sky-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12H8m8 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        <x-text-input id="email" class="w-full bg-transparent border-0 p-0 text-black placeholder-gray-500" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="Email" />
                                    </div>
                                    <x-input-error :messages="$errors->get('email')" class="mt-1 text-sm text-red-600" />
                                </div>

                                <div>
                                    <label for="password" class="sr-only">Password</label>
                                    <div class="flex items-center bg-gray-50 rounded-full px-4 py-3 border border-gray-200">
                                        <svg class="w-5 h-5 text-sky-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.657 0 3-1.343 3-3V6a3 3 0 10-6 0v2c0 1.657 1.343 3 3 3zM5 11h14v8a2 2 0 01-2 2H7a2 2 0 01-2-2v-8z"/></svg>
                                        <x-text-input id="password" class="w-full bg-transparent border-0 p-0 text-black placeholder-gray-500" type="password" name="password" required autocomplete="current-password" placeholder="Password" />
                                    </div>
                                    <x-input-error :messages="$errors->get('password')" class="mt-1 text-sm text-red-600" />
                                </div>

                                <div class="flex items-center justify-between text-sm text-gray-700">
                                    <label class="inline-flex items-center">
                                        <input id="remember_me" type="checkbox" class="h-4 w-4 text-sky-600" name="remember">
                                        <span class="ml-2 text-black">Remember me</span>
                                    </label>
                                    @if (Route::has('password.request'))
                                        <a class="text-sky-600 hover:underline" href="{{ route('password.request') }}">Forgot Password?</a>
                                    @endif
                                </div>

                                <div>
                                    <x-primary-button class="w-full py-3 rounded-full bg-gradient-to-r from-sky-600 to-sky-500 hover:from-sky-700 text-white">
                                        Login
                                    </x-primary-button>
                                </div>
                            </form>

                            <p class="text-center text-sm text-gray-600 mt-6">Don't have an account? <a href="#" class="underline text-sky-600">Register</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
