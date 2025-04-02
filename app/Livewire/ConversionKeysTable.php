<?php

namespace App\Livewire;

use App\Models\ConversionKey; // Ensure this matches your model name
use App\Modules\Api\ClinikoApi;
use App\Modules\Api\GoHighLevelApi;
use Livewire\Component;
use Livewire\WithPagination;

class ConversionKeysTable extends Component
{
    use WithPagination;

    public $conversionKeyId;
    public $showModal = false;
    public $showLogsModal = false;
    public $showDeleteModal = false;
    public $deleteId = null; // Store the ID for confirmation
    public $selectedConversionId;

    public $optionGhlPipelines = [];
    public $optionGhlPipelineStages = [];
    public $optionClinikoAppTypes = [];

    public $form = [
        'company_name' => '',
        'cliniko_api_key' => '',
        'ghl_api_key' => '',
        'cliniko_app_type_id' => '',
        'ghl_pipeline_id' => '',
        'ghl_pipeline_stage_source_id' => '',
        'ghl_pipeline_stage_target_id' => '',
        'active_at' => false,
    ];

    protected $rules = [
        'form.company_name' => 'required|string',
        'form.cliniko_api_key' => 'required|string',
        'form.ghl_api_key' => 'required|string',
        'form.cliniko_app_type_id' => 'required|string',
        'form.ghl_pipeline_id' => 'required|string',
        'form.ghl_pipeline_stage_source_id' => 'required|string',
        'form.ghl_pipeline_stage_target_id' => 'required|string',
    ];

    public function updatedFormClinikoApiKey()
    {
        $this->form['cliniko_app_type_id'] = '';
        $this->optionClinikoAppTypes = $this->getClinikoAppTypes($this->form['cliniko_api_key']);
    }

    public function updatedFormGhlApiKey()
    {
        $this->form['ghl_pipeline_id'] = '';
        $this->form['ghl_pipeline_stage_source_id'] = '';
        $this->form['ghl_pipeline_stage_target_id'] = '';

        $this->optionGhlPipelines = $this->getGhlPipelines($this->form['ghl_api_key']);
        $this->optionGhlPipelineStages = [];
    }

    public function updatedFormGhlPipelineId()
    {
        $this->form['ghl_pipeline_stage_source_id'] = '';
        $this->form['ghl_pipeline_stage_target_id'] = '';
        $this->optionGhlPipelineStages = $this->getGhlPipelineStages($this->form['ghl_pipeline_id']);
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->resetInputFields();

        if ($id) {
            $this->conversionKeyId = $id;
            $record = ConversionKey::findOrFail($id);

            $this->setOptions($record);

            // Dynamically assign record values to form
            foreach (array_keys($this->form) as $key) {
                $this->form[$key] = $record->$key ?? '';
            }
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
    }

    public function openLogsModal($conversionId)
    {
        $this->selectedConversionId = $conversionId;
        $this->showLogsModal = true;
    }

    public function closeLogsModal()
    {
        $this->showLogsModal = false;
    }

    public function resetInputFields()
    {
        $this->conversionKeyId = null;
        $this->form = array_fill_keys(array_keys($this->form), ''); // Reset all fields

        $this->optionGhlPipelines = [];
        $this->optionGhlPipelineStages = [];
        $this->optionClinikoAppTypes = [];

        $this->reset();
    }

    protected function setOptions($record)
    {
        $this->optionClinikoAppTypes = $this->getClinikoAppTypes($record->cliniko_api_key);

        $this->optionGhlPipelines = $this->getGhlPipelines($record->ghl_api_key);
        $this->optionGhlPipelineStages = $this->getGhlPipelineStages($record->ghl_pipeline_id);
    }

    protected function getClinikoAppTypes(string|null $token): array
    {
        if (!$token) return [];

        $cliniko = new ClinikoApi;
        $cliniko->setToken($token);
        $appTypes = $cliniko->request('appointment_types');

        return collect(data_get($appTypes, 'appointment_types', []))
            ->sortBy('name')
            ->values()
            ->toArray();
    }

    protected function getGhlPipelines(string|null $token): array
    {
        if (!$token) return [];

        $ghl = new GoHighLevelApi;
        $ghl->setToken($token);
        $pipelines = $ghl->request('pipelines');

        return collect(data_get($pipelines, 'pipelines', []))
            ->sortBy('name')
            ->values()
            ->toArray();
    }

    protected function getGhlPipelineStages(string|null $pipelineId): array
    {
        if (!$pipelineId) return [];

        $pipeline = collect($this->optionGhlPipelines)
            ->where('id', $pipelineId)
            ->first();

        return data_get($pipeline, 'stages', []);
    }


    public function save()
    {
        $this->validate();

        ConversionKey::updateOrCreate(['id' => $this->conversionKeyId], $this->form);
        $model = ConversionKey::find($this->conversionKeyId);
        $model->active_at = $this->form['active_at'];
        $model->save();

        session()->flash('message', $this->conversionKeyId ? 'Updated Successfully' : 'Created Successfully');

        $this->closeModal();
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->deleteId) {
            ConversionKey::findOrFail($this->deleteId)->delete();
            session()->flash('message', 'Deleted Successfully');
        }

        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.conversion-keys-table', [
            'conversionKeys' => ConversionKey::latest()->paginate(10),
        ]);
    }
}
