@foreach ($records->where('parent_id', $parent_id) as $record)
    @php
        $recordAction = $getRecordAction($record);
        $recordKey = $getRecordKey($record);
        $recordUrl = $getRecordUrl($record);
        $current_chain = (empty($previous_chain) ? '' : $previous_chain . ':') . $record->id;
    @endphp

    <div
        class="p-3 my-2.5 fi-ta-ctn hover:bg-gray-400/10 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10">
        <div class="flex flex-grow items-center">
            <div class="flex items-center space-x-2 rtl:space-x-reverse flex-grow">
                @foreach ($columns as $column)
                    <div class="my-auto">
                        {{ $record->{$column->getName()} }}
                    </div>
                    {{--                <x-filament-tree-table::cell--}}
                    {{--                    @class([--}}
                    {{--                        'fi-table-individual-search-cell-' . str($column->getName())->camel()->kebab(),--}}
                    {{--                        'px-3 py-2',--}}
                    {{--                    ])--}}
                    {{--                >--}}
                    {{--                    @if ($column->isIndividuallySearchable())--}}
                    {{--                        <x-filament-tree-table::search-field--}}
                    {{--                            wire-model="tableColumnSearches.{{ $column->getName() }}"--}}
                    {{--                        />--}}
                    {{--                    @endif--}}
                    {{--                </x-filament-tree-table::cell>--}}
                @endforeach
            </div>
            <div class="flex-shrink-1">
                <x-filament-tree-table::actions
                    :actions="$actions"
                    :record="$record"
                    wrap="-sm"
                />

            </div>
        </div>
        @if ($record->children_count > 0)
            <div class="ltr:pl-12 rtl:pr-12">
                @include('filament-tree-table::records', [
                    'records' => $records,
                    'parent_id' => $record->id,
                    'level' => $level + 1,
                    'previous_chain' => $current_chain,
                ])
            </div>
        @endif
    </div>
@endforeach
