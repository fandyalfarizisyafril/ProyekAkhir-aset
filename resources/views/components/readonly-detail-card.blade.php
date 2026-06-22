@props(['title', 'rows' => []])

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
    <h3 class="text-base font-bold text-slate-800 tracking-tight mb-4">{{ $title }}</h3>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <tbody class="divide-y divide-slate-100 text-xs">
                @foreach($rows as $label => $value)
                    <tr>
                        <td class="py-3 px-4 bg-slate-50 text-slate-400 font-bold uppercase tracking-wider w-44">
                            {{ $label }}
                        </td>
                        <td class="py-3 px-4 text-slate-700 font-semibold">
                            {{ filled($value) ? $value : '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
