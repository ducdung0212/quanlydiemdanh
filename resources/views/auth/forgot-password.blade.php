
<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-white to-sky-50">
        <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-white to-sky-50">
            <div class="flex justify-center items-center w-full">
                <div class="bg-white p-8 md:p-12 rounded-3xl shadow flex items-center mx-auto max-w-sm w-full">
                    <div class="w-full">
                            <h2 class="text-2xl font-bold text-black text-center mb-6">Quên mật khẩu</h2>
                            <p class="mb-4 text-sm text-gray-600 text-center">Vui lòng nhập email của bạn, hệ thống sẽ gửi liên kết đặt lại mật khẩu.</p>

                            <!-- Session Status -->
                            <x-auth-session-status class="mb-4" :status="session('status')" />

                            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                                @csrf

                                <!-- Email Address -->
                                <div>
                                    <label for="email" class="sr-only">Email</label>
                                    <div class="flex items-center bg-gray-50 rounded-full px-4 py-3 border border-gray-200">
                                        <svg class="w-5 h-5 text-sky-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12H8m8 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        <x-text-input id="email" class="w-full bg-transparent border-0 p-0 text-black placeholder-gray-500" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="Email" />
                                    </div>
                                    <x-input-error :messages="$errors->get('email')" class="mt-1 text-sm text-red-600" />
                                </div>

                                <div>
                                    <x-primary-button class="w-full py-3 rounded-full bg-gradient-to-r from-sky-600 to-sky-500 hover:from-sky-700 text-white">
                                        Gửi liên kết đặt lại mật khẩu
                                    </x-primary-button>
                                </div>
                            </form>

                            <p class="text-center text-sm text-gray-600 mt-6">
                                <a href="{{ route('login') }}" class="underline text-sky-600">Quay lại đăng nhập</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
