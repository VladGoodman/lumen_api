<?php

namespace App\Listeners;

use App\Events\CreatingListsEvent;
use App\Events\ExampleEvent;
use App\Http\Helper\ResponseHelper;
use App\Models\UserLists;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;

class CreatingListsListener
{
    /**
     * Handle the event.
     *
     * @param CreatingListsEvent $event
     */
    public function handle(CreatingListsEvent $event)
    {
        UserLists::create([
            'list_id' => $event->list->id,
            'user_id' => Auth::id(),
        ]);
    }
}
