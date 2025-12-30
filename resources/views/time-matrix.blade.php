@php
    $hours = $getHours();
    $days = $getDays();
    $state = $getState();
    $showSelectAllHours = $getShowSelectAllHours();
    $showSelectAllDays = $getShowSelectAllDays();
    $statePath = $getStatePath();
    $isDisabled = $isDisabled();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        wire:ignore
        x-data="{
            matrix: @js($state ?: []),
            statePath: '{{ $statePath }}',

            init() {
                if (Object.keys(this.matrix).length === 0) {
                    @foreach(array_keys($days) as $dayKey)
                        this.matrix['{{ $dayKey }}'] = {};
                        @foreach($hours as $hour)
                            this.matrix['{{ $dayKey }}'][{{ $hour }}] = false;
                        @endforeach
                    @endforeach
                }

                @foreach(array_keys($days) as $dayKey)
                    if (!this.matrix['{{ $dayKey }}']) {
                        this.matrix['{{ $dayKey }}'] = {};
                    }
                    @foreach($hours as $hour)
                        if (this.matrix['{{ $dayKey }}'][{{ $hour }}] === undefined) {
                            this.matrix['{{ $dayKey }}'][{{ $hour }}] = false;
                        }
                    @endforeach
                @endforeach

                this.$watch('matrix', (value) => {
                    this.updateStateDebounced();
                });
            },

            updateStateDebounced: null,

            toggle(day, hour) {
                if (!this.matrix[day]) {
                    this.matrix[day] = {};
                }
                this.matrix[day][hour] = !this.matrix[day][hour];
            },

            isChecked(day, hour) {
                return this.matrix[day]?.[hour] || false;
            },

            updateState() {
                if (!this.updateStateDebounced) {
                    this.updateStateDebounced = this.debounce(() => {
                        this.$wire.set(this.statePath, this.matrix, false)
                    }, 100);
                }
                this.updateStateDebounced();
            },

            debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            },

            selectAllHours() {
                const allSelected = this.areAllSelected();
                @foreach(array_keys($days) as $dayKey)
                    @foreach($hours as $hour)
                        this.matrix['{{ $dayKey }}'][{{ $hour }}] = !allSelected;
                    @endforeach
                @endforeach
                this.updateState();
            },

            selectAllHoursForDay(day) {
                const hours = [@foreach($hours as $hour){{ $hour }},@endforeach];
                const allSelected = hours.every(hour => this.matrix[day][hour]);
                hours.forEach(hour => {
                    this.matrix[day][hour] = !allSelected;
                });
                this.updateState();
            },

            selectAllDaysForHour(hour) {
                const days = [@foreach(array_keys($days) as $dayKey)'{{ $dayKey }}',@endforeach];
                const allSelected = days.every(day => this.matrix[day][hour]);
                days.forEach(day => {
                    this.matrix[day][hour] = !allSelected;
                });
                this.updateState();
            },

            areAllSelected() {
                const days = [@foreach(array_keys($days) as $dayKey)'{{ $dayKey }}',@endforeach];
                const hours = [@foreach($hours as $hour){{ $hour }},@endforeach];
                return days.every(day =>
                    hours.every(hour => this.matrix[day][hour])
                );
            },

            getSelectedCount() {
                let count = 0;
                Object.keys(this.matrix).forEach(day => {
                    Object.keys(this.matrix[day]).forEach(hour => {
                        if (this.matrix[day][hour]) count++;
                    });
                });
                return count;
            }
        }"
        x-init="init()"
        class="space-y-4"
    >
        <!-- Matrix Table -->
        <div class="overflow-x-auto rounded-lg border border-gray-300 dark:border-gray-600">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800">
                        <th class="border border-gray-300 dark:border-gray-600 p-2 text-sm font-medium text-gray-700 dark:text-white sticky left-0 bg-gray-50 dark:bg-gray-800 z-10">
                            Day
                        </th>

                        @foreach($hours as $hour)
                            <th class="border border-gray-300 dark:border-gray-600 p-2 text-sm font-medium text-gray-700 dark:text-white min-w-[50px]">
                                {{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}
                            </th>
                        @endforeach

                        @if($showSelectAllHours)
                            <th class="border border-gray-300 dark:border-gray-600 p-2 text-sm font-medium sticky right-0 bg-gray-50 dark:bg-gray-800">
                                <button
                                    type="button"
                                    @click="selectAllHours()"
                                    class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium"
                                    @disabled($isDisabled)
                                >
                                    All Hours
                                </button>
                            </th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($days as $dayKey => $dayLabel)
                        <tr>
                            <td class="border border-gray-300 dark:border-gray-600 p-2 text-sm font-medium bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-white sticky left-0 z-10">
                                {{ $dayLabel }}
                            </td>

                            @foreach($hours as $hour)
                                <td class="border border-gray-300 dark:border-gray-600 p-0 text-center">
                                    <label class="flex items-center justify-center h-full w-full cursor-pointer hover:bg-primary-50 p-3 transition-colors">
                                        <input
                                            type="checkbox"
                                            x-model="matrix['{{ $dayKey }}'][{{ $hour }}]"
                                            @change="updateState()"
                                            @disabled($isDisabled)
                                            class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:checked:bg-primary-600 dark:checked:border-primary-600 disabled:opacity-50 disabled:cursor-not-allowed"
                                        />
                                    </label>
                                </td>
                            @endforeach

                            @if($showSelectAllHours)
                                <td class="border border-gray-300 dark:border-gray-600 p-2 text-center sticky right-0 bg-white dark:bg-gray-900">
                                    <button
                                        type="button"
                                        @click="selectAllHoursForDay('{{ $dayKey }}')"
                                        class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium px-2 py-1 rounded hover:bg-primary-50"
                                        @disabled($isDisabled)
                                    >
                                        Select All
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @endforeach

                    @if($showSelectAllDays)
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <td class="border border-gray-300 dark:border-gray-600 p-2 text-sm font-medium text-gray-700 dark:text-white sticky left-0 bg-gray-50 dark:bg-gray-800 z-10">
                                All Days
                            </td>

                            @foreach($hours as $hour)
                                <td class="border border-gray-300 dark:border-gray-600 p-2 text-center">
                                    <button
                                        type="button"
                                        @click="selectAllDaysForHour({{ $hour }})"
                                        class="text-primary-600 hover:text-primary-700 dark:text-primary-400 font-bold text-base w-full h-full py-1"
                                        @disabled($isDisabled)
                                    >
                                        ✓
                                    </button>
                                </td>
                            @endforeach

                            @if($showSelectAllHours)
                                <td class="border border-gray-300 dark:border-gray-600 sticky right-0 bg-gray-50 dark:bg-gray-800"></td>
                            @endif
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Summary -->
        <div class="flex items-center justify-between text-sm">
            <span class="text-gray-600 dark:text-gray-400">
                <span x-text="getSelectedCount()"></span> Selected
            </span>

            @if($showSelectAllHours)
                <button
                    type="button"
                    @click="Object.keys(matrix).forEach(day => { Object.keys(matrix[day]).forEach(hour => { matrix[day][hour] = false; }); }); updateState();"
                    class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 underline"
                    @disabled($isDisabled)
                >
                    Reset
                </button>
            @endif
        </div>
    </div>
</x-dynamic-component>
