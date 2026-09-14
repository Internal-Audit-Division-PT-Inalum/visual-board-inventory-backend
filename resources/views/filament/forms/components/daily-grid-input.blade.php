<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <style>
        .daily-grid-container {
            border: 1px solid rgba(107, 114, 128, 0.2);
            border-radius: 0.75rem;
            overflow: hidden;
            background-color: #ffffff;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);
        }
        .dark .daily-grid-container {
            background-color: #111827;
            border-color: rgba(75, 85, 99, 0.4);
        }
        .daily-grid-scroll {
            overflow-x: auto;
            overflow-y: hidden;
            display: flex;
            padding-bottom: 4px;
        }
        /* Sleek scrollbar */
        .daily-grid-scroll::-webkit-scrollbar {
            height: 8px;
        }
        .daily-grid-scroll::-webkit-scrollbar-track {
            background: rgba(243, 244, 246, 0.5);
            border-radius: 4px;
        }
        .dark .daily-grid-scroll::-webkit-scrollbar-track {
            background: rgba(31, 41, 55, 0.5);
        }
        .daily-grid-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .daily-grid-scroll::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .dark .daily-grid-scroll::-webkit-scrollbar-thumb {
            background: #475569;
        }
        .dark .daily-grid-scroll::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }
        
        .daily-grid-col {
            display: flex;
            flex-direction: column;
            min-width: 44px;
            border-right: 1px solid rgba(107, 114, 128, 0.2);
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }
        .daily-grid-col:last-child {
            border-right: none;
        }
        .daily-grid-col:hover {
            background-color: rgba(243, 244, 246, 0.8);
        }
        .dark .daily-grid-col:hover {
            background-color: rgba(31, 41, 55, 0.8);
        }
        
        .daily-grid-header {
            text-align: center;
            padding: 0.5rem 0;
            font-size: 0.75rem;
            font-weight: 600;
            color: #4b5563;
            background-color: rgba(243, 244, 246, 0.5);
            border-bottom: 1px solid rgba(107, 114, 128, 0.2);
        }
        .dark .daily-grid-header {
            color: #9ca3af;
            background-color: rgba(31, 41, 55, 0.5);
        }
        
        .daily-grid-cell {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 48px;
            font-size: 1.25rem;
            font-weight: 700;
            user-select: none;
        }
        
        /* Status Colors */
        .status-null { color: #d1d5db; }
        .dark .status-null { color: #4b5563; }
        
        .status-rencana { color: #6b7280; background-color: #f3f4f6; }
        .dark .status-rencana { color: #9ca3af; background-color: #374151; }
        
        .status-ok_tanpa_5r { color: #16a34a; background-color: #f0fdf4; }
        .dark .status-ok_tanpa_5r { color: #4ade80; background-color: rgba(22, 163, 74, 0.2); }
        
        .status-ok_dengan_5r { color: #d97706; background-color: #fffbeb; }
        .dark .status-ok_dengan_5r { color: #fbbf24; background-color: rgba(217, 119, 6, 0.2); }
        
        .status-abnormal { color: #dc2626; background-color: #fef2f2; }
        .dark .status-abnormal { color: #f87171; background-color: rgba(220, 38, 38, 0.2); }

        .daily-grid-legend {
            padding: 1rem;
            background-color: rgba(249, 250, 251, 0.5);
            border-top: 1px solid rgba(107, 114, 128, 0.2);
            font-size: 0.8125rem;
            color: #4b5563;
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            align-items: center;
        }
        .dark .daily-grid-legend {
            background-color: rgba(17, 24, 39, 0.5);
            color: #9ca3af;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: default;
        }
        .legend-icon {
            font-size: 1.125rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 4px;
        }
    </style>

    <div x-data="{
            state: $wire.$entangle('{{ $getStatePath() }}'),
            cycleStatus(day) {
                if (!this.state) {
                    this.state = {};
                }
                if (Array.isArray(this.state)) {
                    let temp = {};
                    this.state = temp;
                }
                
                const statuses = [null, 'rencana', 'ok_tanpa_5r', 'ok_dengan_5r', 'abnormal'];
                const current = this.state[day.toString()] || null;
                const nextIdx = (statuses.indexOf(current) + 1) % statuses.length;
                
                if (statuses[nextIdx] === null) {
                    delete this.state[day.toString()];
                } else {
                    this.state[day.toString()] = statuses[nextIdx];
                }
                
                this.state = { ...this.state };
            },
            getSymbol(day) {
                const status = (this.state && this.state[day.toString()]) ? this.state[day.toString()] : null;
                switch (status) {
                    case 'rencana': return '○';
                    case 'ok_tanpa_5r': return '◎';
                    case 'ok_dengan_5r': return '△';
                    case 'abnormal': return '☒';
                    default: return '-';
                }
            },
            getStatusClass(day) {
                const status = (this.state && this.state[day.toString()]) ? this.state[day.toString()] : 'null';
                return 'status-' + status;
            }
        }"
        class="daily-grid-container"
    >
        <div class="daily-grid-scroll">
            <template x-for="day in 31" :key="day">
                <div @click="cycleStatus(day)" class="daily-grid-col" :class="getStatusClass(day)">
                    <div class="daily-grid-header" x-text="day"></div>
                    <div class="daily-grid-cell">
                        <span x-text="getSymbol(day)"></span>
                    </div>
                </div>
            </template>
        </div>
        
        <div class="daily-grid-legend">
            <div style="font-weight: 600; color: #111827;" class="dark:text-white flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15.5 3H5a2 2 0 0 0-2 2v14c0 1.1.9 2 2 2h14a2 2 0 0 0 2-2V8.5L15.5 3Z"/><path d="M14 3v7h7"/><path d="m9 15 2 2 4-4"/></svg>
                Panduan Pengisian 
                <span style="font-weight: 400; font-size: 0.75rem; opacity: 0.8;">(Klik pada kolom angka/ikon untuk mengubah status)</span>
            </div>
            
            <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-left: auto;">
                <div class="legend-item"><div class="legend-icon status-rencana">○</div> Rencana</div>
                <div class="legend-item"><div class="legend-icon status-ok_tanpa_5r">◎</div> OK Tanpa 5R</div>
                <div class="legend-item"><div class="legend-icon status-ok_dengan_5r">△</div> OK Dengan 5R</div>
                <div class="legend-item"><div class="legend-icon status-abnormal">☒</div> Abnormal</div>
            </div>
        </div>
    </div>
</x-dynamic-component>
