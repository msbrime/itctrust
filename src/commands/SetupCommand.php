<?php

namespace Itctrust;

/**
 * This file is part of Itctrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Itctrust
 */

use Illuminate\Console\Command;

class SetupCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'itctrust:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup migration and models for Itctrust';

    /**
     * Commands to call with their description
     *
     * @var array
     */
    protected $calls = [
        'itctrust:migration' => 'Creating migration',
        'itctrust:make-role' => 'Creating Role model',
        'itctrust:make-mandate' => 'Creating Mandate model',
        'itctrust:make-permissionset' => 'Creating PermissionSet model',
        'itctrust:make-permission' => 'Creating Permission model',
        'itctrust:add-trait' => 'Adding ItctrustUserTrait to User model'
    ];

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        foreach ($this->calls as $command => $info) {
            $this->line(PHP_EOL . $info);
            $this->call($command);
        }
    }
}
