<?php

namespace App\Console\Commands;

use App\Models\ConversionKey;
use App\Modules\Api\ClinikoApi;
use App\Modules\Api\GoHighLevelApi;
use App\Modules\GhlConversion\GhlConversionService;
use Illuminate\Console\Command;

class GhlConversionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:ghl-conversion-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move pipeline state';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = new GhlConversionService(new ClinikoApi, new GoHighLevelApi);
        $conversions = ConversionKey::active()->get();

        foreach ($conversions as $model) {
            $service->processConversion($model);
        }
    }
}
