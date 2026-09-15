<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                    Dashboard Utama
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Selamat datang kembali, <span class="font-semibold">{{ $this->getUserName() }}</span> ({{ $this->getRoleName() }})
                </p>
            </div>
            <div class="text-right hidden sm:block">
                <p class="text-sm font-medium text-gray-900 dark:text-gray-200">
                    {{ now()->translatedFormat('l, d F Y') }}
                </p>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
