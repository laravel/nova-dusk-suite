<?php

namespace App\Nova\Actions;

use App\Jobs\SleepTask;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\PendingBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Contracts\BatchableAction;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Throwable;

class BatchableSleep extends Action implements BatchableAction, ShouldQueue
{
    use Batchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models): void
    {
        if (! $this->isStandalone()) {
            foreach ($models as $model) {
                /** @phpstan-ignore-next-line */
                $this->batch()->add(new SleepTask($model));

                $this->markAsFinished($model);
            }
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Register `then`, `catch` and `finally` event on batchable job.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Bus\PendingBatch  $batch
     * @return void
     */
    public function withBatch(ActionFields $fields, PendingBatch $batch): mixed
    {
        $batch->then(function (Batch $batch) {
            /** @phpstan-ignore-next-line */
            ray($batch->resourceIds)->orange();
        })->catch(function (Batch $batch, Throwable $e) {
            /** @phpstan-ignore-next-line */
            ray()->exception($e);
            // First batch job failure detected...
        })->finally(function (Batch $batch) {
            ray($batch);
            // The batch has finished executing...
        });
    }
}
