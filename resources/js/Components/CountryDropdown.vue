<script setup>

import {computed, onMounted, ref, watch} from "vue";

    const countries = ref ([]);
    const query = ref('');
    const open = ref(false);
    const selectedCountry = ref(null);

    const emit = defineEmits(["update:modelValue"]);
    const props = defineProps({
        modelValue: String,
    })

    onMounted(async ()=> {
        const res = await fetch('/countries/countries.json')
        countries.value = await res.json()
    });

    watch(
        () => props.modelValue,
        (val)=>{
        query.value = val;
    },
        {immediate: true}
    )

    const filteredCountries = computed(()=>{
        if (!query.value) return countries.value
        return countries.value.filter(c => c.name.toLowerCase().includes(query.value.toLowerCase()))
    });
    function selectCountry(country)
    {
        selectedCountry.value = country
        query.value = country.name
        open.value = false

        emit("update:modelValue", country.name)
    }

</script>

<template>
    <input type="text"
           v-model="query"
           placeholder="Select country"
           @focus="open = true"
    class="w-full p-2 rounded border"
    />
    <ul v-if="open" class="absolute bg-white w-[32%] border rounded max-h-48 overflow-y-auto z-10">
        <li v-for="country in filteredCountries"
            :key="country['country-code']"
            @click="selectCountry(country)"
            class="p-2 hover:bg-gray-100 cursor-pointer"
        >
            {{ country.name }}
        </li>
    </ul>


</template>
