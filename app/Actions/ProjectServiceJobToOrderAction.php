<?php

namespace Modules\Garage\Actions;

use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Customer\Models\Customer;
use Modules\Garage\Models\ServiceJob;
use Modules\Order\Actions\Order\CreateOrder;
use Modules\Order\Data\Order\CreateOrderData;
use Modules\Order\Enums\OrderChannelEnum;
use Modules\Order\Enums\OrderStatusEnum;
use Modules\Order\Enums\OrderTypeEnum;
use Modules\Product\Models\Product;

/**
 * Project a captured service job into a POS Order (GR-05-02) — the hand-off.
 *
 * Creates an `order_type = service`, `status = draft` order (an open, unpaid
 * ticket) via the Order module's own CreateOrder pipeline. DRAFT = "created,
 * payment not completed", so the front desk sees it as an open ticket to
 * invoice + settle. No payment is taken here; CreateOrder fires no OrderPlaced
 * event for DRAFT orders, so nothing settles prematurely.
 *
 * Idempotent: a job that already has an order_id is returned untouched, so a
 * re-submit (CreateServiceJobAction rebuilds lines) never spawns a second ticket.
 */
class ProjectServiceJobToOrderAction
{
	use AsAction;

	public function handle(ServiceJob $job): ServiceJob
	{
		if ($job->order_id) {
			return $job; // already handed off
		}

		return DB::transaction(function () use ($job) {
			$job->loadMissing(['lines', 'vehicle']);

			// Tenant is NOT passed explicitly — BelongsToMorphTenant stamps it
			// from the active request context, exactly as POS checkout does, so
			// the order's tenant representation matches the scope and loads back.
			$data = CreateOrderData::fromRequest([
				'order_type' => OrderTypeEnum::SERVICE->value,
				'status' => OrderStatusEnum::DRAFT->value,
				'payment_status' => 'unpaid',
				'customer_type' => $this->customerMorph($job),
				'customer_id' => $job->customer_id,
				'customer_notes' => $job->note,
				'metadata' => $this->orderMetadata($job),
				'items' => $this->items($job),
			]);

			$order = CreateOrder::run($data);

			// Channel isn't part of CreateOrderData; stamp it (and the vehicle
			// service context) after creation. Kept out of the money path.
			$order->update(['channel' => OrderChannelEnum::POS]);

			$job->update(['order_id' => $order->id]);

			return $job->fresh(['lines', 'vehicle', 'order']);
		});
	}

	/**
	 * Line items from the job's services + parts. Resolves product_uuid to a
	 * product_id where the catalog item still exists; a custom line keeps a null
	 * product_id and its captured name.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function items(ServiceJob $job): array
	{
		return $job->lines->map(function ($line) {
			$price = (float) ($line->unit_price ?? 0);
			$subtotal = round($price * $line->qty, 2);

			return [
				'product_id' => $this->resolveProductId($line->product_uuid),
				'name' => $line->name,
				'quantity' => $line->qty,
				'price' => $price,
				'subtotal' => $subtotal,
				'notes' => $line->note,
				'metadata' => array_filter([
					'garage_line_type' => $line->type,
					'membership_redemption' => $line->membership_redemption,
					'serial' => $line->serial,
					'client_line_id' => $line->client_line_id,
				], fn ($v) => $v !== null),
			];
		})->all();
	}

	private function resolveProductId(?string $productUuid): ?int
	{
		if (! $productUuid) {
			return null;
		}

		return Product::query()->where('uuid', $productUuid)->value('id');
	}

	/**
	 * The Customer morph type, resolved without assuming a hard-coded alias.
	 */
	private function customerMorph(ServiceJob $job): ?string
	{
		return $job->customer_id ? (new Customer)->getMorphClass() : null;
	}

	/**
	 * Vehicle-service context carried on the order for the front desk + audit.
	 *
	 * @return array<string, mixed>
	 */
	private function orderMetadata(ServiceJob $job): array
	{
		return array_filter([
			'garage_service_job_uuid' => $job->uuid,
			'garage_job_uuid' => $job->job_uuid,
			'vehicle_plate' => $job->vehicle?->plate_number,
			'vehicle_label' => $job->vehicle?->label,
			'technician' => $job->technician,
			'odometer' => $job->odometer,
			'membership_ref' => $job->membership_ref,
			'membership_redemption' => $job->lines->contains(fn ($l) => (bool) $l->membership_redemption),
		], fn ($v) => $v !== null);
	}
}
