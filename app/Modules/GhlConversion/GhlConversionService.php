<?php

namespace App\Modules\GhlConversion;

use App\Models\ConversionKey;
use App\Models\ConversionLog;
use App\Modules\Api\ClinikoApi;
use App\Modules\Api\GoHighLevelApi;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class GhlConversionService
{
    public function __construct(protected ClinikoApi $clinikoApi, protected GoHighLevelApi $ghlApi)
    {
        $this->clinikoApi = $clinikoApi;
        $this->ghlApi = $ghlApi;
    }

    public function processConversion(ConversionKey $model)
    {
        $cToken = $model->cliniko_api_key;
        $ghlToken = $model->ghl_api_key;
        $appTypeId = $model->cliniko_app_type_id;
        $pipelineId = $model->ghl_pipeline_id;
        $pipelineStageId = $model->ghl_pipeline_stage_target_id;
        $companyName = $model->company_name;

        $this->clinikoApi->setToken($cToken);
        $this->ghlApi->setToken($ghlToken);

        $appointments = $this->getNewAppointments($appTypeId, $model);
        $newData = $this->processAppointments($appointments, $pipelineId, $pipelineStageId);
        foreach ($newData as $data) {
            $appointmentId = data_get($data, 'appointment.id');
            $patientId = data_get($data, 'patient.id');
            $contactId = data_get($data, 'contact.id');
            $source = data_get($data, 'opportunity.source');
            $contact = data_get($data, 'opportunity.contact', []);
            $opportunityId = data_get($data, 'opportunity.id');
            $patientName = data_get($data, 'patient.first_name') . ' ' . data_get($data, 'patient.last_name');
            $patientPhone = data_get($data, 'patient.patient_phone_numbers.0.normalized_number');
            $patientEmail = data_get($data, 'patient.email');
            $contactName = data_get($contact, 'name');
            $contactPhone = data_get($contact, 'phone');
            $contactEmail = data_get($contact, 'email');

            logger('Converted Opportunity', compact(
                'appointmentId',
                'opportunityId',
                'patientId',
                'contactId',
                'companyName',
                'source',
                'patientName',
                'patientPhone',
                'patientEmail',
                'contactName',
                'contactPhone',
                'contactEmail',
            ));

            try {
                $data = [
                    'appointment_id' => $appointmentId,
                    'opportunity_id' => $opportunityId,
                    'contact_id' => $contactId ?: '',
                    'patient_id' => $patientId,
                    'source' => $source,
                    'patient_name' => $patientName,
                    'patient_phone' => $patientPhone,
                    'patient_email' => $patientEmail,
                    'contact_name' => $contactName,
                    'contact_phone' => $contactPhone,
                    'contact_email' => $contactEmail,
                ];
                $model->conversionLogs()->updateOrCreate(Arr::only($data, ['appointment_id']), $data);
            } catch (\Throwable $th) {
                logger()->error($th);
            }
        }
    }

    protected function getNewAppointments($appTypeId, ConversionKey $model): array
    {
        $formattedDate = Carbon::now()->subHour()->toIso8601ZuluString();

        if (app()->environment('local')) {
            $formattedDate = Carbon::now()->subDays(150)->toIso8601ZuluString();
        }

        $logAppId = $model->conversionLogs
            ->pluck('appointment_id')
            ->toArray();

        $params = [
            'order=desc',
            'q[]=appointment_type_id:=' . $appTypeId,
            'q[]=created_at:>' . $formattedDate,
        ];

        $results = $this->clinikoApi->request(
            'individual_appointments?' . implode('&', $params)
        );

        return collect(data_get($results, 'individual_appointments', []))
            ->map(function ($appointment) {
                $url = data_get($appointment, 'patient.links.self', '');
                $patient = $this->clinikoApi->request($url);
                return compact('appointment', 'patient');
            })
            ->whereNotIn('id', $logAppId)
            ->values()
            ->toArray();
    }

    protected function processAppointments(array $appList, $pipelineId, $pipelineStageId)
    {
        $results = [];
        foreach ($appList as $cliniko) {
            $search = $this->searchOpportunity($pipelineId, $cliniko);

            $opportunity = data_get($search, 'opportunity', []);
            $contact = data_get($search, 'contact', []);
            $isMoved = false;

            if (!$opportunity) {
                if (!$contact) {
                    $contact = $this->newContact($cliniko['patient']);
                }
                if (data_get($contact, 'id')) {
                    $opportunity = $this->newOpportunities($contact, $cliniko['patient'], $pipelineId, $pipelineStageId);
                    $isMoved = true;
                }
            } else {
                if (data_get($opportunity, 'pipelineStageId') != $pipelineStageId) {
                    $opportunity = $this->moveOpportunities($opportunity, $pipelineId, $pipelineStageId);
                    $isMoved = true;
                }
            }

            if ($isMoved) {
                $results[] = $cliniko + compact('opportunity', 'contact');
            }
        }

        return $results;
    }

    protected function moveOpportunities(array $opportunity, $pipelineId, $pipelineStageId): array
    {
        if (app()->environment('local')) return [];

        return $this->ghlApi->request(
            "pipelines/{$pipelineId}/opportunities/" . data_get($opportunity, 'id'),
            [
                'title' => data_get($opportunity, 'name'),
                'stageId' => $pipelineStageId,
                'status' => data_get($opportunity, 'status'),
            ],
            'put'
        );
    }

    protected function newOpportunities(array $contact, array $patient, $pipelineId, $pipelineStageId): array
    {
        if (app()->environment('local')) return [];

        return $this->ghlApi->request(
            "pipelines/{$pipelineId}/opportunities",
            [
                'title' => data_get($patient, 'first_name') . ' ' . data_get($patient, 'last_name'),
                'stageId' => $pipelineStageId,
                'contactId' => data_get($contact, 'id'),
                'status' => 'open',
                'source' => 'Cliniko',
            ],
            'post'
        );
    }

    protected function newContact(array $patient): array
    {
        if (app()->environment('local')) return [];

        return $this->ghlApi->request(
            "contacts",
            [
                "email" => data_get($patient, 'email'),
                "phone" => data_get($patient, 'patient_phone_numbers.0.normalized_number'),
                "firstName" => data_get($patient, 'first_name'),
                "lastName" => data_get($patient, 'last_name'),
                "name" => data_get($patient, 'first_name') . ' ' . data_get($patient, 'last_name'),
                "source" => "cliniko",
            ],
            'post'
        );
    }

    protected function getOpportunities(string $query, $pipelineId): array
    {
        $opportunities = $this->ghlApi->request("pipelines/{$pipelineId}/opportunities", compact('query'));
        return data_get($opportunities, 'opportunities', []);
    }

    protected function getContacts(string $query): array
    {
        $order = 'desc';
        $contacts = $this->ghlApi->request("contacts", compact('query', 'order'));
        return data_get($contacts, 'contacts', []);
    }

    protected function searchOpportunity(string $pipelineId, array $cliniko): array
    {
        // email matching
        $query = data_get($cliniko, 'patient.email');

        $match = 'email';
        $opportunity = head($this->getOpportunities($query, $pipelineId));
        $contact = head($this->getContacts($query));
        if ($contact) return compact('opportunity', 'contact', 'match');

        // phone matching
        if ($phone = data_get($cliniko, 'patient.patient_phone_numbers.0.normalized_number')) {
            $items = ['first_name', 'last_name'];
            while (!empty($items)) {
                $item = array_shift($items); // pops from start
                $query = data_get($cliniko, "patient.{$item}");

                $match = 'phone';
                $opportunity = collect($this->getOpportunities($query, $pipelineId))
                    ->filter(fn(array $item) => str_contains(data_get($item, 'contact.phone'), $phone))
                    ->first(); // reset keys if needed;
                $contact = collect($this->getContacts($query))
                    ->filter(fn(array $item) => str_contains(data_get($item, 'phone'), $phone))
                    ->first();

                if ($contact) return compact('opportunity', 'contact', 'match');
            }
        }

        return [];
    }
}
