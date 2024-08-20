<?php
use App\Http\Livewire\Homepage;
use App\Livewire\About;
use App\Livewire\Contact;
use App\Livewire\Webrender;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', Webrender::class);