<?php namespace Itctrust;

/**
 * This file is part of Itctrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Itctrust
 */

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Itctrust\Traits\ItctrustUserTrait;
use Traitor\Traitor;

class AddItctrustUserTraitUseCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'itctrust:add-trait';

    /**
     * Trait added to User model
     *
     * @var string
     */
    protected $targetTrait = ItctrustUserTrait::class;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $userModel = $this->getUserModel();
        
        if (! class_exists($userModel)) {
            $this->error("Class $userModel does not exist.");
            return;
        }

        if ($this->alreadyUsesItctrustUserTrait()) {
            $this->error("Class $userModel already uses ItctrustUserTrait.");
            return;
        }

        Traitor::addTrait($this->targetTrait)->toClass($userModel);

        $this->info("ItctrustUserTrait added successfully");
    }

    /**
     * @return bool
     */
    protected function alreadyUsesItctrustUserTrait()
    {
        return in_array(ItctrustUserTrait::class, class_uses($this->getUserModel()));
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return "Add ItctrustUserTrait to {$this->getUserModel()} class";
    }

    /**
     * @return string
     */
    protected function getUserModel()
    {
        return Config::get('auth.providers.users.model', 'App\User');
    }
}
