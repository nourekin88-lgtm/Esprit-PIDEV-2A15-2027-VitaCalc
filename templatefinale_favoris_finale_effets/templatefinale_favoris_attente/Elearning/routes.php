<?php
declare(strict_types=1);

// Une route associe un nom de page à un contrôleur et à sa méthode.
return [
    'home'         => [HomeController::class,   'index'],
    'events'       => [EventsController::class,  'index'],
    'event-detail' => [EventsController::class,  'detail'],
    'signup'       => [SignupController::class,  'index'],
    'admin'        => [AdminController::class,   'index'],
];
