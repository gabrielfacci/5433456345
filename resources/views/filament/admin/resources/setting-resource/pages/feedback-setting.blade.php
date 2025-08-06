<x-filament-panels::page>
    <x-filament::form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                Salvar
            </x-filament::button>
        </div>
    </x-filament::form>
</x-filament-panels::page> 