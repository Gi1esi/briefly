<script setup>
import {toRef, toRefs} from "vue";

    const props = defineProps({
        modelValue: {type: Boolean, default: false},
        label: {type: String, default: ''},
        name: {type: String, default: ''},
        wrapperClass: {type: String, default: ''},
        inputClass: {type: String,  default: ''},
        required: {type: Boolean, default: false},
        disabled: {type: Boolean,  default: false},
    })

    const emit = defineEmits(['update:modelValue']);

    const checked = toRef(props, 'modelValue');

    function toggle(){
        if (props.disabled) return;
        const next = !checked.value
        emit("update:modelValue", next)
    }

    console.log('digest enabled', props.modelValue);

</script>

<template>
    <div :class="['flex items-center gap-3', wrapperClass]">
        <div class="flex items-center">
            <input type="checkbox"
                   :name="name || undefined"
                   :checked="checked"
                   :disabled="disabled"
                   :required="required"
                   class="sr-only"
            >
            <button
                :disabled="disabled"
                :aria-required="required"
                type="button"
                @click.prevent="toggle"
                @keydown.space.prevent="toggle"
                @keydown.enter.prevent="toggle"
                :class="['h-5 w-5 flex-shrink-0 rounded-sm border  flex items-center justify-center transition',
      modelValue ? 'border bg-primary' : 'border-gray-300 bg-white',
      disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer', inputClass]"

            >
                <svg v-if="modelValue" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-secondary" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414-1.414L8 11.172 4.707 7.879a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        <div>
            <span>{{ label }}</span>
        </div>
    </div>

</template>

