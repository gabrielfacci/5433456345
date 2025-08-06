<?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => $getFieldWrapperView()] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\DynamicComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['field' => $field]); ?>
    <?php ($codeFieldId = 'code-field-'.\Illuminate\Support\Str::slug($getId())); ?>

    <style>
        #<?php echo e($codeFieldId); ?> .cm-scroller {
            max-height: <?php echo e($getMaxHeight()); ?> !important;
        }

        #<?php echo e($codeFieldId); ?> .cm-scroller, .cm-gutter {
            min-height: <?php echo e($getMinHeight()); ?> !important;
        }
    </style>

    <div x-load-css="[<?php echo \Illuminate\Support\Js::from(\Filament\Support\Facades\FilamentAsset::getStyleHref('filament-code-field', package: 'creagia/filament-code-field'))->toHtml() ?>]"
         x-load-js="[<?php echo \Illuminate\Support\Js::from(\Filament\Support\Facades\FilamentAsset::getScriptSrc('filament-code-field', package: 'creagia/filament-code-field'))->toHtml() ?>]"
         x-data="filamentCodeField({
            state: $wire.<?php echo e($applyStateBindingModifiers('entangle(\''.$getStatePath().'\')')); ?>,
            displayMode: <?php echo e($field->displayMode ? 1 : 0); ?>,
            language: '<?php echo e($field->language); ?>',
            disabled: <?php echo e($field->isDisabled() ? 1 : 0); ?>,
            withLineNumbers: <?php echo e($field->lineNumbers ? 1 : 0); ?>,
            withAutocompletion: <?php echo e($field->autocompletion ? 1 : 0); ?>

        })"
    >
        <div id="<?php echo e($codeFieldId); ?>" wire:ignore>
            <div x-ref="codeBlock"
                 class="fi-input-wrapper !block !caret-black flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white focus-within:ring-2 dark:!caret-white dark:bg-white/5 ring-gray-950/10 focus-within:ring-primary-600 dark:ring-white/20 dark:focus-within:ring-primary-500 fi-fo-text-input overflow-hidden"
            ></div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php /**PATH /home/raspoupixbr/htdocs/raspoupixbr.site/vendor/creagia/filament-code-field/src/../resources/views/code-field.blade.php ENDPATH**/ ?>