<template>
	<inertia-head :title="__('Service Job')" />

	<div class="tw-space-y-6">
		<!-- Header -->
		<div class="tw-flex tw-flex-col tw-gap-4 md:tw-flex-row md:tw-items-center md:tw-justify-between">
			<div class="tw-flex tw-items-center tw-gap-3">
				<Button variant="outline" size="icon" @click="back">
					<ArrowLeft class="tw-h-4 tw-w-4" />
				</Button>
				<div>
					<h1 class="tw-text-2xl tw-font-bold tw-tracking-tight tw-text-foreground tw-flex tw-items-center tw-gap-3">
						{{ job.vehicle?.plate_number || __('Service Job') }}
						<Badge :variant="statusVariant(job.status)">{{ job.status_label }}</Badge>
					</h1>
					<p class="tw-text-muted-foreground tw-text-sm">
						{{ __('Submitted') }} {{ formatDate(job.submitted_at) }}
						<span v-if="job.technician"> · {{ __('by') }} {{ job.technician }}</span>
					</p>
				</div>
			</div>
		</div>

		<div class="tw-grid tw-gap-6 lg:tw-grid-cols-3">
			<!-- Left: lines + proof -->
			<div class="tw-space-y-6 lg:tw-col-span-2">
				<!-- Line items -->
				<Card>
					<CardHeader>
						<CardTitle>{{ __('Services & Parts') }}</CardTitle>
					</CardHeader>
					<CardContent class="tw-p-0">
						<div class="tw-overflow-x-auto">
							<table class="tw-w-full">
								<thead class="tw-border-b">
									<tr class="tw-text-left">
										<th class="tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-muted-foreground">{{ __('Item') }}</th>
										<th class="tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-muted-foreground">{{ __('Type') }}</th>
										<th class="tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-muted-foreground tw-text-center">{{ __('Qty') }}</th>
										<th class="tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-muted-foreground tw-text-right">{{ __('Total') }}</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="line in job.lines" :key="line.id" class="tw-border-b last:tw-border-0">
										<td class="tw-px-4 tw-py-2">
											<div class="tw-font-medium">{{ line.name }}</div>
											<div v-if="line.serial" class="tw-text-xs tw-text-muted-foreground">SN: {{ line.serial }}</div>
											<div v-if="line.note" class="tw-text-xs tw-text-muted-foreground">{{ line.note }}</div>
										</td>
										<td class="tw-px-4 tw-py-2">
											<Badge variant="outline" size="sm">{{ line.type === 'part' ? __('Part') : __('Service') }}</Badge>
											<Badge v-if="line.membership_redemption" variant="secondary" size="sm" class="tw-ml-1">{{ __('Member') }}</Badge>
										</td>
										<td class="tw-px-4 tw-py-2 tw-text-center">{{ line.qty }}</td>
										<td class="tw-px-4 tw-py-2 tw-text-right tw-font-medium">
											<span v-if="line.membership_redemption" class="tw-text-muted-foreground">{{ __('Comp') }}</span>
											<span v-else>{{ money(line.line_total) }}</span>
										</td>
									</tr>
								</tbody>
								<tfoot class="tw-border-t">
									<tr>
										<td colspan="3" class="tw-px-4 tw-py-3 tw-text-right tw-font-semibold">{{ __('Total') }}</td>
										<td class="tw-px-4 tw-py-3 tw-text-right tw-font-bold">{{ money(job.display_total) }}</td>
									</tr>
								</tfoot>
							</table>
						</div>
					</CardContent>
				</Card>

				<!-- Photo proof -->
				<Card>
					<CardHeader>
						<CardTitle class="tw-flex tw-items-center tw-gap-2">
							<Camera class="tw-h-5 tw-w-5" />
							{{ __('Photo Proof') }}
							<span class="tw-text-sm tw-font-normal tw-text-muted-foreground">({{ job.media?.length || 0 }})</span>
						</CardTitle>
					</CardHeader>
					<CardContent>
						<div v-if="!job.media || job.media.length === 0" class="tw-text-center tw-py-8 tw-text-muted-foreground">
							{{ __('No photos captured for this job') }}
						</div>
						<div v-else class="tw-space-y-6">
							<div v-for="group in mediaGroups" :key="group.kind">
								<h3 class="tw-text-sm tw-font-medium tw-text-muted-foreground tw-mb-2 tw-capitalize">{{ group.label }}</h3>
								<div class="tw-grid tw-grid-cols-2 sm:tw-grid-cols-3 md:tw-grid-cols-4 tw-gap-3">
									<a
										v-for="photo in group.items"
										:key="photo.id"
										:href="photo.url"
										target="_blank"
										rel="noopener"
										class="tw-group tw-relative tw-aspect-square tw-overflow-hidden tw-rounded-lg tw-border tw-bg-muted"
									>
										<img
											v-if="photo.url"
											:src="photo.url"
											:alt="photo.caption || group.label"
											class="tw-h-full tw-w-full tw-object-cover tw-transition-transform group-hover:tw-scale-105"
											loading="lazy"
										/>
										<div v-else class="tw-flex tw-h-full tw-items-center tw-justify-center tw-text-muted-foreground">
											<ImageOff class="tw-h-6 tw-w-6" />
										</div>
										<div v-if="photo.caption" class="tw-absolute tw-bottom-0 tw-inset-x-0 tw-bg-black/60 tw-text-white tw-text-xs tw-px-2 tw-py-1 tw-truncate">
											{{ photo.caption }}
										</div>
									</a>
								</div>
							</div>
						</div>
					</CardContent>
				</Card>
			</div>

			<!-- Right: vehicle + settlement + meta -->
			<div class="tw-space-y-6">
				<!-- Vehicle -->
				<Card>
					<CardHeader><CardTitle>{{ __('Vehicle') }}</CardTitle></CardHeader>
					<CardContent class="tw-space-y-2 tw-text-sm">
						<div class="tw-flex tw-items-center tw-gap-2 tw-text-base tw-font-semibold">
							<Car class="tw-h-4 tw-w-4 tw-text-muted-foreground" />
							{{ job.vehicle?.plate_number || '—' }}
						</div>
						<div class="tw-flex tw-justify-between tw-gap-4">
							<span class="tw-text-muted-foreground">{{ __('Make / Model') }}</span>
							<span class="tw-font-medium">{{ [job.vehicle?.make, job.vehicle?.model].filter(Boolean).join(' ') || '—' }}</span>
						</div>
						<div class="tw-flex tw-justify-between tw-gap-4">
							<span class="tw-text-muted-foreground">{{ __('Customer') }}</span>
							<span class="tw-font-medium">{{ job.customer?.name || __('Walk-in') }}</span>
						</div>
						<div class="tw-flex tw-justify-between tw-gap-4">
							<span class="tw-text-muted-foreground">{{ __('Odometer') }}</span>
							<span class="tw-font-medium">{{ job.odometer ? job.odometer + ' km' : '—' }}</span>
						</div>
					</CardContent>
				</Card>

				<!-- Settlement / POS ticket -->
				<Card>
					<CardHeader><CardTitle>{{ __('Settlement') }}</CardTitle></CardHeader>
					<CardContent class="tw-space-y-2 tw-text-sm">
						<template v-if="job.order">
							<div class="tw-flex tw-justify-between tw-gap-4">
								<span class="tw-text-muted-foreground">{{ __('Ticket') }}</span>
								<span class="tw-font-mono tw-font-medium">{{ job.order.order_number }}</span>
							</div>
							<div class="tw-flex tw-justify-between tw-gap-4">
								<span class="tw-text-muted-foreground">{{ __('Order status') }}</span>
								<span class="tw-font-medium tw-capitalize">{{ job.order.status }}</span>
							</div>
							<div class="tw-flex tw-justify-between tw-gap-4">
								<span class="tw-text-muted-foreground">{{ __('Order total') }}</span>
								<span class="tw-font-medium">{{ money(job.order.total_amount) }}</span>
							</div>
						</template>
						<p v-else class="tw-text-muted-foreground">{{ __('Not yet handed off to the POS') }}</p>
						<div v-if="job.has_membership_redemption" class="tw-flex tw-items-center tw-gap-2 tw-text-amber-600 tw-pt-1">
							<BadgeCheck class="tw-h-4 tw-w-4" />
							<span class="tw-text-xs">{{ __('Membership redemption flagged — desk confirms at settlement') }}</span>
						</div>
					</CardContent>
				</Card>

				<!-- Note -->
				<Card v-if="job.note">
					<CardHeader><CardTitle>{{ __('Note') }}</CardTitle></CardHeader>
					<CardContent><p class="tw-text-sm tw-text-muted-foreground">{{ job.note }}</p></CardContent>
				</Card>
			</div>
		</div>
	</div>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { ArrowLeft, Car, Camera, ImageOff, BadgeCheck } from 'lucide-vue-next'

const props = defineProps({
	item: { type: Object, required: true },
})

// The controller returns a JsonResource; Inertia serializes it under `data`.
const job = computed(() => props.item?.data ?? props.item)

const KIND_LABELS = { plate: 'Plate', before: 'Before', after: 'After', condition: 'Condition', signature: 'Signature' }
const KIND_ORDER = ['plate', 'before', 'after', 'condition', 'signature']

const mediaGroups = computed(() => {
	const media = job.value.media || []
	const byKind = {}
	for (const m of media) {
		(byKind[m.kind] ??= []).push(m)
	}
	return KIND_ORDER
		.filter((k) => byKind[k]?.length)
		.map((k) => ({ kind: k, label: KIND_LABELS[k] || k, items: byKind[k] }))
})

const back = () => router.get(route('dashboard.garage.service-jobs.index'))

const statusVariant = (status) => {
	switch (status) {
		case 'settled': return 'default'
		case 'voided': return 'destructive'
		default: return 'secondary'
	}
}

const money = (value) => {
	const n = Number(value ?? 0)
	return '$' + n.toFixed(2)
}

const formatDate = (value) => {
	if (!value) return '—'
	return new Date(value).toLocaleString(undefined, { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}
</script>
