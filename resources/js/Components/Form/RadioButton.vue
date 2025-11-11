<script setup>
    const props = defineProps({
        modelValue: String,
        wrapperClass: {type: String, default: ''},
        InputClass: {type: String,  default: ''},
        label: {type: String,  default: ''},
        name: {type: String, default: 'digest_frequency'},
        disabled: {type: Boolean, default: false},
        options: {type: Array,  default: ()=> [
                { label: 'Daily', value: 'daily' },
                { label: 'Weekly', value: 'weekly' },
                { label: 'Monthly', value: 'monthly' },
            ]},
        digest_enabled: {type: Boolean, default:false},
    })

    const emit = defineEmits(["update:modelValue"]);
    function selectOption(value){
        if (props.disabled) return;

        emit("update:modelValue", value)
    }

    console.log('digest enabled', props.digest_enabled);
</script>

<template>
    <div class="flex flex-col gap-2" v-if="digest_enabled" >
        <div
            :class="['flex items-center gap-2', wrapperClass]"
            v-for="option in props.options"
            :key="option.value"
        >
            <input type="radio"
                   :name="props.name"
                   :value="option.value"
                   :checked="option.value === modelValue"
                   @change="selectOption(option.value)"
                   :disabled="props.disabled"
                   class="cursor-pointer"
            />
            <label :for="option.value" class="cursor-pointer  fs-sm">{{ option.label }}</label>
        </div>
    </div>

</template>

