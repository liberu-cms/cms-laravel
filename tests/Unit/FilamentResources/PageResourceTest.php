<?php

namespace Tests\Unit\FilamentResources;

use Tests\TestCase;
use App\Filament\Resources\PageResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;

class PageResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_schema_includes_all_fields_with_correct_configuration()
    {
        $form = PageResource::form(app(\Filament\Resources\Form::class));
        $schema = $form->getSchema();

        $this->assertTrue(collect($schema)->contains(fn ($component) => $component instanceof TextInput && $component->getName() === 'title' && $component->isRequired() && $component->getMaxLength() === 255));
        $this->assertTrue(collect($schema)->contains(fn ($component) => $component instanceof Textarea && $component->getName() === 'content' && $component->isRequired()));
        $this->assertTrue(collect($schema)->contains(fn ($component) => $component instanceof TextInput && $component->getName() === 'slug' && $component->isRequired() && $component->getMaxLength() === 255));
        $this->assertTrue(collect($schema)->contains(fn ($component) => $component instanceof DateTimePicker && $component->getName() === 'published_at' && $component->isRequired()));
        $this->assertTrue(collect($schema)->contains(fn ($component) => $component instanceof Select && $component->getName() === 'user_id' && $component->isRequired()));
        $this->assertTrue(collect($schema)->contains(fn ($component) => $component instanceof Select && $component->getName() === 'category_id' && $component->isRequired()));
    }

    public function test_form_schema_correctly_defines_relationships()
    {
        $form = PageResource::form(app(\Filament\Resources\Form::class));
        $schema = $form->getSchema();

        $this->assertTrue(collect($schema)->contains(fn ($component) => $component instanceof Select && $component->getName() === 'user_id' && $component->getRelationshipName() === 'user'));
        $this->assertTrue(collect($schema)->contains(fn ($component) => $component instanceof Select && $component->getName() === 'category_id' && $component->getRelationshipName() === 'category'));
    }
}
