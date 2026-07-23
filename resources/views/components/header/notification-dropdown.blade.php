{{-- Notification Dropdown Component --}}
<div class="relative" x-data="{ dropdownOpen: false, toggleDropdown() { this.dropdownOpen = !this.dropdownOpen; }, closeDropdown() { this.dropdownOpen = false; } }" @click.away="closeDropdown()">
    <button
        class="relative flex items-center justify-center text-gray-500 transition-colors bg-white border border-gray-200 rounded-full hover:text-dark-900 h-11 w-11 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
        @click="toggleDropdown()" type="button">
        @if ($unreadCount > 0)
            <span class="absolute right-0 top-0.5 z-1 flex h-4 w-4 items-center justify-center rounded-full bg-orange-500 text-[10px] text-white">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
        @endif
        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M10.75 2.29248C10.75 1.87827 10.4143 1.54248 10 1.54248C9.58583 1.54248 9.25004 1.87827 9.25004 2.29248V2.83613C6.08266 3.20733 3.62504 5.9004 3.62504 9.16748V14.4591H3.33337C2.91916 14.4591 2.58337 14.7949 2.58337 15.2091C2.58337 15.6234 2.91916 15.9591 3.33337 15.9591H4.37504H15.625H16.6667C17.0809 15.9591 17.4167 15.6234 17.4167 15.2091C17.4167 14.7949 17.0809 14.4591 16.6667 14.4591H16.375V9.16748C16.375 5.9004 13.9174 3.20733 10.75 2.83613V2.29248ZM14.875 14.4591V9.16748C14.875 6.47509 12.6924 4.29248 10 4.29248C7.30765 4.29248 5.12504 6.47509 5.12504 9.16748V14.4591H14.875Z" fill=""/></svg>
    </button>

    <div x-show="dropdownOpen" x-transition
        class="absolute -right-[240px] mt-[17px] flex max-h-[480px] w-[350px] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark sm:w-[361px] lg:right-0"
        style="display: none;">
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-100 dark:border-gray-800">
            <h5 class="text-lg font-semibold text-gray-800 dark:text-white/90">Notificaciones</h5>
            <a href="{{ route('notifications.index') }}" class="text-xs text-brand-500">Ver todas</a>
        </div>

        <ul class="flex flex-col overflow-y-auto">
            @forelse ($notifications as $notification)
                <li>
                    <a href="{{ $notification->link ?? route('notifications.index') }}"
                        class="block rounded-lg border-b border-gray-100 p-3 hover:bg-gray-100 dark:border-gray-800 dark:hover:bg-white/5 {{ $notification->read_at ? 'opacity-70' : '' }}">
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $notification->title }}</p>
                        <p class="mt-1 text-xs text-gray-500">{{ $notification->message }}</p>
                        <p class="mt-1 text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
                    </a>
                </li>
            @empty
                <li class="p-4 text-center text-sm text-gray-500">Sin notificaciones</li>
            @endforelse
        </ul>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (!window.Swal) {
                    return;
                }

                const alerts = @json($notificationAlerts ?? []);
                if (!Array.isArray(alerts) || !alerts.length) {
                    return;
                }

                const sessionPrefix = 'notif-alert-shown-';
                const queue = [];

                alerts.forEach(function (alertItem) {
                    const key = sessionPrefix + String(alertItem.key || 'general');
                    if (sessionStorage.getItem(key)) {
                        return;
                    }

                    sessionStorage.setItem(key, '1');
                    queue.push({
                        icon: alertItem.icon || 'warning',
                        title: alertItem.title || 'Notificación',
                        text: alertItem.text || '',
                        confirmButtonText: 'Entendido'
                    });
                });

                if (!queue.length) {
                    return;
                }

                let chain = Promise.resolve();
                queue.forEach(function (modalConfig) {
                    chain = chain.then(function () {
                        return window.Swal.fire(modalConfig);
                    });
                });
            });
        </script>
    @endpush
@endonce
