<template>
	<inertia-head :title="__('Service Jobs')" />

	<div class="tw-space-y-6">
		<!-- Header -->
		<div class="tw-flex tw-flex-col tw-gap-4 md:tw-flex-row md:tw-items-center md:tw-justify-between">
			<div>
				<h1 class="tw-text-3xl tw-font-bold tw-tracking-tight tw-text-foreground">
					{{ __('Service Jobs') }}
				</h1>
				<p class="tw-text-muted-foreground">
					{{ __('Captured jobs with photo proof, handed off to the POS to settle') }}
				</p>
			</div>
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
								:placeholder="__('Plate, technician...')"
								class="tw-pl-9"
							/>
						</div>
					</div>
					<div class="tw-space-y-2">
						<Label for="status-filter">{{ __('Status') }}</Label>
						<Select v-model="statusFilter">
							<SelectTrigger>
								<SelectValue :placeholder="__('All statuses')" />
							</SelectTrigger>
							<SelectContent>
								<SelectItem value="all">{{ __('All statuses') }}</SelectItem>
								<SelectItem value="pending_settlement">{{ __('Pending settlement') }}</SelectItem>
								<SelectItem value="settled">{{ __('Settled') }}</SelectItem>
								<SelectItem value="voided">{{ __('Voided') }}</SelectItem>
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

		<!-- Jobs Table -->
		<Card>
			<CardHeader>
				<CardTitle>{{ __('Service Jobs') }}</CardTitle>
			</CardHeader>
			<CardContent class="tw-p-0">
				<div class="tw-overflow-x-auto">
					<table class="tw-w-full">
						<thead class="tw-border-b">
							<tr class="tw-text-left">
								<th class="tw-px-4 tw-py-3 tw-text-sm tw-font-medium tw-text-muted-foreground">{{ __('Vehicle') }}</th>
								<th class="tw-px-4 tw-py-3 tw-text-sm tw-font-medium tw-text-muted-foreground">{{ __('Technician') }}</th>
								<th class="tw-px-4 tw-py-3 tw-text-sm tw-font-medium tw-text-muted-foreground">{{ __('Items') }}</th>
								<th class="tw-px-4 tw-py-3 tw-text-sm tw-font-medium tw-text-muted-foreground">{{ __('Proof') }}</th>
								<th class="tw-px-4 tw-py-3 tw-text-sm tw-font-medium tw-text-muted-foreground">{{ __('Ticket') }}</th>
								<th class="tw-px-4 tw-py-3 tw-text-sm tw-font-medium tw-text-muted-foreground">{{ __('Status') }}</th>
								<th class="tw-px-4 tw-py-3 tw-text-sm tw-font-medium tw-text-muted-foreground">{{ __('Submitted') }}</th>
								<th class="tw-px-4 tw-py-3 tw-text-sm tw-font-medium tw-text-muted-foreground tw-w-16"></th>
							</tr>
						</thead>
						<tbody>
							<tr
								v-for="job in items.data"
								:key="job.id"
								class="tw-border-b hover:tw-bg-muted/50 tw-transition-colors tw-cursor-pointer"
								@click="viewJob(job)"
							>
								<td class="tw-px-4 tw-py-3">
									<div class="tw-flex tw-items-center tw-gap-2">
										<Car class="tw-h-4 tw-w-4 tw-text-muted-foreground tw-shrink-0" />
										<div>
											<div class="tw-font-medium">{{ job.vehicle?.plate_number || __('Vehicle') }}</div>
											<div v-if="job.vehicle" class="tw-text-sm tw-text-muted-foreground">
												{{ [job.vehicle.make, job.vehicle.model].filter(Boolean).join(' ') }}
											</div>
										</div>
									</div>
								</td>
								<td class="tw-px-4 tw-py-3 tw-text-sm">{{ job.technician || '—' }}</td>
								<td class="tw-px-4 tw-py-3 tw-text-sm tw-text-muted-foreground">{{ job.line_count ?? 0 }}</td>
								<td class="tw-px-4 tw-py-3">
									<div class="tw-flex tw-items-center tw-gap-1 tw-text-sm" :class="job.photo_count ? 'tw-text-foreground' : 'tw-text-muted-foreground'">
										<Camera class="tw-h-4 tw-w-4" />
										{{ job.photo_count ?? 0 }}
									</div>
								</td>
								<td class="tw-px-4 tw-py-3 tw-text-sm tw-font-mono">{{ job.order?.order_number || '—' }}</td>
								<td class="tw-px-4 tw-py-3">
									<Badge :variant="statusVariant(job.status)" size="sm">{{ job.status_label }}</Badge>
								</td>
								<td class="tw-px-4 tw-py-3 tw-text-sm tw-text-muted-foreground">{{ formatDate(job.submitted_at) }}</td>
								<td class="tw-px-4 tw-py-3 tw-text-right">
									<ChevronRight class="tw-h-4 tw-w-4 tw-text-muted-foreground" />
								</td>
							</tr>
							<tr v-if="!items.data || items.data.length === 0">
								<td colspan="8" class="tw-px-4 tw-py-8 tw-text-center tw-text-muted-foreground">
									<div class="tw-flex tw-flex-col tw-items-center tw-gap-2">
										<Wrench class="tw-h-8 tw-w-8 tw-text-muted-foreground/50" />
										<p>{{ __('No service jobs found') }}</p>
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
						{{ __('Showing :from to :to of :total jobs', {
							args: { from: items.meta?.from || 0, to: items.meta?.to || 0, total: items.meta?.total || 0 }
						}) }}
					</div>
					<div class="tw-flex tw-items-center tw-gap-2" v-if="lastPage > 1">
						<Button variant="outline" size="sm" :disabled="currentPage === 1" @click="goToPage(currentPage - 1)">
							{{ __('Previous') }}
						</Button>
						<span class="tw-text-sm tw-text-muted-foreground">
							{{ __('Page :current of :total', { args: { current: currentPage, total: lastPage } }) }}
						</span>
						<Button variant="outline" size="sm" :disabled="currentPage === lastPage" @click="goToPage(currentPage + 1)">
							{{ __('Next') }}
						</Button>
					</div>
				</div>
			</CardFooter>
		</Card>
	</div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Badge } from '@/Components/ui/badge'
