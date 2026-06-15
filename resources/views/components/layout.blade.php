<!doctype html>
<html lang="es" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Assets específicos para páginas personalizadas (separados de Filament) --}}
    @vite('resources/js/custom-pages.js')
    @yield('styles')
    @stack('styles')
</head>

<body class="fi">
    {{ $slot }}
    @yield('scripts')
    @stack('scripts')

    <!-- Notifications Container -->
    <div x-data="{
            notifications: [],
            add(msg, type = 'success') {
                const id = Date.now();
                this.notifications.push({ id, msg, type, show: true });
                setTimeout(() => {
                    this.remove(id);
                }, 3000);
            },
            remove(id) {
                const index = this.notifications.findIndex(n => n.id === id);
                if (index > -1) {
                    this.notifications[index].show = false;
                    setTimeout(() => {
                        this.notifications = this.notifications.filter(n => n.id !== id);
                    }, 300);
                }
            }
        }"
        @notify.window="add($event.detail.message, $event.detail.type)"
        class="fixed top-4 right-4 z-50 flex flex-col gap-2 w-full max-w-sm pointer-events-none"
    >
        <template x-for="notification in notifications" :key="notification.id">
            <div x-show="notification.show"
                 x-transition:enter="transform ease-out duration-300 transition"
                 x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                 x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="pointer-events-auto w-full max-w-sm overflow-hidden bg-white dark:bg-gray-800 rounded-xl shadow-lg ring-1 ring-black ring-opacity-5 p-4 flex items-center gap-4"
            >
                <!-- Icon -->
                <div class="flex-shrink-0">
                    <template x-if="notification.type === 'success'">
                        <div class="rounded-full bg-green-100 dark:bg-green-900/30 p-2">
                            <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </template>
                    <template x-if="notification.type === 'error'">
                        <div class="rounded-full bg-red-100 dark:bg-red-900/30 p-2">
                             <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                    </template>
                </div>

                <!-- Content -->
                <div class="flex-1 w-0">
                     <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="notification.type === 'success' ? 'Éxito' : 'Error'"></p>
                     <div class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-html="notification.msg"></div>
                </div>

                <!-- Close -->
                <div class="flex flex-shrink-0 ml-4">
                    <button @click="remove(notification.id)" class="inline-flex text-gray-400 bg-white dark:bg-gray-800 rounded-md hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </template>
    </div>

    <script nonce="{{ csp_nonce() }}">
        function showToast(message, type = "success") {
            window.dispatchEvent(new CustomEvent('notify', { detail: { message, type } }));
        }
    </script>
</body>

</html>
