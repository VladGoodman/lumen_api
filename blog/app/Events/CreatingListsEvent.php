<?php

namespace App\Events;
use App\Models\Lists;
use Illuminate\Queue\SerializesModels;

class CreatingListsEvent extends Event
{
    use SerializesModels;

    public $list;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Lists $list)
    {
        $this->list = $list;
    }
}
