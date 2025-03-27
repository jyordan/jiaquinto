<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ClinikoMonitorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cliniko-monitor-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert lead to customer.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $appointments = $this->getNewAppointments();
        $this->processAppointments($appointments);
    }

    protected function processAppointments(array $list)
    {
        $pipelineId = env('GHL_PIPELINE_ID');
        $pipelineStateId = env('GHL_PIPELINE_STAGE_ID');
        // $pipelines = data_get($this->requestGoHighLevel("pipelines"), 'pipelines');
        // $pipeline = collect($pipelines)->where('id', $pipelineId)
        //     ->first();

        foreach ($list as $cliniko) {
            $patient = data_get($cliniko, 'patient', []);
            $query = data_get($cliniko, 'patient.email');

            $opportunity = head($this->getOpportunities($pipelineId, $query));
            $contact = head($this->getContacts($query));
            $isMoved = 0;

            if (!$opportunity) {
                if (!$contact) {
                    $contact = $this->newContact($patient);
                    dump($contact, 'New contact');
                }
                if (data_get($contact, 'id')) {
                    $opportunity = $this->newOpportunities($pipelineId, $pipelineStateId, $contact, $patient);
                    $isMoved = 1;
                    dump($patient, $opportunity, $contact, 'New opportunity');
                }
            } else {
                if (data_get($opportunity, 'pipelineStageId') != $pipelineStateId) {
                    $opportunity = $this->moveOpportunities($pipelineId, $pipelineStateId, $opportunity);
                    $isMoved = 1;
                    dump($patient, $opportunity, $contact, 'Move opportunity');
                } else {
                    dump($patient, $opportunity, $contact, 'Same opportunity');
                }
            }

            if ($isMoved) {
                $contact = data_get($opportunity, 'contact');
                $source = data_get($opportunity, 'source');
                $name = data_get($contact, 'name');
                $phone = data_get($contact, 'phone');
                $email = data_get($contact, 'email');

                $this->logSheet($name, $phone, $email, $source);
            }
        }
    }

    protected function getNewAppointments(): array
    {
        $aptId = env('CLINIKO_APPOINTMENT_TYPE_ID');
        $formattedDate = Carbon::now()->subHour()->toIso8601ZuluString();
        $params = [
            'order=desc',
            'q[]=appointment_type_id:=' . $aptId,
            'q[]=created_at:>' . $formattedDate,
        ];

        $results = $this->requestStaticClinikoApi(
            'individual_appointments?' . implode('&', $params)
        );

        $appointments = collect(data_get($results, 'individual_appointments', []))
            ->map(function ($appointment) {
                $url = data_get($appointment, 'patient.links.self', '');
                $patient = $this->requestClinikoApi($url);
                return compact('appointment', 'patient');
            })
            ->values()
            ->toArray();

        return $appointments;
    }

    protected function moveOpportunities(string $pipelineId, string $pipelineStateId, array $opportunity): array
    {
        $id = data_get($opportunity, 'id');
        $title = data_get($opportunity, 'name');
        $status = data_get($opportunity, 'status');

        return $this->requestGoHighLevel(
            "pipelines/{$pipelineId}/opportunities/{$id}",
            [
                'title' => $title,
                'stageId' => $pipelineStateId,
                'status' => $status,
            ],
            'put'
        );
    }

    protected function newOpportunities(string $pipelineId, string $pipelineStateId, array $contact, array $patient): array
    {
        $firstName = data_get($patient, 'first_name');
        $lastName = data_get($patient, 'last_name');
        $contactId = data_get($contact, 'id');

        return $this->requestGoHighLevel(
            "pipelines/{$pipelineId}/opportunities",
            [
                'title' => "{$firstName} {$lastName}",
                'stageId' => $pipelineStateId,
                'contactId' => $contactId,
                'status' => 'open',
                'source' => 'Cliniko',
            ],
            'post'
        );
    }

    protected function newContact(array $patient): array
    {
        $firstName = data_get($patient, 'first_name');
        $lastName = data_get($patient, 'last_name');
        $email = data_get($patient, 'email');
        $patientPhoneNumbers = data_get($patient, 'patient_phone_numbers', []);
        $phone = data_get(head($patientPhoneNumbers), 'normalized_number');
        $dateOfBirth = data_get($patient, 'date_of_birth');
        $city = data_get($patient, 'city');
        $country = data_get($patient, 'country');
        $state = data_get($patient, 'state');
        $postalCode = data_get($patient, 'post_code');
        $companyName = data_get($patient, 'Cliniko');

        return $this->requestGoHighLevel(
            "contacts",
            [
                "email" => $email,
                "phone" => $phone,
                "firstName" => $firstName,
                "lastName" => $lastName,
                "name" => "{$firstName} {$lastName}",
                "dateOfBirth" => $dateOfBirth,
                "address1" => null,
                "city" => $city,
                "state" => $state,
                "country" => $country,
                "postalCode" => $postalCode,
                "companyName" => $companyName,
                // "website" => "35061",
                // "tags" => ["commodo", "veniam ut reprehenderit"],
                "source" => "cliniko",
                // "customField" => [
                //     "__custom_field_id__" => "do in Lorem ut exercitation"
                // ]
            ],
            'post'
        );
    }

    protected function getOpportunities(string $pipelineId, string $query): array
    {
        $opportunities = $this->requestGoHighLevel("pipelines/{$pipelineId}/opportunities", compact('query'));
        return data_get($opportunities, 'opportunities', []);
    }

    protected function getContacts(string $query): array
    {
        $opportunities = $this->requestGoHighLevel("contacts", compact('query'));
        return data_get($opportunities, 'contacts', []);
    }

    protected function requestClinikoApi(string $any, array $request = [], string $method = 'get'): array
    {
        // Validate or perform any authentication checks here as needed

        // Build the full API URL
        $apiUrl = 'https://api.au1.cliniko.com/v1/' . $any;
        if (str_contains($any, 'https://')) $apiUrl = $any;

        $user = env('CLINIKO_USER');
        $data = array_merge(['order' => 'desc'], $request);

        // Perform the HTTP request using Guzzle
        $response = Http::acceptJson()
            ->withBasicAuth($user, '')
            ->{$method}($apiUrl, $data);

        return json_decode($response->body(), true);
    }

    protected function requestStaticClinikoApi(string $any): array
    {
        // Validate or perform any authentication checks here as needed

        // Build the full API URL
        $apiUrl = 'https://api.au1.cliniko.com/v1/' . $any;
        if (str_contains($any, 'https://')) $apiUrl = $any;

        $user = env('CLINIKO_USER');

        // Perform the HTTP request using Guzzle
        $response = Http::acceptJson()
            ->withBasicAuth($user, '')
            ->get($apiUrl);

        return json_decode($response->body(), true);
    }

    protected function logSheet(string $name, string $phone, string $email, string $workflow)
    {
        // Perform the HTTP request using Guzzle
        Http::asForm()->post(env('GOOGLE_FORM_URL'), [
            'entry.2135131916' => $name,
            'entry.715138392' => $phone,
            'entry.1706893644' => $email,
            'entry.1016933667' => $workflow,
            'entry.1297538632' => now()->toDateTimeString(),
        ]);
    }

    protected function requestGoHighLevel(string $any, array $request = [], string $method = 'get'): array
    {
        // Build the full API URL
        $apiUrl = 'https://rest.gohighlevel.com/v1/' . $any;
        $token = env('GO_HIGH_LEVEL_KEY');

        // Perform the HTTP request using Guzzle
        $response = Http::acceptJson()
            ->withToken($token)
            ->{$method}($apiUrl, $request);

        // Return the API response to the client
        return json_decode($response->body(), true);
    }
}
