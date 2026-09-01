<?php

use App\Enums\SupportedLocale;
use App\Livewire\Actions\Logout;
use App\Livewire\ConversationShow;
use App\Livewire\Home;
use App\Livewire\Inbox;
use App\Livewire\ListingForm;
use App\Livewire\ListingIndex;
use App\Livewire\ListingShow;
use App\Livewire\MyListings;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');
Route::get('/anuncios', ListingIndex::class)->name('listings.index');
Route::get('/categorias/{category:slug}', ListingIndex::class)->name('categories.show');

Route::middleware('auth')->group(function () {
    Route::get('/meus-anuncios', MyListings::class)->name('listings.mine');
    Route::get('/anuncios/novo', ListingForm::class)->name('listings.create');
    Route::get('/anuncios/{listing:slug}/editar', ListingForm::class)->name('listings.edit');
    Route::get('/mensagens', Inbox::class)->name('messages.index');
    Route::get('/mensagens/nova/{listing:slug}', ConversationShow::class)->name('messages.start');
    Route::get('/mensagens/{conversation}', ConversationShow::class)->name('messages.show');
});

Route::get('/anuncios/{listing:slug}', ListingShow::class)->name('listings.show');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::post('/logout', function (Logout $logout) {
    $logout();

    return redirect('/');
})->middleware('auth')->name('logout');

Route::get('/idioma/{locale}', function (string $locale) {
    if (SupportedLocale::tryFrom($locale)) {
        session(['locale' => $locale]);
    }

    return redirect()->back();
})->name('locale.switch');

require __DIR__.'/auth.php';
