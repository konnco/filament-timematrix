@php
    $hours = $getHours();
    $days = $getDays();
    $state = $getState();
    $showSelectAllHours = $getShowSelectAllHours();
    $showSelectAllDays = $getShowSelectAllDays();
    $statePath = $getStatePath();
    $isDisabled = $isDisabled();

    $daysKeys = array_keys($days);
    $daysKeysJson = json_encode($daysKeys);
    $hoursJson = json_encode($hours);
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        wire:ignore
        x-data="{
            matrix: @js($state ?: []),
            statePath: '{{ $statePath }}',
            days: @js($daysKeys),
            hours: @js($hours),

            init() {
                this.initializeMatrix();
                this.$watch('matrix', () => {
                    this.debouncedUpdate();
                });
            },

            initializeMatrix() {
                if (Object.keys(this.matrix).length === 0) {
                    this.days.forEach(day => {
                        this.matrix[day] = {};
                        this.hours.forEach(hour => {
                            this.matrix[day][hour] = false;
                        });
                    });
                } else {
                    this.days.forEach(day => {
                        if (!this.matrix[day]) {
                            this.matrix[day] = {};
                        }
                        this.hours.forEach(hour => {
                            if (this.matrix[day][hour] === undefined) {
                                this.matrix[day][hour] = false;
                            }
                        });
                    });
                }
            },

            debouncedUpdate: null,

            toggle(day, hour) {
                if (!this.matrix[day]) {
                    this.matrix[day] = {};
                }
                this.matrix[day][hour] = !this.matrix[day][hour];
                this.debouncedUpdate();
            },

            isChecked(day, hour) {
                return this.matrix[day]?.[hour] || false;
            },

            updateWire() {
                this.$wire.set(this.statePath, this.matrix, false);
            },

            debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            },

            selectAllHours() {
                const allSelected = this.areAllSelected();
                this.days.forEach(day => {
                    this.hours.forEach(hour => {
                        this.matrix[day][hour] = !allSelected;
                    });
                });
                this.updateWire();
            },

            selectAllHoursForDay(day) {
                const allSelected = this.hours.every(hour => this.matrix[day][hour]);
                this.hours.forEach(hour => {
                    this.matrix[day][hour] = !allSelected;
                });
                this.updateWire();
            },

            selectAllDaysForHour(hour) {
                const allSelected = this.days.every(day => this.matrix[day][hour]);
                this.days.forEach(day => {
                    this.matrix[day][hour] = !allSelected;
                });
                this.updateWire();
            },

            areAllSelected() {
                return this.days.every(day =>
                    this.hours.every(hour => this.matrix[day][hour])
                );
            },

            getSelectedCount() {
                let count = 0;
                for (const day of this.days) {
                    for (const hour of this.hours) {
                        if (this.matrix[day][hour]) count++;
                    }
                }
                return count;
            },

            resetAll() {
                this.days.forEach(day => {
                    this.hours.forEach(hour => {
                        this.matrix[day][hour] = false;
                    });
                });
                this.updateWire();
            }
        }"
        x-init="debouncedUpdate = debounce(updateWire, 150)"
        class="timematrix-container"
    >
        <style>
            :root {
                --tm-border-light: #e5e7eb;
                --tm-border-dark: #374151;
                --tm-bg-light: #ffffff;
                --tm-bg-dark: #111827;
                --tm-bg-header-light: #f9fafb;
                --tm-bg-header-dark: #1f2937;
                --tm-text-light: #111827;
                --tm-text-dark: #f9fafb;
                --tm-text-muted-light: #6b7280;
                --tm-text-muted-dark: #9ca3af;
                --tm-primary: #3b82f6;
                --tm-primary-hover: #2563eb;
                --tm-accent: #ea580c;
                --tm-accent-light: #fb923c;
            }

            .timematrix-container {
                margin-bottom: 1rem;
            }

            .timematrix-wrapper {
                overflow-x: auto;
                border-radius: 0.5rem;
            }

            .timematrix-table {
                width: 100%;
                border-collapse: collapse;
                border: 1px solid var(--tm-border-light);
                background: var(--tm-bg-light);
                color: var(--tm-text-light);
            }

            .dark .timematrix-table {
                border-color: var(--tm-border-dark);
                background: var(--tm-bg-dark);
                color: var(--tm-text-dark);
            }

            .timematrix-table th,
            .timematrix-table td {
                border: 1px solid var(--tm-border-light);
                padding: 0.625rem 0.75rem;
                font-size: 0.875rem;
                line-height: 1.25rem;
                background-color: var(--tm-bg-light);
                color: var(--tm-text-light);
                transition: background-color 0.15s ease;
            }

            .dark .timematrix-table th,
            .dark .timematrix-table td {
                border-color: var(--tm-border-dark);
                background-color: var(--tm-bg-dark);
                color: var(--tm-text-dark);
            }

            .timematrix-table th {
                background-color: var(--tm-bg-header-light);
                font-weight: 500;
                color: var(--tm-text-muted-light);
                text-align: center;
            }

            .dark .timematrix-table th {
                background-color: var(--tm-bg-header-dark);
                color: var(--tm-text-dark);
            }

            .timematrix-table th:first-child {
                text-align: left;
                position: sticky;
                left: 0;
                z-index: 10;
            }

            .timematrix-table th.sticky-right {
                position: sticky;
                right: 0;
                z-index: 10;
            }

            .timematrix-table tbody td:first-child {
                font-weight: 500;
                text-align: left;
                position: sticky;
                left: 0;
                z-index: 5;
            }

            .timematrix-table tbody td.sticky-right {
                text-align: center;
                font-size: 0.5rem;
                position: sticky;
                right: 0;
                z-index: 5;
            }

            .timematrix-checkbox-cell {
                padding: 0.625rem;
                text-align: center;
            }

            .timematrix-checkbox-cell:hover {
                background-color: rgba(59, 130, 246, 0.05);
            }

            .dark .timematrix-checkbox-cell:hover {
                background-color: rgba(59, 130, 246, 0.1);
            }

            .timematrix-checkbox-label {
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                min-height: 1.5rem;
            }

            .timematrix-checkbox {
                width: 1.125rem;
                height: 1.125rem;
                border-radius: 0.25rem;
                border: 2px solid var(--tm-text-muted-light);
                cursor: pointer;
                appearance: none;
                background-color: var(--tm-bg-light);
                position: relative;
                transition: all 0.15s ease;
            }

            .dark .timematrix-checkbox {
                border-color: var(--tm-text-muted-dark);
                background-color: #374151;
            }

            .timematrix-checkbox:hover {
                border-color: var(--tm-primary);
            }

            .timematrix-checkbox:checked {
                background-color: var(--tm-primary);
                border-color: var(--tm-primary);
            }

            .timematrix-checkbox:checked::after {
                content: '';
                position: absolute;
                left: 0.3125rem;
                top: 0.0625rem;
                width: 0.375rem;
                height: 0.625rem;
                border: solid white;
                border-width: 0 2px 2px 0;
                transform: rotate(45deg);
            }

            .timematrix-checkbox:focus {
                outline: 2px solid var(--tm-primary);
                outline-offset: 2px;
            }

            .timematrix-checkbox:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }

            .timematrix-button {
                font-size: 0.875rem;
                font-weight: 500;
                color: var(--tm-accent);
                background: none;
                border: none;
                cursor: pointer;
                padding: 0;
                text-decoration: none;
                transition: all 0.15s ease;
            }

            .dark .timematrix-button {
                color: var(--tm-accent-light);
            }

            .timematrix-button:hover:not(:disabled) {
                text-decoration: underline;
                opacity: 0.8;
            }

            .timematrix-button:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }

            .timematrix-button-header {
                color: var(--tm-primary-hover);
                border: 1px solid var(--tm-primary-hover);
                border-radius: 0.375rem;
                padding: 0.375rem 0.75rem;
                font-size: 0.875rem;
                background-color: var(--tm-bg-light);
                font-weight: 500;
                transition: all 0.15s ease;
            }

            .dark .timematrix-button-header {
                color: var(--tm-primary);
                border-color: var(--tm-primary);
                background-color: var(--tm-bg-dark);
            }

            .timematrix-button-header:hover:not(:disabled) {
                background-color: #eff6ff;
                text-decoration: none;
                transform: translateY(-1px);
            }

            .dark .timematrix-button-header:hover:not(:disabled) {
                background-color: rgba(59, 130, 246, 0.1);
            }

            .timematrix-button-checkmark {
                font-weight: 600;
                font-size: 1.25rem;
                color: var(--tm-accent);
                padding: 0;
                line-height: 1;
            }

            .dark .timematrix-button-checkmark {
                color: var(--tm-accent-light);
            }

            .timematrix-all-days-row td:first-child {
                font-weight: 600;
            }

            .timematrix-summary {
                display: flex;
                align-items: center;
                justify-content: space-between;
                font-size: 0.875rem;
                margin-top: 0.75rem;
                padding: 0.5rem 0;
                color: var(--tm-text-light);
            }

            .dark .timematrix-summary {
                color: var(--tm-text-dark);
            }

            .timematrix-summary-count {
                color: var(--tm-text-muted-light);
                font-weight: 500;
            }

            .dark .timematrix-summary-count {
                color: var(--tm-text-muted-dark);
            }

            .timematrix-summary-count span {
                font-weight: 600;
                color: var(--tm-primary);
            }

            .timematrix-reset {
                color: var(--tm-text-muted-light);
                text-decoration: underline;
                background: none;
                border: none;
                cursor: pointer;
                font-size: 0.875rem;
                transition: color 0.15s ease;
            }

            .dark .timematrix-reset {
                color: var(--tm-text-muted-dark);
            }

            .timematrix-reset:hover:not(:disabled) {
                color: var(--tm-text-light);
            }

            .dark .timematrix-reset:hover:not(:disabled) {
                color: var(--tm-text-dark);
            }

            @media (max-width: 640px) {
                .timematrix-table th,
                .timematrix-table td {
                    padding: 0.5rem;
                    font-size: 0.8125rem;
                }

                .timematrix-checkbox {
                    width: 1rem;
                    height: 1rem;
                }

                .timematrix-button-header {
                    padding: 0.25rem 0.5rem;
                    font-size: 0.8125rem;
                }
            }
        </style>

        <!-- Matrix Table -->
        <div class="timematrix-wrapper">
            <table class="timematrix-table">
                <thead>
                <tr>
                    <th>Day</th>
                    @foreach($hours as $hour)
                        <th>{{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}</th>
                    @endforeach
                    @if($showSelectAllHours)
                        <th class="sticky-right">
                            <button
                                type="button"
                                @click="selectAllHours()"
                                class="timematrix-button timematrix-button-header"
                                :disabled="@js($isDisabled)"
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
                        <td>{{ $dayLabel }}</td>
                        @foreach($hours as $hour)
                            <td class="timematrix-checkbox-cell">
                                <label class="timematrix-checkbox-label">
                                    <input
                                        type="checkbox"
                                        x-model="matrix['{{ $dayKey }}'][{{ $hour }}]"
                                        :disabled="@js($isDisabled)"
                                        class="timematrix-checkbox"
                                    />
                                </label>
                            </td>
                        @endforeach
                        @if($showSelectAllHours)
                            <td class="sticky-right">
                                <button
                                    type="button"
                                    @click="selectAllHoursForDay('{{ $dayKey }}')"
                                    class="timematrix-button"
                                    :disabled="@js($isDisabled)"
                                >
                                    Select All
                                </button>
                            </td>
                        @endif
                    </tr>
                @endforeach
                @if($showSelectAllDays)
                    <tr class="timematrix-all-days-row">
                        <td>All Days</td>
                        @foreach($hours as $hour)
                            <td style="text-align: center; padding: 0.625rem;">
                                <button
                                    type="button"
                                    @click="selectAllDaysForHour({{ $hour }})"
                                    class="timematrix-button timematrix-button-checkmark"
                                    :disabled="@js($isDisabled)"
                                >
                                    ✓
                                </button>
                            </td>
                        @endforeach
                        @if($showSelectAllHours)
                            <td class="sticky-right"></td>
                        @endif
                    </tr>
                @endif
                </tbody>
            </table>
        </div>

        <!-- Summary -->
        <div class="timematrix-summary">
            <span class="timematrix-summary-count">
                <span x-text="getSelectedCount()">0</span> Selected
            </span>
            @if($showSelectAllHours)
                <button
                    type="button"
                    @click="resetAll()"
                    class="timematrix-reset"
                    :disabled="@js($isDisabled)"
                >
                    Reset All
                </button>
            @endif
        </div>
    </div>
</x-dynamic-component>
