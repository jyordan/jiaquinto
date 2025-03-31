<?php

namespace App\Modules\GhlConversion;

use App\Modules\Api\ClinikoApi;
use App\Modules\Api\GoHighLevelApi;
use Carbon\Carbon;

class GhlConversionService
{
    public function __construct(protected ClinikoApi $clinikoApi, protected GoHighLevelApi $ghlApi)
    {
        $this->clinikoApi = $clinikoApi;
        $this->ghlApi = $ghlApi;
    }

    public function processConversion(
        string $cToken,
        string $ghlToken,
        string $appTypeId,
        string $pipelineId,
        string $pipelineStageId,
        string $companyName,
    ) {
        $this->clinikoApi->setToken($cToken);
        $this->ghlApi->setToken($ghlToken);

        $appointments = $this->getNewAppointments($appTypeId);
        $newData = $this->processAppointments($appointments, $pipelineId, $pipelineStageId);
        foreach ($newData as $data) {
            $contact = data_get($data, 'opportunity.contact', []);
            $source = data_get($data, 'opportunity.source');
            $opportunityId = data_get($data, 'opportunity.id');
            $patientId = data_get($data, 'patient.id');
            $patientName = data_get($data, 'patient.first_name') . ' ' . data_get($data, 'patient.last_name');
            $patientPhone = data_get($data, 'patient.patient_phone_numbers.0.normalized_number');
            $patientEmail = data_get($data, 'patient.email');
            $contactName = data_get($contact, 'name');
            $contactPhone = data_get($contact, 'phone');
            $contactEmail = data_get($contact, 'email');

            logger('Converted Opportunity', compact(
                'companyName',
                'source',
                'opportunityId',
                'patientId',
                'patientName',
                'patientPhone',
                'patientEmail',
                'contactName',
                'contactPhone',
                'contactEmail',
            ));
            // logger('Converted Opportunity JSON', [json_encode($data)]);
        }
    }

    protected function getNewAppointments($appTypeId): array
    {
        $formattedDate = Carbon::now()->subHour()->toIso8601ZuluString();

        if (app()->environment('local')) {
            $formattedDate = Carbon::now()->subDays(30)->toIso8601ZuluString();
        }

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
            ->values()
            ->toArray();
    }

    protected function processAppointments(array $list, $pipelineId, $pipelineStageId)
    {
        $results = [];
        foreach ($list as $cliniko) {
            $query = data_get($cliniko, 'patient.email');

            $opportunity = head($this->getOpportunities($query, $pipelineId));
            $contact = head($this->getContacts($query));
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

            if ($isMoved || app()->environment('local')) {
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
        $contacts = $this->ghlApi->request("contacts", compact('query'));
        return data_get($contacts, 'contacts', []);
    }
}
