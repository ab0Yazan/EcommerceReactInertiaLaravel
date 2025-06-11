<?php

namespace App\Filament\Resources;

use App\Enums\ProductStatusEnum;
use App\Enums\RolesEnum;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static SubNavigationPosition $subNavigationPosition = subNavigationPosition::End;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->forVendor();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(
                            function (string $operation, $state, callable $set){
                                $set("slug", Str::slug($state));
                            }
                        ),
                    TextInput::make('slug')
                        ->required(),

                    Forms\Components\Select::make('department_id')
                        ->relationship('department', 'name')
                        ->label(__('Department'))
                        ->preload()
                        ->searchable()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function(callable $set){
                            $set("category_id", null);
                        }),
                    Forms\Components\Select::make('category_id')
                        ->relationship('category', 'name', function(Builder $query, callable $get){
                            $departmentId = $get('department_id');
                            if($departmentId){
                                $query->where('department_id', $departmentId);
                            }
                        })->preload()
                        ->searchable()
                        ->required(),
                ]),

                RichEditor::make('description')->required()->columnSpan(2),

                Forms\Components\Grid::make()
                ->schema([
                    TextInput::make('price')
                    ->required()
                    ->numeric(),
                    TextInput::make('quantity')
                    ->integer(),
                    Select::make('status')
                    ->options(ProductStatusEnum::labels())
                    ->default(ProductStatusEnum::Draft->value)
                    ->required()
                ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('images')
                    ->collection('images')
                    ->limit(1)
                    ->label(__('Image'))
                    ->conversion('thumb'),
                TextColumn::make('title')
                ->searchable()
                ->words(10)
                ->sortable(),
                TextColumn::make('status')
                ->badge()
                ->colors(ProductStatusEnum::colors()),
                TextColumn::make('department.name'),
                TextColumn::make('category.name'),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                SelectFilter::make('status')
                ->options(ProductStatusEnum::labels()),
                SelectFilter::make('department_id')
                ->relationship('department', 'name')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
            'images' => Pages\ProductImages::route('/{record}/images'),
            'variation-types' => Pages\ProductVariationTypes::route('/{record}/variation-types'),
            'product-variations' => Pages\ProductVariations::route('/{record}/product-variations'),
        ];
    }

    public static function canViewAny(): bool
    {
        /** @var User $user */
        $user = Filament::auth()->user();
        return $user && $user->hasRole(RolesEnum::Vendor);
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
           Pages\EditProduct::class,
            Pages\ProductImages::class,
            Pages\ProductVariationTypes::class,
            Pages\ProductVariations::class,
        ]);
    }
}
