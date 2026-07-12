<template>
	<inertia-head :title="__('Vehicles')" />

	<div class="tw-space-y-6">
		<!-- Header -->
		<div class="tw-flex tw-flex-col tw-gap-4 md:tw-flex-row md:tw-items-center md:tw-justify-between">
			<div>
				<h1 class="tw-text-3xl tw-font-bold tw-tracking-tight tw-text-foreground">
					{{ __('Vehicles') }}
				</h1>
				<p class="tw-text-muted-foreground">
					{{ __('Manage customer vehicles serviced by your garage') }}
				</p>
			</div>
			<Button @click="createVehicle" class="tw-shrink-0" v-permission:any="'CREATE_GARAGE_VEHICLE'">
				<Plus class="tw-h-4 tw-w-4 tw-mr-2" />
				{{ __('Add Vehicle') }}
			</Button>
		</div>

		<!-- Filters Card -->
		<Card>
			<CardHeader>
				<CardTitle class="tw-flex tw-items-center tw-gap-2">
					<Search class="tw-h-5 tw-w-5" />
					{{ __('Search & Filter') }}
				</CardTitle>
			</CardHeader>
			<CardContent>
				<div class="tw-grid tw-gap-4 md:tw-grid-cols-2 lg:tw-grid-cols-4">
					<div class="tw-space-y-2">
						<Label for="search">{{ __('Search') }}</Label>
						<div class="tw-relative">
							<Search class="tw-absolute tw-left-3 tw-top-2.5 tw-h-4 tw-w-4 tw-text-muted-foreground" />
							<Input
								id="search"
								v-model="search"
								:placeholder="__('Search plate, make, model...')"
								class="tw-pl-9"
							/>
						</div>
					</div>
					<div class="tw-space-y-2">
						<Label for="customer-filter">{{ __('Customer') }}</Label>
						<Select v-model="customerFilter">
							<SelectTrigger>
								<SelectValue :placeholder="__('All customers')" />
							</SelectTrigger>
							<SelectContent>
								<SelectItem value="all">{{ __('All customers') }}</SelectItem>
								<SelectItem
									v-for="customer in customers"
									:key="customer.id"
									:value="customer.id.toString()"
								>
									{{ customer.name }}
								</SelectItem>
							</SelectContent>
						</Select>
					</div>
					<div class="tw-flex tw-items-end">
						<Button @click="refresh" variant="outline" :disabled="loading">
							<RotateCcw :class="{ 'tw-animate-spin': loading }" class="tw-h-4 tw-w-4 tw-mr-2" />
							{{ __('Refresh') }}
						</Button>
					</div>
				</div>
			</CardContent>
		</Card>

		<!-- Vehicles Table -->
		<Card>
			<CardHeader>
				<CardTitle>{{ __('Vehicles') }}</CardTitle>
			</CardHeader>
			<CardContent class="tw-p-0">
				<div class="tw-overflow-x-auto">
					<table class="tw-w-full">
						<thead class="tw-border-b">
							<tr class="tw-text-left">
								<th class="tw-px-4 tw-py-3 tw-text-sm tw-font-medium tw-text-muted-foreground">
									{{ __('Vehicle') }}
								</th>
								<th class="tw-px-4 tw-py-3 tw-text-sm tw-font-medium tw-text-muted-foreground">
									{{ __('Customer') }}
								</th>
								<th class="tw-px-4 tw-py-3 tw-text-sm tw-font-medium tw-text-muted-foreground">
									{{ __('Color') }}
								</th>
								<th class="tw-px-4 tw-py-3 tw-text-sm tw-font-medium tw-text-muted-foreground">
									{{ __('Status') }}
								</th>
								<th class="tw-px-4 tw-py-3 tw-text-sm tw-font-medium tw-text-muted-foreground tw-w-24">
									{{ __('Actions') }}
								</th>
							</tr>
						</thead>
						<tbody>
							<tr
								v-for="vehicle in items.data"
								:key="vehicle.id"
								class="tw-border-b hover:tw-bg-muted/50 tw-transition-colors"
							>
								<td class="tw-px-4 tw-py-3">
									<div class="tw-flex tw-items-center tw-gap-2">
										<Car class="tw-h-4 tw-w-4 tw-text-muted-foreground tw-shrink-0" />
										<div>
											<div class="tw-font-medium">{{ [vehicle.make, vehicle.model].filter(Boolean).join(' ') || __('Vehicle') }}</div>
											<div v-if="vehicle.plate_number" class="tw-text-sm tw-text-muted-foreground">
												{{ vehicle.plate_number }}
											</div>
										</div>
									</div>
								</td>
								<td class="tw-px-4 tw-py-3">
									<div class="tw-flex tw-items-center tw-gap-2">
										<User class="tw-h-4 tw-w-4 tw-text-muted-foreground tw-shrink-0" />
										<span class="tw-font-medium">{{ vehicle.customer?.name || __('Unassigned') }}</span>
									</div>
								</td>
								<td class="tw-px-4 tw-py-3">
									<span class="tw-text-sm tw-text-muted-foreground">{{ vehicle.color || '—' }}</span>
								</td>
								<td class="tw-px-4 tw-py-3">
									<Badge v-if="vehicle.active" variant="secondary" size="sm">{{ __('Active') }}</Badge>
									<Badge v-else variant="outline" size="sm">{{ __('Inactive') }}</Badge>
								</td>
								<td class="tw-px-4 tw-py-3">
									<DropdownMenu>
										<DropdownMenuTrigger as-child>
											<Button variant="ghost" size="sm">
												<MoreHorizontal class="tw-h-4 tw-w-4" />
											</Button>
										</DropdownMenuTrigger>
										<DropdownMenuContent align="end">
											<DropdownMenuItem @click="editVehicle(vehicle)" v-permission:any="'UPDATE_GARAGE_VEHICLE'">
												<Pencil class="tw-h-4 tw-w-4 tw-mr-2" />
												{{ __('Edit') }}
											</DropdownMenuItem>
											<DropdownMenuSeparator />
											<DropdownMenuItem @click="deleteVehicle(vehicle)" class="tw-text-destructive" v-permission:any="'DELETE_GARAGE_VEHICLE'">
												<Trash2 class="tw-h-4 tw-w-4 tw-mr-2" />
												{{ __('Delete') }}
											</DropdownMenuItem>
										</DropdownMenuContent>
									</DropdownMenu>
								</td>
							</tr>
							<tr v-if="!items.data || items.data.length === 0">
								<td colspan="5" class="tw-px-4 tw-py-8 tw-text-center tw-text-muted-foreground">
									<div class="tw-flex tw-flex-col tw-items-center tw-gap-2">
										<Car class="tw-h-8 tw-w-8 tw-text-muted-foreground/50" />
										<p>{{ __('No vehicles found') }}</p>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</CardContent>
			<CardFooter v-if="items.data && items.data.length > 0">
				<div class="tw-flex tw-items-center tw-justify-between tw-w-full">
					<div class="tw-text-sm tw-text-muted-foreground">
						{{ __('Showing :from to :to of :total vehicles', {
							args: {
								from: items.meta?.from || 0,
								to: items.meta?.to || 0,
								total: items.meta?.total || 0
							}
						}) }}
					</div>
					<div class="tw-flex tw-items-center tw-gap-2" v-if="lastPage > 1">
						<Button
							variant="outline"
							size="sm"
							:disabled="currentPage === 1"
							@click="goToPage(currentPage - 1)"
						>
							{{ __('Previous') }}
						</Button>
						<span class="tw-text-sm tw-text-muted-foreground">
							{{ __('Page :current of :total', { args: { current: currentPage, total: lastPage } }) }}
						</span>
						<Button
							variant="outline"
							size="sm"
							:disabled="currentPage === lastPage"
							@click="goToPage(currentPage + 1)"
						>
							{{ __('Next') }}
						</Button>
					</div>
				</div>
			</CardFooter>
		</Card>
	</div>
