<template>
	<inertia-head :title="__('Delete Vehicle')" />

	<vee-form :validation-schema="schema" @submit="submit" v-slot="{ setErrors }">
		<sakal-form-modal size="lg" :loading="item.processing">
			<template #title>
				<div class="tw-flex tw-items-center tw-gap-3">
					<div class="tw-flex tw-h-10 tw-w-10 tw-items-center tw-justify-center tw-rounded-full tw-bg-destructive/10">
						<AlertTriangle class="tw-h-5 tw-w-5 tw-text-destructive" />
					</div>
					<div>
						<h3 class="tw-text-lg tw-font-semibold">{{ __("Delete Vehicle") }}</h3>
						<p class="tw-text-sm tw-text-muted-foreground">{{ __("This action cannot be undone") }}</p>
					</div>
				</div>
			</template>

			<div class="tw-space-y-6">
				<!-- Vehicle Information -->
				<div class="tw-bg-muted/30 tw-rounded-lg tw-p-4">
					<div class="tw-flex tw-items-center tw-gap-3">
						<div class="tw-flex tw-h-12 tw-w-12 tw-items-center tw-justify-center tw-rounded-full tw-bg-muted">
							<Car class="tw-h-6 tw-w-6 tw-text-muted-foreground" />
						</div>
						<div>
							<h4 class="tw-font-semibold">{{ [vehicle.make, vehicle.model].filter(Boolean).join(' ') || __('Vehicle') }}</h4>
							<div class="tw-flex tw-gap-2 tw-mt-1">
								<Badge v-if="vehicle.plate_number" variant="secondary" class="tw-text-xs">
									{{ vehicle.plate_number }}
								</Badge>
								<Badge
									:variant="vehicle.active ? 'default' : 'secondary'"
									class="tw-text-xs"
								>
									{{ vehicle.active ? __('Active') : __('Inactive') }}
								</Badge>
							</div>
						</div>
					</div>
				</div>

				<!-- Vehicle Details -->
				<div class="tw-border tw-rounded-md tw-p-3 tw-bg-muted/30">
					<p class="tw-text-sm tw-font-medium tw-mb-2">{{ __("Vehicle Details:") }}</p>
					<div class="tw-text-sm tw-text-muted-foreground tw-space-y-1">
						<div v-if="vehicle.color" class="tw-flex tw-items-center tw-gap-2">
							<Palette class="tw-h-3 tw-w-3" />
							{{ vehicle.color }}
						</div>
						<div v-if="vehicle.customer?.name" class="tw-flex tw-items-center tw-gap-2">
							<User class="tw-h-3 tw-w-3" />
							{{ __('Customer') }}: {{ vehicle.customer.name }}
						</div>
					</div>
				</div>

				<!-- Confirmation Checkbox -->
				<div class="tw-border tw-border-destructive/20 tw-rounded-md tw-p-4 tw-bg-destructive/5">
					<vee-field name="confirmation" v-slot="{ field, errors }">
						<div class="tw-flex tw-items-start tw-space-x-3">
							<Checkbox
								id="confirmation"
								:model-value="item.confirmation"
								@update:model-value="(checked) => item.confirmation = checked"
								v-bind="field"
								:class="{ 'tw-border-destructive': errors.length }"
							/>
							<div class="tw-flex-1">
								<Label
									for="confirmation"
									class="tw-text-sm tw-font-medium tw-leading-tight tw-cursor-pointer"
								>
									{{ __("I understand and want to delete this vehicle") }}
								</Label>
								<p class="tw-text-xs tw-text-muted-foreground tw-mt-1">
									{{ __("This action is permanent and cannot be undone.") }}
								</p>
								<p v-if="errors.length" class="tw-text-xs tw-text-destructive tw-mt-1">{{ errors[0] }}</p>
							</div>
						</div>
					</vee-field>
				</div>
			</div>

			<template #footer>
				<Button
					variant="destructive"
					type="button"
					@click="submit(setErrors)"
					:disabled="!item.confirmation || item.processing"
					class="tw-w-auto tw-min-w-24"
				>
					<Loader2 v-if="item.processing" class="tw-h-4 tw-w-4 tw-mr-2 tw-animate-spin" />
					<Trash2 v-else class="tw-h-4 tw-w-4 tw-mr-2" />
					{{ __("Delete Vehicle") }}
				</Button>
			</template>
		</sakal-form-modal>
	</vee-form>
</template>

<script setup>
	import { useForm } from "@inertiajs/vue3";
	import { Button } from "@/Components/ui/button";
	import { Badge } from "@/Components/ui/badge";
	import { Checkbox } from "@/Components/ui/checkbox";
	import { Label } from "@/Components/ui/label";
	import { AlertTriangle, Loader2, Trash2, Car, User, Palette } from "lucide-vue-next";
	import * as yup from "yup";

	const props = defineProps({
		vehicle: {
			type: Object,
			required: true
		}
	});

	const schema = yup.object({
		confirmation: yup.boolean().oneOf([true], __('You must confirm the deletion by checking the confirmation box.')),
	});

	const item = useForm({
		confirmation: false,
	});

	const submit = (setErrors) => {
		item.delete(
			route("dashboard.garage.vehicles.destroy", {
				vehicle: props.vehicle.uuid,
			}),
			{
				onError: (errors) => {
					setErrors(errors);
				},
			}
		);
	};
</script>
