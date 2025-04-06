<?php

namespace App\Models;

use App\Modules\Api\ClinikoApi;
use DateTime;
use DateTimeZone;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'opportunity_id',
        'patient_id',
        'contact_id',
        'source',
        'patient_name',
        'patient_phone',
        'patient_email',
        'contact_name',
        'contact_phone',
        'contact_email',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['conversionKeys'];

    protected $appends = ['api_details'];

    public function conversionKeys()
    {
        return $this->belongsTo(ConversionKey::class, 'conversion_id');
    }

    protected function apiDetails(): Attribute
    {
        return Attribute::make(
            get: fn($value, array $attributes) => $this->requestApiDetails('api_details', $attributes),
        );
    }

    protected function practitionerName(): Attribute
    {
        $details = $this->api_details;
        return Attribute::make(
            get: fn($value, array $attributes) =>
            data_get($details, 'practitioner.first_name') . ' ' . data_get($details, 'practitioner.last_name'),
        );
    }

    protected function appointmentStatus(): Attribute
    {
        $details = $this->api_details;
        return Attribute::make(
            get: fn($value, array $attributes) => $this->getAppointmentStatus(data_get($details, 'appointment', [])),
        );
    }

    protected function appointmentDate(): Attribute
    {
        $details = $this->api_details;
        $startsAt = data_get($details, 'appointment.starts_at');
        if ($startsAt) $startsAt = date('Y-m-d H:i:s', strtotime($startsAt));

        return Attribute::make(
            get: fn($value, array $attributes) => $startsAt,
        );
    }

    protected function flowDirection(): Attribute
    {
        $details = $this->api_details;
        $id = data_get($details, 'opportunity.id');
        $source = data_get($details, 'opportunity.source');
        $direction = 'GHL to Cliniko';
        if (strtolower($source) == strtolower(config('app.cliniko_source'))) {
            $direction = 'Cliniko to GHL';
        }

        if (!$id) {
            $direction = '---';
        }

        return Attribute::make(
            get: fn($value, array $attributes) => $direction,
        );
    }

    protected function convertedStamp(): Attribute
    {
        $details = $this->api_details;
        $statusChangeAt = data_get($details, 'opportunity.lastStatusChangeAt');

        $dateString = null;

        if ($statusChangeAt) {
            // Convert to DateTime object (handling the 'Z' as UTC)
            $date = new DateTime($statusChangeAt, new DateTimeZone("UTC"));
            $dateString = $date->format("Y-m-d H:i:s");
        }

        return Attribute::make(
            get: fn($value, array $attributes) => $dateString,
        );
    }

    protected function requestApiDetails(string $key, array $attributes): array
    {
        if ($result = data_get($attributes, $key)) return $result;

        /**
         * @var ConversionKey
         */
        $ckModel = $this->conversionKeys;

        /**
         * @var ClinikoApi
         */
        $clinikoApi = $ckModel->getClinikoApi();

        /**
         * @var GoHighLevelApi
         */
        $ghlApi = $ckModel->getGoHighLevelApi();

        $result = [];

        $appointment = $clinikoApi->request(
            'individual_appointments/' . $attributes['appointment_id']
        );
        $result += compact('appointment');

        if ($practitionerLink = data_get($appointment, 'practitioner.links.self')) {
            $practitioner = $clinikoApi->requestUrl(
                data_get($appointment, 'practitioner.links.self')
            );
            $result += compact('practitioner');
        }

        $opportunity = $ghlApi->request(
            'pipelines/' . $ckModel->ghl_pipeline_id . '/opportunities/' . $attributes['opportunity_id']
        );
        $result += compact('opportunity');

        $this->setAttribute($key, $result);

        return $result;
    }

    protected function getAppointmentStatus(array $appointment): string
    {
        // Check if the appointment was cancelled
        if ($cancelledAt = data_get($appointment, 'cancelled_at')) {
            $cancellationReason = data_get($appointment, 'cancellation_reason_description', 'No specific reason provided');
            $cancellationNote = data_get($appointment, 'cancellation_note', 'No cancellation note provided');
            return "Cancelled - Reason: $cancellationReason. Note: $cancellationNote";
        }

        // Check if the patient did not arrive (no show)
        if (data_get($appointment, 'did_not_arrive') === true) {
            return 'No Show - Patient did not attend the appointment';
        }

        // Check if the patient arrived
        if (data_get($appointment, 'patient_arrived') === true) {
            return 'Attended - Patient attended the appointment';
        }

        // Get the appointment's start and end times
        $startsAt = data_get($appointment, 'starts_at');
        $endsAt = data_get($appointment, 'ends_at');

        // Check if the appointment has already passed
        if ($startsAt && strtotime($startsAt) < time() && strtotime($endsAt) < time()) {
            // If the appointment has ended and the patient didn't arrive
            if (data_get($appointment, 'patient_arrived') === false) {
                return 'No Show - Patient did not attend the appointment';
            }
            // If the appointment has passed, but the patient arrived
            return 'Completed - Patient attended the appointment';
        }

        // Check if the appointment is upcoming (scheduled in the future)
        if ($startsAt && strtotime($startsAt) > time()) {
            return 'Upcoming Appointment';
        }

        // Check if the appointment is completed (patient arrived and time has passed)
        if ($endsAt && strtotime($endsAt) < time() && data_get($appointment, 'patient_arrived') === true) {
            return 'Completed - Patient attended the appointment';
        }

        // Pending status if we expect confirmation or action
        if (data_get($appointment, 'invoice_status') === 1) {
            return 'Awaiting Payment - Payment is pending';
        }

        // If none of the above conditions apply, return Unknown
        return 'Unknown Status - No status available';
    }
}
