<div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($items as $label => $value)
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-300">{{ $label }}:</span>
                <span class="text-sm text-white font-semibold">{{ $value }}</span>
            </div>
        @endforeach
    </div>
</div> 