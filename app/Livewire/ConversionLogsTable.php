<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ConversionLog; // Assuming there's a model

class ConversionLogsTable extends Component
{
    use WithPagination;

    public $conversionId; // Capture conversion_id
    public $search = ''; // For filtering logs
    public $perPage = 10; // Pagination size

    protected $queryString = ['search'];

    public function mount($conversionId)
    {
        $this->conversionId = $conversionId;
    }

    public function render()
    {
        $logs = ConversionLog::query()
            ->where('conversion_id', $this->conversionId) // Filter logs by conversion ID
            ->when($this->search, function ($query) {
                $query->where('patient_name', 'like', "%{$this->search}%")
                    ->orWhere('patient_email', 'like', "%{$this->search}%")
                    ->orWhere('contact_email', 'like', "%{$this->search}%")
                    ->orWhere('contact_name', 'like', "%{$this->search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.conversion-logs-table', compact('logs'));
    }
}
