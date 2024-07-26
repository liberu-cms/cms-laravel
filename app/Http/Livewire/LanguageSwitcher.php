<?php

namespace App\Http\Livewire;

use App\Models\Language;
use Livewire\Component;

class LanguageSwitcher extends Component
{
    public $currentLocale;

    public function mount()
    {
        $this->currentLocale = app()->getLocale();