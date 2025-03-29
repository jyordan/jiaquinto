<?php

namespace App\Livewire;

use App\Models\ConversionKey; // Ensure this matches your model name
use Illuminate\Support\Arr;
use Livewire\Component;
use Livewire\WithPagination;

class ConversionKeysTable extends Component
{
    use WithPagination;

    public $conversionKeyId;
    public $showModal = false;
    public $showDeleteModal = false;
    public $deleteId = null; // Store the ID for confirmation

    public $form = [
        'cliniko_api_key' => '',
        'ghl_api_key' => '',
        'cliniko_app_type_id' => '',
        'ghl_pipeline_id' => '',
        'ghl_pipeline_stage_id' => '',
        'starts_at' => null,
        'ends_at' => null,
    ];

    protected $rules = [
        'form.cliniko_api_key' => 'required|string',
        'form.ghl_api_key' => 'required|string',
        'form.cliniko_app_type_id' => 'required|string',
        'form.ghl_pipeline_id' => 'required|string',
        'form.ghl_pipeline_stage_id' => 'required|string',
        'form.starts_at' => 'nullable|date',
        'form.ends_at' => 'nullable|date',
    ];

    public function openModal($id = null)
    {
        $this->resetInputFields();

        if ($id) {
            $this->conversionKeyId = $id;
            $record = ConversionKey::findOrFail($id);

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

    public function resetInputFields()
    {
        $this->conversionKeyId = null;
        $this->form = array_fill_keys(array_keys($this->form), ''); // Reset all fields
    }

    public function save()
    {
        $this->validate();

        $form = Arr::except($this->form, ['starts_at', 'ends_at']);
        ConversionKey::updateOrCreate(['id' => $this->conversionKeyId], $form);

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