</template>

<script setup>
import { computed, ref, watch, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Badge } from '@/Components/ui/badge'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/Components/ui/dropdown-menu'
import { Plus, RotateCcw, Search, Car, User, MoreHorizontal, Pencil, Trash2 } from 'lucide-vue-next'
import { debounce } from 'lodash'

const props = defineProps({
	items: {
		type: Object,
		required: true
	},
	filters: {
		type: Object,
		default: () => ({})
	}
})

const customers = ref([])
const search = ref('')
const customerFilter = ref('all')
const loading = ref(false)

const currentPage = computed(() => props.items.meta?.current_page || 1)
const lastPage = computed(() => props.items.meta?.last_page || 1)

const buildQuery = (page) => {
	const filter = {}
	if (search.value) filter.search = search.value
	if (customerFilter.value !== 'all') filter.customer_id = customerFilter.value

	router.get(route('dashboard.garage.vehicles.index'), { filter, page }, {
		preserveState: true,
		preserveScroll: true,
		only: ['items']
	})
}

const debouncedSearch = debounce(() => buildQuery(1), 300)

watch(search, () => debouncedSearch())
watch(customerFilter, () => buildQuery(1))

const goToPage = (page) => buildQuery(page)

const refresh = () => {
	loading.value = true
	router.reload({
		only: ['items'],
		onFinish: () => {
			loading.value = false
		}
	})
}

const createVehicle = () => {
	router.get(route('dashboard.garage.vehicles.create'))
}

const editVehicle = (vehicle) => {
	router.get(route('dashboard.garage.vehicles.edit', { vehicle: vehicle.uuid }))
}

const deleteVehicle = (vehicle) => {
	router.get(route('dashboard.garage.vehicles.delete', { vehicle: vehicle.uuid }))
}

const getCustomers = async () => {
	try {
		const { data } = await axios.get(route("dashboard.customer.data.customers"))
		if (data.success) {
			customers.value = data.data
		}
	} catch (error) {
		console.error('Failed to fetch customers:', error)
	}
}

onMounted(async () => {
	await getCustomers()
})
</script>