import { RotateCcw, Search, Car, Camera, Wrench, ChevronRight } from 'lucide-vue-next'
import { debounce } from 'lodash'

const props = defineProps({
	items: { type: Object, required: true },
	filters: { type: Object, default: () => ({}) },
})

const search = ref('')
const statusFilter = ref('all')
const loading = ref(false)

const currentPage = computed(() => props.items.meta?.current_page || 1)
const lastPage = computed(() => props.items.meta?.last_page || 1)

const buildQuery = (page) => {
	const filter = {}
	if (search.value) filter.search = search.value
	if (statusFilter.value !== 'all') filter.status = statusFilter.value

	router.get(route('dashboard.garage.service-jobs.index'), { filter, page }, {
		preserveState: true,
		preserveScroll: true,
		only: ['items'],
	})
}

const debouncedSearch = debounce(() => buildQuery(1), 300)

watch(search, () => debouncedSearch())
watch(statusFilter, () => buildQuery(1))

const goToPage = (page) => buildQuery(page)

const refresh = () => {
	loading.value = true
	router.reload({ only: ['items'], onFinish: () => { loading.value = false } })
}

const viewJob = (job) => {
	router.get(route('dashboard.garage.service-jobs.show', { service_job: job.uuid }))
}

const statusVariant = (status) => {
	switch (status) {
		case 'settled': return 'default'
		case 'voided': return 'destructive'
		default: return 'secondary'
	}
}

const formatDate = (value) => {
	if (!value) return '—'
	return new Date(value).toLocaleString(undefined, { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}
</script>
