<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Listeners\MergeCartListener;

class MergeCartListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
{
    // event->user adalah user yang baru login
    $cartService = new \App\Services\CartService();
    $cartService->mergeCartOnLogin();
}
protected $listen = [
    Login::class => [
        MergeCartListener::class,
    ],
];

}