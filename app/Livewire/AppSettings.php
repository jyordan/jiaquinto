<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AppSetting;

class AppSettings extends Component
{
    public $settings = [];
    public $setting_id;
    public $key;
    public $value;
    public $isEdit = false;
    public $showLogsModal = false;

    public function mount()
    {
        $this->loadSettings();
    }

    public function loadSettings()
    {
        $this->settings = AppSetting::all();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showLogsModal = true;
    }

    public function saveSetting()
    {
        $this->validate([
            'key' => 'required|string|unique:app_settings,key,' . $this->setting_id,
            'value' => 'nullable|string',
        ]);

        if ($this->isEdit) {
            $setting = AppSetting::find($this->setting_id);
            $setting->update([
                'key' => $this->key,
                'value' => $this->value,
            ]);
        } else {
            AppSetting::create([
                'key' => $this->key,
                'value' => $this->value,
            ]);
        }

        $this->resetForm();
        $this->loadSettings();
        $this->showLogsModal = false; // Close modal after saving
    }

    public function editSetting($id)
    {
        $setting = AppSetting::find($id);
        $this->setting_id = $setting->id;
        $this->key = $setting->key;
        $this->value = $setting->value;
        $this->isEdit = true;
        $this->showLogsModal = true; // Open modal for editing
    }

    public function deleteSetting($id)
    {
        AppSetting::find($id)?->delete();
        $this->loadSettings();
    }

    public function resetForm()
    {
        $this->reset(['setting_id', 'key', 'value', 'isEdit', 'showLogsModal']);
    }

    public function render()
    {
        return view('livewire.app-settings');
    }
}
