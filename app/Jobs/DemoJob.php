<?php

namespace App\Jobs;

use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DemoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $deleteWhenMissingModels = true;

    /**
     * Create a new job instance.
     */
    public function __construct(private Property $property)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        echo $this->property->title;
    }
}
