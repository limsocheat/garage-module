<template>
	<div class="tw-grid tw-gap-4">
		<vee-field name="customer_id" v-slot="{ errors }">
			<div class="tw-space-y-2">
				<Label>
					{{ __('Customer') }}
					<span class="tw-text-red-500 tw-ml-1">*</span>
				</Label>
				<Popover v-model:open="customerOpen">
					<PopoverTrigger as-child>
						<Button variant="outline" role="combobox" :aria-expanded="customerOpen" class="tw-w-full tw-justify-between tw-font-normal">
							<div class="tw-flex tw-items-center tw-gap-2">
								<User class="tw-h-4 tw-w-4 tw-text-muted-foreground" />
								{{ selectedCustomer?.name || __('Select customer...') }}
							</div>
							<ChevronsUpDown class="tw-ml-2 tw-h-4 tw-w-4 tw-shrink-0 tw-opacity-50" />
						</Button>
					</PopoverTrigger>
					<PopoverContent class="tw-w-full tw-p-0">
						<Command>
							<CommandInput :placeholder="__('Search customer...')" v-model="customerSearch" />
							<CommandEmpty>{{ __('No customer found.') }}</CommandEmpty>
							<CommandList>
								<CommandGroup>
									<CommandItem
										v-for="customer in filteredCustomers"
										:key="customer.id"
										:value="customer.id"
										@select="() => { item.customer_id = customer.id; customerOpen = false; }"
									>
										<Check
											:class="{
												'tw-opacity-100': item.customer_id === customer.id,
												'tw-opacity-0': item.customer_id !== customer.id
											}"
											class="tw-mr-2 tw-h-4 tw-w-4"
										/>
										{{ customer.name }}
									</CommandItem>
								</CommandGroup>
							</CommandList>
						</Command>
					</PopoverContent>
				</Popover>
				<div v-if="errors.length" class="tw-text-sm tw-text-red-500">
					<div v-for="error in errors" :key="error">{{ error }}</div>
				</div>
			</div>
		</vee-field>

		<vee-field name="plate_number" v-slot="{ field: { value, ...field }, errors }">
			<div class="tw-space-y-2">
				<Label>{{ __('Plate Number') }}</Label>
				<div class="tw-relative">
					<Hash class="tw-absolute tw-left-3 tw-top-2.5 tw-h-4 tw-w-4 tw-text-muted-foreground" />
					<Input v-bind="field" v-model="item.plate_number" :placeholder="__('e.g., 2ABC-1234')" class="tw-pl-9" />
				</div>
				<div v-if="errors.length" class="tw-text-sm tw-text-red-500">
					<div v-for="error in errors" :key="error">{{ error }}</div>
				</div>
			</div>
		</vee-field>

		<div class="tw-grid tw-gap-4 md:tw-grid-cols-2">
			<vee-field name="make" v-slot="{ field: { value, ...field }, errors }">
				<div class="tw-space-y-2">
					<Label>{{ __('Make') }}</Label>
					<Input v-bind="field" v-model="item.make" :placeholder="__('e.g., Toyota')" />
					<div v-if="errors.length" class="tw-text-sm tw-text-red-500">
						<div v-for="error in errors" :key="error">{{ error }}</div>
					</div>
				</div>
			</vee-field>

			<vee-field name="model" v-slot="{ field: { value, ...field }, errors }">
				<div class="tw-space-y-2">
					<Label>{{ __('Model') }}</Label>
					<Input v-bind="field" v-model="item.model" :placeholder="__('e.g., Camry')" />
					<div v-if="errors.length" class="tw-text-sm tw-text-red-500">
						<div v-for="error in errors" :key="error">{{ error }}</div>
					</div>
				</div>
			</vee-field>
		</div>

		<div class="tw-grid tw-gap-4 md:tw-grid-cols-2">
			<vee-field name="color" v-slot="{ field: { value, ...field }, errors }">
				<div class="tw-space-y-2">
					<Label>{{ __('Color') }}</Label>
					<Input v-bind="field" v-model="item.color" :placeholder="__('e.g., White')" />
					<div v-if="errors.length" class="tw-text-sm tw-text-red-500">
						<div v-for="error in errors" :key="error">{{ error }}</div>
					</div>
				</div>
			</vee-field>

			<vee-field name="year" v-slot="{ field: { value, ...field }, errors }">
				<div class="tw-space-y-2">
					<Label>{{ __('Year') }}</Label>
					<Input v-bind="field" v-model="item.year" type="number" :placeholder="__('e.g., 2021')" />
					<div v-if="errors.length" class="tw-text-sm tw-text-red-500">
						<div v-for="error in errors" :key="error">{{ error }}</div>
					</div>
				</div>
			</vee-field>
		</div>

		<vee-field name="notes" v-slot="{ field: { value, ...field }, errors }">
			<div class="tw-space-y-2">
				<Label>{{ __('Notes') }}</Label>
				<Textarea v-bind="field" v-model="item.notes" class="tw-min-h-[80px]" :placeholder="__('e.g., scratch on rear bumper')" />
				<div v-if="errors.length" class="tw-text-sm tw-text-red-500">
					<div v-for="error in errors" :key="error">{{ error }}</div>
				</div>
			</div>
		</vee-field>

		<div class="tw-flex tw-items-center tw-space-x-2">
			<input
				type="checkbox"
				id="active"
				v-model="item.active"
				class="tw-h-4 tw-w-4 tw-rounded tw-border-gray-300"
			/>
			<Label htmlFor="active" class="tw-text-sm tw-font-normal tw-cursor-pointer">
				{{ __('Active') }}
			</Label>
		</div>
	</div>
</template>

<script setup>
	import { ref, computed, onMounted } from "vue";
	import { Label } from "@/Components/ui/label";
	import { Input } from "@/Components/ui/input";
	import { Textarea } from "@/Components/ui/textarea";
	import { Button } from "@/Components/ui/button";
	import { Popover, PopoverContent, PopoverTrigger } from "@/Components/ui/popover";
	import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from "@/Components/ui/command";
	import { Check, ChevronsUpDown, User, Hash } from "lucide-vue-next";

	const props = defineProps({
		modelValue: Object,
	});

	const emit = defineEmits(['update:modelValue']);

	const customers = ref([]);
	const customerOpen = ref(false);
	const customerSearch = ref("");

	const item = computed({
		get() {
			return props.modelValue;
		},
		set(val) {
			emit("update:modelValue", val);
		},
	});

	const selectedCustomer = computed(() => {
		return customers.value.find(c => c.id === item.value?.customer_id);
	});

	const filteredCustomers = computed(() => {
		if (!customerSearch.value) return customers.value;
		return customers.value.filter(customer =>
			customer.name.toLowerCase().includes(customerSearch.value.toLowerCase())
		);
	});

	const getCustomers = async () => {
		try {
			const { data } = await axios.get(route("dashboard.customer.data.customers"));
			if (data.success) {
				customers.value = data.data;
			}
		} catch (error) {
			console.error("Failed to fetch customers:", error);
		}
	};

	onMounted(async () => {
		await getCustomers();
	});
</script>
