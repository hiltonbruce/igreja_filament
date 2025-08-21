# Igreja Filament - AI Coding Assistant Instructions

## Project Overview
This is a **church management system** built with **Laravel + Filament v3**, designed for Brazilian evangelical churches. The system manages member registration, ecclesiastical records, letters (recommendation/transfer), and administrative processes. The application is bilingual (Portuguese/English) with Portuguese as the primary language.

## Architecture & Core Patterns

### Database Design
- **Hierarchical geographic structure**: `countries` → `states` → `cities` → `neighborhoods`
- **Member-centric design**: All features revolve around the `members` table with extensive relationships
- **Soft deletes enabled**: Use `softDeletesTz()` for data integrity
- **Foreign key constraints**: Strict referential integrity with `constrained()` and proper `onDelete` cascades
- **Brazilian-specific fields**: CPF validation, blood types, spiritual status tracking

### Filament Resource Pattern
```php
// Standard Filament Resource structure in app/Filament/Admin/Resources/
class MemberResource extends Resource {
    // Use Wizard components for complex multi-step forms
    Wizard::make([
        Wizard\Step::make(__('custom.Personal Data'))
            ->schema([...])
            ->columns(3),
    ])
    
    // Always use MaxWidth::Full for complex forms
    Tables\Actions\EditAction::make()
        ->modalWidth(MaxWidth::Full)
}
```

### Internationalization System
- **Custom translation keys**: Use `__('custom.Key')` pattern, not Laravel defaults
- **Translation files**: `lang/pt_BR/custom.php` and `lang/es/custom.php`
- **Consistent naming**: All form labels and UI text must use translation helpers

### Form Interaction Patterns
- **Conditional visibility**: Use `->live()` + `->visible(fn (Forms\Get $get) => $get('field') === 'value')`
- **Brazilian context**: `brazilian_born` field controls visibility of `country_of_birth_id`
- **Required field logic**: Fields become required/optional based on user selections

## Development Workflows

### Adding New Resources
1. Create migration with detailed comments explaining field purposes
2. Create Model with proper relationships and constraints
3. Create Filament Resource with Wizard for complex forms
4. Add translation keys to `lang/pt_BR/custom.php` and `lang/es/custom.php`
5. Use `->columnSpanFull()` for full-width fields, `->columns(3)` for step layout

### Database Migrations
```php
// Always include detailed comments
$table->string('document')->unique()->comment('Documento do membro, como CPF ou RG');
$table->boolean('spiritual_situation')->default(true)->comment('Situação espiritual do membro - false para disciplinado');

// Use proper foreign key constraints
$table->foreignId('country_id')->constrained('countries')->default(55)->comment('País do membro, padrão é Brasil');
```

### Common Commands
```bash
php artisan make:filament-resource ModelName --generate
php artisan migrate
php artisan make:model ModelName -m
```

## Key Dependencies & Integrations
- **Filament v3.3**: Main admin panel framework
- **laravel-lang/common**: Provides additional language packs
- **Brazilian locale support**: Default timezone and currency formatting

## Critical Business Rules
1. **CPF uniqueness**: Only one member per CPF document
2. **Roll number restriction**: Cannot use sequence "666" in member rolls
3. **Spiritual status tracking**: Members can be "disciplined" (spiritual_situation = false)
4. **Family relationships**: Members can reference other members as parents
5. **Church hierarchy**: Members belong to churches, which have departments and neighborhoods

## File Structure Conventions
- Models: Simple Eloquent models in `app/Models/`
- Resources: Filament admin resources in `app/Filament/Admin/Resources/`
- Migrations: Detailed with Portuguese comments explaining business context
- Translations: Custom keys in `lang/{locale}/custom.php`

## Forms Best Practices
- Use `Wizard` components for multi-step member registration
- Apply `->live()` for reactive form fields
- Set proper column layouts: `->columns(3)` for main content, `->columnSpanFull()` for wide fields
- Always include proper validation and required field logic
- Use `MaxWidth::Full` for complex edit modals
